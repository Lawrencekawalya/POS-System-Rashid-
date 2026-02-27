<x-layouts::app title="Expense Log">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Expense Log</h1>
                <p class="text-sm text-gray-500">Total this month: <span class="font-bold text-gray-900">UGX
                        {{ number_format($totalThisMonth) }}</span></p>
            </div>
            <a href="{{ route('expenses.create') }}"
                class="bg-black text-white px-4 py-2 rounded-lg font-bold hover:bg-gray-800 transition">
                + Record Expense
            </a>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Category & Staff</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($expenses as $expense)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $expense->expense_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $expense->category }}</div>
                                <div class="flex items-center gap-1 text-xs text-gray-500">
                                    <flux:icon.user variant="micro" class="size-3" />
                                    {{-- Displays the name of the user who recorded it --}}
                                    <span class="font-medium text-indigo-600">{{ $expense->user->name ?? 'System' }}</span>
                                    <span class="text-gray-300 mx-1">|</span>
                                    <span>{{ $expense->payment_method }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-gray-900">
                                {{ number_format($expense->amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($expense->status == 'confirmed')
                                    <span
                                        class="px-2 py-1 text-xs font-bold bg-green-100 text-green-700 rounded-full">VERIFIED</span>
                                @elseif($expense->status == 'rejected')
                                    <span
                                        class="px-2 py-1 text-xs font-bold bg-red-100 text-red-700 rounded-full">REJECTED</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-xs font-bold bg-yellow-100 text-yellow-700 rounded-full">PENDING</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                @if($expense->status == 'pending')
                                    <a href="{{ route('expenses.edit', $expense) }}"
                                        class="text-indigo-600 hover:text-indigo-900 font-bold mr-3">Edit</a>
                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Delete this record?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:text-red-900 font-bold">Delete</button>
                                    </form>
                                @else
                                    <span class="text-gray-400 italic text-xs">Locked</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $expenses->links() }}
        </div>
    </div>
</x-layouts::app>