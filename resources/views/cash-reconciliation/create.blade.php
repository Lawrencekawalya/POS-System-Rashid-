<x-layouts::app title="Cash Reconciliation">

    <h1 class="text-xl font-semibold mb-4">Cash Reconciliation</h1>
    <div class="flex items-center justify-between mb-4">
        @if ($errors->any())
            <div class="mb-4 text-red-600">
                {{ $errors->first() }}
            </div>
        @endif
    </div>
    <form method="POST" action="{{ route('cash.reconcile.store') }}">
        @csrf

        <input type="hidden" name="business_date" value="{{ $date }}">
        <input type="hidden" name="cash_expected" value="{{ $cashExpected }}">

        <div class="mb-3">
            <label class="block text-sm">Business Date</label>
            <input value="{{ $date }}" disabled class="border px-3 py-2 w-full bg-gray-100">
        </div>

        <div class="mb-3">
            <label class="block text-sm">Cash Expected</label>
            <input value="{{ number_format($cashExpected, 2) }}" disabled class="border px-3 py-2 w-full bg-gray-100">
        </div>

        <div class="mb-3">
            <label class="block text-sm">Cash Counted</label>
            <input name="cash_counted" type="number" step="0.01" required class="border px-3 py-2 w-full">
        </div>

        <div class="mb-3">
            <label class="block text-sm">Notes (required if over/short)</label>
            <textarea name="notes" class="border px-3 py-2 w-full"></textarea>
        </div>

        <button class="bg-black text-white px-4 py-2 w-full">
            Submit Reconciliation
        </button>
    </form>

</x-layouts::app>
