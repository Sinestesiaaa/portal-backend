<x-app-layout>
    <div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">

        <h2 class="text-xl font-bold text-green-700 mb-4">Tambah Pengguna</h2>

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <label class="block mb-2">Nama</label>
            <input type="text" name="name" class="w-full border p-2 rounded mb-3" required>

            <label class="block mb-2">Email</label>
            <input type="email" name="email" class="w-full border p-2 rounded mb-3" required>

            <label class="block mb-2">Role</label>
            <select name="role_id" class="w-full border p-2 rounded mb-3">
                <option value="2">User</option>
                <option value="1">Admin</option>
            </select>

            <label class="block mb-2">Password</label>
            <input type="password" name="password" class="w-full border p-2 rounded mb-3" required>

            <label class="block mb-2">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="w-full border p-2 rounded mb-3" required>

            <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Simpan
            </button>
        </form>

    </div>
</x-app-layout>
