<?php

namespace App\Http\Controllers;

use App\Models\FreshCutBatch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\FreshCutService;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Rule;

class FreshCutController extends Controller
{
    public function __construct(private FreshCutService $freshCuts) {}

    public function index(Request $request)
    {
        $date = $this->date($request);

        $cuts = Product::freshCuts()
            ->with('portionMenuItems:id,name,stock_product_id,stock_quantity')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $cut) => ['cut' => $cut, 'summary' => $this->freshCuts->summary($cut, $date)]);

        return view('fresh-cuts.index', [
            'cuts' => $cuts,
            'date' => $date,
            'isToday' => $date->isToday(),
        ]);
    }

    public function show(Request $request, Product $freshCut)
    {
        abort_unless($freshCut->isFreshCut(), 404);

        $date = $this->date($request);

        return view('fresh-cuts.show', [
            'cut' => $freshCut->load('portionMenuItems'),
            'date' => $date,
            'isToday' => $date->isToday(),
            'summary' => $this->freshCuts->summary($freshCut, $date),
            'history' => StockMovement::where('product_id', $freshCut->id)->latest('created_at')->latest('id')->paginate(25),
            'reasons' => FreshCutService::WRITE_OFF_REASONS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('products', 'name')->where('unit_type', Product::PORTION)->whereNull('deleted_at')],
            'portions_per_kg' => 'required|numeric|min:0.1|max:1000',
        ]);

        $cut = $this->freshCuts->createCut($data['name'], (float) $data['portions_per_kg']);

        return redirect()->route('fresh-cuts.show', $cut)->with('success', "{$cut->name} added. Record a purchase to add portions.");
    }

    public function update(Request $request, Product $freshCut)
    {
        abort_unless($freshCut->isFreshCut(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('products', 'name')->where('unit_type', Product::PORTION)->whereNull('deleted_at')->ignore($freshCut->id)],
            'portions_per_kg' => 'required|numeric|min:0.1|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $freshCut->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Fresh cut updated.');
    }

    public function storePurchase(Request $request, Product $freshCut)
    {
        abort_unless($freshCut->isFreshCut(), 404);

        $data = $request->validate([
            'kg' => 'required|numeric|min:0.01|max:100000',
            'portions_per_kg' => 'required|numeric|min:0.1|max:1000',
            'total_cost' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'purchased_at' => 'required|date|before_or_equal:now',
        ]);

        $batch = $this->freshCuts->recordPurchase(
            $freshCut,
            $request->user(),
            (float) $data['kg'],
            (float) $data['portions_per_kg'],
            (float) ($data['total_cost'] ?? 0),
            $data['supplier'] ?? null,
            Date::parse($data['purchased_at']),
        );

        return back()->with('success', "Added {$batch->portions} portions of {$freshCut->name}.");
    }

    public function destroyPurchase(Product $freshCut, FreshCutBatch $batch)
    {
        abort_unless($batch->product_id === $freshCut->id, 404);

        $this->freshCuts->deletePurchase($batch);

        return back()->with('success', 'Purchase removed.');
    }

    public function storeWriteOff(Request $request, Product $freshCut)
    {
        abort_unless($freshCut->isFreshCut(), 404);

        $data = $request->validate([
            'portions' => 'required|integer|min:1',
            'reason' => ['required', Rule::in(array_keys(FreshCutService::WRITE_OFF_REASONS))],
            'note' => 'nullable|string|max:150',
        ]);

        $this->freshCuts->writeOff($freshCut, $request->user(), (int) $data['portions'], $data['reason'], $data['note'] ?? null);

        return back()->with('success', "Wrote off {$data['portions']} portion(s) of {$freshCut->name}.");
    }

    private function date(Request $request): CarbonInterface
    {
        $request->validate(['date' => 'nullable|date|before_or_equal:today']);

        return $request->filled('date') ? Date::parse($request->date) : now();
    }
}
