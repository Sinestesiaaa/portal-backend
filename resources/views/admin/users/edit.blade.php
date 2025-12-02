<x-app-layout>

    <div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">

        <h2 class="text-xl font-bold text-green-700 mb-4">Edit Pengguna</h2>

        @if ($errors->any())
        <div class="bg-red-200 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc px-5">
                @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method("PUT")

            {{-- Nama --}}
            <label class="block mb-2 font-semibold">Nama</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                class="w-full border p-2 rounded mb-4" required>

            {{-- Email (read-only) --}}
            <label class="block mb-2 font-semibold">Email</label>
            <input type="email" value="{{ $user->email }}"
                class="w-full border p-2 rounded bg-gray-100 mb-4" disabled>

            {{-- Role --}}
            <label class="block mb-2 font-semibold">Role</label>
            <select name="role_id"
                class="w-full border p-2 rounded mb-4">

                <option value="1" {{ $user->role_id == 1 ? 'selected' : '' }}>Admin</option>
                <option value="2" {{ $user->role_id == 2 ? 'selected' : '' }}>Super User</option>
                <option value="3" {{ $user->role_id == 3 ? 'selected' : '' }}>User</option>

            </select>

            {{-- Departemen --}}
            <label class="block mb-2 font-semibold">Departemen</label>
            <select name="department_id"
                class="w-full border p-2 rounded mb-4">

                <option value="">Tidak Ada</option>

                @foreach($departments as $dept)
                <option value="{{ $dept->id }}"
                    {{ $user->department_id == $dept->id ? 'selected' : '' }}>
                    {{ $dept->name }}
                </option>
                @endforeach

            </select>

            <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full text-center">
                Update Pengguna
            </button>
        </form>

    </div>

</x-app-layout>
