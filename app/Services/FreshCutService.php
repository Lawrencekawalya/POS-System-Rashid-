<?php

namespace App\Services;

use App\Models\FreshCutBatch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Fresh cuts are bought by the kg and sold as portions through menu items.
 *
 * Portions live in stock movements like any product: purchases add them,
 * sales take them, refunds return them and write-offs remove them. Usage is
 * matched to purchases oldest first, so each purchase shows what is left of it.
 */
class FreshCutService
{
    public const WRITE_OFF = 'write_off';

    public const WRITE_OFF_REASONS = [
        'spoiled' => 'Spoiled',
        'staff_meal' => 'Staff meal',
        'given_away' => 'Given away',
        'count_correction' => 'Count correction (missing)',
        'other' => 'Other',
    ];

    public function createCut(string $name, float $portionsPerKg): Product
    {
        return Product::create([
            'name' => $name,
            'barcode' => 'CUT-'.Str::upper(Str::random(10)),
            'unit_type' => Product::PORTION,
            'portions_per_kg' => $portionsPerKg,
            'cost_price' => 0,
            'selling_price' => 0,
            'is_active' => true,
        ]);
    }

    public function recordPurchase(
        Product $cut,
        User $user,
        float $kg,
        float $portionsPerKg,
        float $totalCost,
        ?string $supplier,
        CarbonInterface $purchasedAt,
    ): FreshCutBatch {
        $portions = (int) round($kg * $portionsPerKg);

        if ($portions < 1) {
            throw ValidationException::withMessages(['kg' => 'This purchase gives less than one portion.']);
        }

        return DB::transaction(function () use ($cut, $user, $kg, $portionsPerKg, $portions, $totalCost, $supplier, $purchasedAt) {
            $batch = FreshCutBatch::create([
                'product_id' => $cut->id,
                'user_id' => $user->id,
                'kg' => $kg,
                'portions_per_kg' => $portionsPerKg,
                'portions' => $portions,
                'total_cost' => $totalCost,
                'supplier' => $supplier,
                'purchased_at' => $purchasedAt,
            ]);

            $movement = new StockMovement([
                'product_id' => $cut->id,
                'quantity' => $portions,
                'type' => 'purchase',
                'reference_type' => StockMovement::FRESH_CUT_BATCH,
                'reference_id' => $batch->id,
                'remarks' => rtrim(rtrim(number_format($kg, 2), '0'), '.').' kg bought',
            ]);
            // Dated with the purchase so day-by-day figures line up.
            $movement->created_at = $purchasedAt;
            $movement->save();

            // Remember the latest yield and cost per portion for the next purchase and reports.
            $cut->update(array_filter([
                'portions_per_kg' => $portionsPerKg,
                'cost_price' => $totalCost > 0 ? round($totalCost / $portions, 2) : null,
            ], fn ($value) => $value !== null));

            return $batch;
        });
    }

    /**
     * Remove a purchase recorded by mistake. Only allowed while none of it is used.
     */
    public function deletePurchase(FreshCutBatch $batch): void
    {
        $lot = collect($this->summary($batch->product)['lots'])->firstWhere('batch.id', $batch->id);

        if (! $lot || $lot['remaining'] < $batch->portions) {
            throw ValidationException::withMessages(['batch' => 'Portions from this purchase have already been used, so it cannot be removed.']);
        }

        DB::transaction(function () use ($batch) {
            StockMovement::where('reference_type', StockMovement::FRESH_CUT_BATCH)
                ->where('reference_id', $batch->id)
                ->delete();
            $batch->delete();
        });
    }

    public function writeOff(Product $cut, User $user, int $portions, string $reason, ?string $note): void
    {
        DB::transaction(function () use ($cut, $user, $portions, $reason, $note) {
            $left = (int) StockMovement::where('product_id', $cut->id)->lockForUpdate()->sum('quantity');

            if ($portions > $left) {
                throw ValidationException::withMessages(['portions' => "Only {$left} portion(s) are left to write off."]);
            }

            StockMovement::create([
                'product_id' => $cut->id,
                'quantity' => -$portions,
                'type' => self::WRITE_OFF,
                'reference_type' => 'user',
                'reference_id' => $user->id,
                'remarks' => self::WRITE_OFF_REASONS[$reason].($note ? ': '.$note : '').' (by '.$user->name.')',
            ]);
        });
    }

    /**
     * Portions for one cut on a given day, and what is left of each purchase at the end of it.
     *
     * @return array{opening: int, bought: int, sold: int, returned: int, written_off: int, closing: int, kg_left: ?float, lots: array<int, array{batch: ?FreshCutBatch, portions: int, used: int, remaining: int}>}
     */
    public function summary(Product $cut, ?CarbonInterface $date = null): array
    {
        $date ??= now();
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        $movements = StockMovement::where('product_id', $cut->id)
            ->where('created_at', '<=', $end)
            ->get(['quantity', 'type', 'reference_type', 'created_at']);

        $today = $movements->filter(fn (StockMovement $m) => $m->created_at->gte($start));
        $closing = (int) $movements->sum('quantity');

        // Portions bought before Fresh Cuts existed (through Purchases) count as one earlier lot.
        $earlier = (int) $movements
            ->where('type', 'purchase')
            ->where('reference_type', '!=', StockMovement::FRESH_CUT_BATCH)
            ->sum('quantity');

        $lots = $earlier > 0 ? [['batch' => null, 'portions' => $earlier]] : [];

        foreach ($cut->freshCutBatches()->where('purchased_at', '<=', $end)->orderBy('purchased_at')->orderBy('id')->get() as $batch) {
            $lots[] = ['batch' => $batch, 'portions' => $batch->portions];
        }

        // Whatever is not on hand has been used; take it from the oldest purchase first.
        $toAllocate = max(0, array_sum(array_column($lots, 'portions')) - max(0, $closing));

        foreach ($lots as &$lot) {
            $lot['used'] = min($lot['portions'], $toAllocate);
            $lot['remaining'] = $lot['portions'] - $lot['used'];
            $toAllocate -= $lot['used'];
        }
        unset($lot);

        $sumOf = fn (string $type) => (int) $today->where('type', $type)->sum('quantity');

        return [
            'opening' => $closing - (int) $today->sum('quantity'),
            'bought' => $sumOf('purchase'),
            'sold' => -$sumOf('sale'),
            'returned' => $sumOf('refund'),
            'written_off' => -$sumOf(self::WRITE_OFF),
            'closing' => $closing,
            'kg_left' => $cut->portions_per_kg > 0 ? round(max(0, $closing) / (float) $cut->portions_per_kg, 2) : null,
            'lots' => $lots,
        ];
    }
}
