<x-layouts::app title="Edit Room">

    <form method="POST" action="{{ route('rooms.update', $room) }}" class="max-w-md">
        @csrf
        @method('PUT')

        <input type="text" name="name" value="{{ $room->name }}" class="border w-full p-3 rounded mb-4" required>

        <select name="status" class="border w-full p-3 rounded mb-4">
            <option value="free" {{ $room->status == 'free' ? 'selected' : '' }}>Free</option>
            <option value="occupied" {{ $room->status == 'occupied' ? 'selected' : '' }}>Occupied</option>
        </select>

        <button class="bg-blue-600 text-white px-4 py-2 rounded">
            Update
        </button>
    </form>

</x-layouts::app>
