<x-app-layout>

    <div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">

        <h2 class="text-xl font-bold text-green-700 mb-4">Tambah Pengguna</h2>

        {{-- ERROR VALIDATION --}}
        @if ($errors->any())
        <div class="bg-red-200 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc px-5">
                @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            {{-- Nama --}}
            <label class="block mb-2 font-semibold">Nama</label>
            <input type="text" name="name" value="{{ old('name') }}"
                class="w-full border p-2 rounded mb-4" required>

            {{-- Email --}}
            <label class="block mb-2 font-semibold">Email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                class="w-full border p-2 rounded mb-4" required>

            {{-- Role --}}
            <label class="block mb-2 font-semibold">Role</label>
            <select name="role_id"
                class="w-full border p-2 rounded mb-4">
                <option value="1" {{ old('role_id') == 1 ? 'selected' : '' }}>Admin</option>
                <option value="2" {{ old('role_id') == 2 ? 'selected' : '' }}>Super User</option>
                <option value="3" {{ old('role_id') == 3 ? 'selected' : '' }}>User</option>
            </select>

            {{-- Departemen --}}
            <label class="block mb-2 font-semibold">Departemen</label>
            <select name="department_id"
                class="w-full border p-2 rounded mb-4">
                <option value="">Pilih Departemen...</option>

                @foreach($departments as $dept)
                <option value="{{ $dept->id }}"
                    {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                    {{ $dept->name }}
                </option>
                @endforeach
            </select>

            {{-- Password --}}
            <label class="block mb-2 font-semibold">Password</label>
            <div class="relative mb-4">
                <input type="password" name="password"
                    class="w-full border p-2 rounded"
                    id="password" required>

                <button type="button" onclick="togglePassword('password')"
                    class="absolute right-3 top-2 text-gray-600">👁️</button>
            </div>

            {{-- Confirm Password --}}
            <label class="block mb-2 font-semibold">Konfirmasi Password</label>
            <div class="relative mb-6">
                <input type="password" name="password_confirmation"
                    class="w-full border p-2 rounded"
                    id="password_confirm" required>

                <button type="button" onclick="togglePassword('password_confirm')"
                    class="absolute right-3 top-2 text-gray-600">👁️</button>
            </div>

            <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full text-center">
                Simpan Pengguna
            </button>
        </form>

    </div>

    {{-- Show/Hide Password Script --}}
    <script>
        function togglePassword(id) {
            const field = document.getElementById(id);
            field.type = field.type === "password" ? "text" : "password";
        }
    </script>

</x-app-layout>
