<x-layouts::app title="Add Room">

    <form method="POST" action="{{ route('rooms.store') }}" class="max-w-md">
        @csrf

        <input type="text" name="name" placeholder="Room name"
               class="border w-full p-3 rounded mb-4" required>

        <button class="bg-green-600 text-white px-4 py-2 rounded">
            Save
        </button>
    </form>

</x-layouts::app>
