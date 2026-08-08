<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function __construct(private PartialRefundService $partialRefundService) {}

    public function refund(Sale $sale, int $userId, string $reason): void
    {
        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'refund_reason' => 'Refund reason is required.',
            ]);
        }

        $sale->load('items');

        if ($sale->isRefunded()) {
            throw ValidationException::withMessages([
                'sale' => 'This sale has already been refunded.',
            ]);
        }

        $this->partialRefundService->refund(
            $sale,
            $userId,
            $sale->items->mapWithKeys(fn ($item) => [$item->id => $item->quantity])->all(),
            $reason,
        );
    }
}
