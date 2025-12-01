<x-app-layout>
    <div class="max-w-6xl mx-auto mt-8">

        <h1 class="text-2xl font-bold mb-6 text-green-700">Manajemen Pengguna</h1>

        @if (session('success'))
        <div class="p-3 bg-green-200 text-green-800 rounded mb-3">
            {{ session('success') }}
        </div>
        @endif

        <a href="{{ route('admin.users.create') }}"
            class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">
            + Tambah Pengguna
        </a>

        <div class="mt-6 bg-white p-4 rounded shadow">
            <table class="w-full border-collapse">
                <thead class="bg-green-100">
                    <tr>
                        <th class="border p-2">Nama</th>
                        <th class="border p-2">Email</th>
                        <th class="border p-2">Role</th>
                        <th class="border p-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    <tr>
                        <td class="border p-2">{{ $user->name }}</td>
                        <td class="border p-2">{{ $user->email }}</td>
                        <td class="border p-2">
                            {{ $user->role_id == 1 ? 'Admin' : 'User' }}
                        </td>
                        <td class="border p-2 text-center">
                            <a href="{{ route('admin.users.edit', $user->id) }}"
                                class="text-blue-600 hover:underline">Edit</a>

                            <form action="{{ route('admin.users.destroy', $user->id) }}"
                                method="POST" class="inline-block"
                                onsubmit="return confirm('Hapus akun ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-red-600 hover:underline ml-3">
                                    Hapus
                                </button>
                            </form>

                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
