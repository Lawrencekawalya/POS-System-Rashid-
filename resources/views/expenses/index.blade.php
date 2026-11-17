<x-layouts::app title="Expense Log">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-100">Expense Log</h1>
                <p class="text-sm text-gray-500 dark:text-zinc-400">Total this month: <span class="font-bold text-gray-900 dark:text-zinc-100">UGX
                        {{ number_format($totalThisMonth) }}</span></p>
            </div>
            <a href="{{ route('expenses.create') }}"
                class="bg-black dark:bg-white text-white dark:text-zinc-900 px-4 py-2 rounded-lg font-bold hover:bg-gray-800 dark:hover:bg-zinc-200 transition">
                + Record Expense
            </a>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead class="bg-gray-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase">Category & Staff</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase">Amount</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-zinc-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                    @foreach($expenses as $expense)
                        <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-zinc-300">
                                {{ $expense->expense_date->format('M d, Y') }}
                            </td>
                            {{-- <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900 dark:text-zinc-100">{{ $expense->category }}</div>
                                <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-zinc-400">
                                    <flux:icon.user variant="micro" class="size-3" />
                                    {{-- Displays the name of the user who recorded it --
                                    <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ $expense->user->name ?? 'System' }}</span>
                                    <span class="text-gray-300 mx-1">|</span>
                                    <span>{{ $expense->payment_method }}</span>
                                </div>
                            </td> --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3"> {{-- Increased gap for breathing room --}}
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-gray-900 dark:text-zinc-100">{{ $expense->category }}</span>

                                            {{-- Move the icon here --}}
                                            <flux:tooltip position="top" align="center">
                                                <x-slot name="content">
                                                    <div class="max-w-94 whitespace-normal">
                                                        {{ $expense->notes }}
                                                    </div>
                                                </x-slot>

                                                <flux:icon.information-circle
                                                    class="size-4.5 mb-0.5 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 cursor-pointer" />
                                            </flux:tooltip>
                                        </div>

                                        <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-zinc-400">
                                            <flux:icon.user variant="micro" class="size-3" />
                                            <span
                                                class="font-medium text-indigo-600 dark:text-indigo-400">{{ $expense->user->name ?? 'System' }}</span>
                                            <span class="text-gray-300 mx-1">|</span>
                                            <span>{{ $expense->payment_method }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-gray-900 dark:text-zinc-100">
                                {{ number_format($expense->amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($expense->status == 'confirmed')
                                    <span
                                        class="px-2 py-1 text-xs font-bold bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded-full">VERIFIED</span>
                                @elseif($expense->status == 'rejected')
                                    <span
                                        class="px-2 py-1 text-xs font-bold bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 rounded-full">REJECTED</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-xs font-bold bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300 rounded-full">PENDING</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                @if($expense->status == 'pending')
                                    <a href="{{ route('expenses.edit', $expense) }}"
                                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-bold mr-3">Edit</a>
                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Delete this record?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 font-bold">Delete</button>
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