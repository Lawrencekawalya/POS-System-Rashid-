<x-layouts::app title="Cash Reconciliation History">

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Cash Reconciliation History</h1>

        <a href="{{ route('cash.reconcile') }}" class="px-4 py-2 bg-black text-white rounded text-sm">
            + New Reconciliation
        </a>
    </div>

    <table class="w-full border text-sm">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">Date</th>
                <th class="border px-2 py-1">Cashier</th>
                <th class="border px-2 py-1">Expected</th>
                <th class="border px-2 py-1">Counted</th>
                <th class="border px-2 py-1">Difference</th>
                <th class="border px-2 py-1">Status</th>
                <th class="border px-2 py-1">Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reconciliations as $rec)
                <tr>
                    <td class="border px-2 py-1">{{ $rec->business_date }}</td>
                    <td class="border px-2 py-1">{{ $rec->user->name }}</td>
                    <td class="border px-2 py-1">{{ number_format($rec->cash_expected, 2) }}</td>
                    <td class="border px-2 py-1">{{ number_format($rec->cash_counted, 2) }}</td>
                    <td class="border px-2 py-1 {{ $rec->difference < 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($rec->difference, 2) }}
                    </td>
                    <td class="border px-2 py-1">{{ ucfirst($rec->status) }}</td>
                    <td class="border px-2 py-1">{{ $rec->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</x-layouts::app>
