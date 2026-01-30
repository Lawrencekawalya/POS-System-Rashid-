<x-layouts::app title="User Management">

    <h1 class="text-xl font-semibold mb-4">User Management</h1>

    @if (session('success'))
        <div class="mb-3 p-2 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-3 p-2 bg-red-100 text-red-800 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4">
        <a href="{{ route('users.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded text-sm">
            Add User
        </a>
    </div>

    <table class="w-full border text-sm">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">Name</th>
                <th class="border px-2 py-1">Email</th>
                <th class="border px-2 py-1 text-center">Role</th>
                <th class="border px-2 py-1 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td class="border px-2 py-1">
                        {{ $user->name }}
                    </td>
                    <td class="border px-2 py-1">
                        {{ $user->email }}
                    </td>
                    <td class="border px-2 py-1 text-center">
                        {{ ucfirst($user->role) }}
                    </td>
                    <td class="border px-2 py-1 text-center space-x-2">
                        <a href="{{ route('users.edit', $user) }}" class="text-blue-600 hover:underline">
                            Edit
                        </a>

                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Delete this user?')" class="text-red-600 hover:underline">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="border px-2 py-2 text-center text-gray-500">
                        No users found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</x-layouts::app>
