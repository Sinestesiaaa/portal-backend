<x-app-layout>
    <div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">

        <h2 class="text-xl font-bold text-green-700 mb-4">Edit Pengguna</h2>

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <label class="block mb-2">Nama</label>
            <input type="text" name="name" value="{{ $user->name }}" class="w-full border p-2 rounded mb-3" required>

            <label class="block mb-2">Email</label>
            <input type="email" name="email" value="{{ $user->email }}" class="w-full border p-2 rounded mb-3" required>

            <label class="block mb-2">Role</label>
            <select name="role_id" class="w-full border p-2 rounded mb-3">
                <option value="1" {{ $user->role_id == 1 ? 'selected' : '' }}>Admin</option>
                <option value="2" {{ $user->role_id == 2 ? 'selected' : '' }}>User</option>
            </select>

            <label class="block mb-2">Password (opsional)</label>
            <input type="password" name="password" class="w-full border p-2 rounded mb-3">

            <label class="block mb-2">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="w-full border p-2 rounded mb-3">

            <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Update
            </button>
        </form>

    </div>
</x-app-layout>
