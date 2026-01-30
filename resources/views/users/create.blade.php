<x-layouts::app title="Create User">

    <h1 class="text-xl font-semibold mb-4">Create User</h1>

    @if ($errors->any())
        <div class="mb-3 p-2 bg-red-100 text-red-800 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.store') }}" class="max-w-md">
        @csrf

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border px-2 py-1" required>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border px-2 py-1" required>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Password</label>
            <input type="password" name="password" class="w-full border px-2 py-1" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Role</label>
            <select name="role" class="w-full border px-2 py-1" required>
                <option value="cashier">Cashier</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">
                Create User
            </button>

            <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-300 rounded">
                Cancel
            </a>
        </div>
    </form>

</x-layouts::app>
