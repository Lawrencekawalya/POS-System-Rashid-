{{--<x-layouts::app title="Cash Reconciliation History">

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Cash Reconciliation History</h1>

        <a href="{{ route('cash.reconcile.create') }}" class="px-4 py-2 bg-black text-white rounded text-sm">
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

</x-layouts::app>--}}

<x-layouts::app title="Cash Reconciliation History">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Cash Reconciliation</h1>
                <p class="text-sm text-gray-500">Track and verify cash drawer accuracy across shifts.</p>
            </div>

            <a href="{{ route('cash.reconcile.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150">
                <flux:icon.plus variant="micro" class="mr-2" />
                New Reconciliation
            </a>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Admin/Cashier</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Expected</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Counted</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Difference</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach ($reconciliations as $rec)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $rec->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $rec->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-7 w-7 rounded-full bg-gray-100 flex items-center justify-center mr-3 text-xs font-bold text-gray-600">
                                        {{ substr($rec->user->name, 0, 1) }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">{{ $rec->user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                {{ number_format($rec->cash_expected) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                {{ number_format($rec->cash_counted) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black">
                                @if($rec->difference == 0)
                                    <span class="text-green-600">0</span>
                                @else
                                    <span class="{{ $rec->difference < 0 ? 'text-red-600' : 'text-blue-600' }}">
                                        {{ $rec->difference > 0 ? '+' : '' }}{{ number_format($rec->difference) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusClasses = match($rec->status) {
                                        'balanced' => 'bg-green-100 text-green-700',
                                        'short' => 'bg-red-100 text-red-700',
                                        'over' => 'bg-blue-100 text-blue-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $statusClasses }}">
                                    {{ $rec->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                {{ $rec->notes ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $reconciliations->links() }}
        </div>
    </div>
</x-layouts::app>
