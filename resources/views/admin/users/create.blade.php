<x-app-layout>

    <div class="max-w-3xl mx-auto py-10 px-6 space-y-6">

        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">➕ Tambah Pengguna Baru</h1>
            <p class="text-white/90 text-sm">Buat akun baru untuk mengakses sistem</p>
        </div>

        {{-- ERROR ALERT --}}
        @if ($errors->any())
            <div class="bg-red-200 text-red-800 p-4 rounded-lg shadow">
                <ul class="list-disc px-5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- FORM CARD --}}
        <div class="bg-white p-6 rounded-xl shadow-md border">

            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- NAMA --}}
                    <div>
                        <label class="font-semibold text-gray-700">Nama</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="mt-1 w-full border rounded-lg px-4 py-2
                                   focus:ring-2 focus:ring-[#0AA03A]"
                            required>
                    </div>

                    {{-- EMAIL --}}
                    <div>
                        <label class="font-semibold text-gray-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="mt-1 w-full border rounded-lg px-4 py-2
                                   focus:ring-2 focus:ring-[#0AA03A]"
                            required>
                    </div>

                    {{-- ROLE --}}
                    <div>
                        <label class="font-semibold text-gray-700">Role</label>
                        <select name="role_id"
                            class="mt-1 w-full border rounded-lg px-4 py-2
                                   focus:ring-2 focus:ring-[#0AA03A]">
                            <option value="1" {{ old('role_id') == 1 ? 'selected' : '' }}>Admin</option>
                            <option value="2" {{ old('role_id') == 2 ? 'selected' : '' }}>Super User</option>
                            <option value="3" {{ old('role_id') == 3 ? 'selected' : '' }}>User</option>
                        </select>
                    </div>

                    {{-- DEPARTEMEN --}}
                    <div>
                        <label class="font-semibold text-gray-700">Departemen</label>
                        <select name="department_id"
                            class="mt-1 w-full border rounded-lg px-4 py-2
                                   focus:ring-2 focus:ring-[#0AA03A]">
                            <option value="">Pilih Departemen...</option>

                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PASSWORD --}}
                    <div>
                        <label class="font-semibold text-gray-700">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password"
                                class="mt-1 w-full border rounded-lg px-4 py-2 pr-10
                                       focus:ring-2 focus:ring-[#0AA03A]"
                                required>

                            <button type="button" onclick="togglePassword('password')"
                                class="absolute right-3 top-3 text-gray-600 hover:text-gray-900">
                                👁️
                            </button>
                        </div>
                    </div>

                    {{-- CONFIRM PASSWORD --}}
                    <div>
                        <label class="font-semibold text-gray-700">Konfirmasi Password</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirm"
                                class="mt-1 w-full border rounded-lg px-4 py-2 pr-10
                                       focus:ring-2 focus:ring-[#0AA03A]"
                                required>

                            <button type="button" onclick="togglePassword('password_confirm')"
                                class="absolute right-3 top-3 text-gray-600 hover:text-gray-900">
                                👁️
                            </button>
                        </div>
                    </div>

                </div>

                {{-- BUTTONS --}}
                <div class="flex justify-between mt-8">

                    <a href="{{ route('admin.users.index') }}"
                        class="px-6 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 shadow">
                        Batal
                    </a>

                    <button class="px-6 py-2 bg-[#0AA03A] text-white rounded-lg shadow hover:bg-[#087C2D]">
                        Simpan Pengguna
                    </button>

                </div>

            </form>

        </div>

    </div>


    {{-- PASSWORD TOGGLE SCRIPT --}}
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }
    </script>

</x-app-layout>
