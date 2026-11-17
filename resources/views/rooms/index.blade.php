<x-layouts::app title="Rooms">

    <div class="flex justify-between mb-4">
        <h1 class="text-xl font-bold">Rooms</h1>
        <a href="{{ route('rooms.create') }}" class="bg-black dark:bg-white text-white dark:text-zinc-900 px-4 py-2 rounded">
            + Add Room
        </a>
    </div>

    <div class="bg-white dark:bg-zinc-900 rounded shadow">
        <table class="w-full">
            <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-right">Outstanding Balance</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rooms as $room)
                    <tr class="border-t">
                        <td class="p-3 font-bold">Room {{ $room->name }}</td>
                        <td class="p-3 text-right text-red-600 dark:text-red-400 font-bold">
                            {{ number_format($room->currentBalance(), 2) }}
                        </td>
                        <td class="p-3 text-center flex gap-4 justify-center">
                            <a href="{{ route('rooms.folio', $room) }}" class="text-green-600 dark:text-green-400 font-bold hover:underline">View Folio</a>
                            <a href="{{ route('rooms.edit', $room) }}" class="text-blue-600 dark:text-blue-400">Edit</a>

                            <form method="POST" action="{{ route('rooms.destroy', $room) }}">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 dark:text-red-400">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-layouts::app>
