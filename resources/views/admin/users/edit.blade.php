<x-app-layout>

    <div class="max-w-6xl mx-auto px-6 py-10 grid grid-cols-1 lg:grid-cols-2 gap-8">

        {{-- ========================= --}}
        {{-- LEFT SIDE — EDIT FORM     --}}
        {{-- ========================= --}}
        <div class="bg-white p-8 rounded-2xl shadow-md border">

            <h2 class="text-2xl font-bold text-[#0AA03A] mb-6">✏️ Edit Pengguna</h2>

            {{-- ERROR VALIDATION --}}
            @if ($errors->any())
                <div class="bg-red-200 text-red-800 p-3 rounded mb-4">
                    <ul class="list-disc px-5 text-sm">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Nama --}}
                <label class="block mb-2 font-semibold text-gray-700">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                    class="w-full border rounded-lg px-4 py-2 mb-4 focus:ring-2 focus:ring-[#0AA03A]" required>

                {{-- Email (readonly) --}}
                <label class="block mb-2 font-semibold text-gray-700">Email</label>
                <input type="email" value="{{ $user->email }}" disabled
                    class="w-full border rounded-lg px-4 py-2 bg-gray-100 text-gray-600 mb-4">

                {{-- Role --}}
                <label class="block mb-2 font-semibold text-gray-700">Role</label>
                <select name="role_id"
                    class="w-full border rounded-lg px-4 py-2 mb-4 focus:ring-2 focus:ring-[#0AA03A]">
                    <option value="1" {{ $user->role_id == 1 ? 'selected' : '' }}>Admin</option>
                    <option value="2" {{ $user->role_id == 2 ? 'selected' : '' }}>Super User</option>
                    <option value="3" {{ $user->role_id == 3 ? 'selected' : '' }}>User</option>
                </select>

                {{-- Departemen --}}
                <label class="block mb-2 font-semibold text-gray-700">Departemen</label>
                <select name="department_id"
                    class="w-full border rounded-lg px-4 py-2 mb-6 focus:ring-2 focus:ring-[#0AA03A]">

                    <option value="">Tidak Ada</option>

                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $user->department_id == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Password Baru --}}
                <label class="block mb-2 font-semibold">Password Baru (Opsional)</label>

                <div class="relative mb-4">
                    <input type="password" name="password" id="password" class="w-full border p-2 rounded"
                        placeholder="Kosongkan jika tidak ingin mengganti password">

                    <button type="button" onclick="togglePassword('password')"
                        class="absolute right-3 top-2 text-gray-600">👁️</button>
                </div>

                <p class="text-xs text-gray-500 mb-4">
                    Kosongkan jika tidak ingin mereset password pengguna.
                </p>


                <div class="flex flex-col sm:flex-row sm:justify-end gap-3 mt-6">
                    {{-- BATAL --}}
                    <a href="{{ route('admin.users.index') }}"
                        class="w-full sm:w-auto px-5 py-2 rounded-lg border border-gray-300 text-gray-700
               bg-gray-100 hover:bg-gray-200 transition shadow-sm">
                        Batal
                    </a>

                    {{-- UPDATE --}}
                    <button type="submit"
                        class="w-full sm:w-auto px-5 py-2 rounded-lg bg-[#0AA03A] text-white font-semibold shadow
               hover:bg-[#087C2D] transition">
                        Update Pengguna
                    </button>
                </div>


            </form>

        </div>


        {{-- ========================= --}}
        {{-- RIGHT SIDE — USER SUMMARY --}}
        {{-- ========================= --}}
        @php
            $roleBadge = [
                1 => ['Admin', 'bg-red-200 text-red-800', '🔑'],
                2 => ['Super User', 'bg-blue-200 text-blue-800', '⭐'],
                3 => ['User', 'bg-green-200 text-green-800', '👤'],
            ];

            $deptConf = [
                'CPSD' => ['#bbf7d0', '#166534', '🧩'],
                'ENG' => ['#fecaca', '#991b1b', '🔧'],
                'SM' => ['#e0e7ff', '#3730a3', '🧭'],
                'SHE' => ['#d1fae5', '#065f46', '🛡️'],
                'SPD' => ['#fef9c3', '#854d0e', '📊'],
                'FAT' => ['#dbeafe', '#1e3a8a', '📘'],
                'PDV' => ['#ede9fe', '#5b21b6', '🏭'],
                'GS' => ['#f3e8ff', '#6b21a8', '🛠️'],
                'HC' => ['#fee2e2', '#b91c1c', '👥'],
                'PLANT' => ['#dcfce7', '#15803d', '🌱'],
                'OPR' => ['#e0f2fe', '#0369a1', '⚙️'],
            ];

            $deptName = $user->department->name ?? '-';
            $dept = $deptConf[$deptName] ?? null;
        @endphp

        <div class="bg-white p-8 rounded-2xl shadow-md border">

            <h2 class="text-2xl font-bold text-[#0AA03A] mb-4">👤 Detail Pengguna</h2>

            <div class="space-y-4 text-gray-700">

                <div>
                    <p class="font-semibold text-gray-600">Nama:</p>
                    <p>{{ $user->name }}</p>
                </div>

                <div>
                    <p class="font-semibold text-gray-600">Email:</p>
                    <p>{{ $user->email }}</p>
                </div>

                <div>
                    <p class="font-semibold text-gray-600">Role:</p>
                    <span
                        class="px-3 py-1 rounded-full text-sm inline-flex items-center gap-1 {{ $roleBadge[$user->role_id][1] }}">
                        {{ $roleBadge[$user->role_id][2] }} {{ $roleBadge[$user->role_id][0] }}
                    </span>
                </div>

                <div>
                    <p class="font-semibold text-gray-600">Departemen:</p>

                    @if ($dept)
                        <span class="px-3 py-1 rounded-full text-sm inline-flex items-center gap-1"
                            style="background: {{ $dept[0] }}; color: {{ $dept[1] }};">
                            {{ $dept[2] }} {{ $deptName }}
                        </span>
                    @else
                        <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm"> - </span>
                    @endif
                </div>

                {{-- Timestamp FIXED --}}
                <div class="pt-4 border-t">
                    <p class="text-sm font-semibold text-gray-600">Dibuat Pada:</p>
                    <p class="text-gray-600">
                        {{ $user->created_at ? $user->created_at->format('d M Y H:i') : '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-600">Terakhir Diperbarui:</p>
                    <p class="text-gray-600">
                        {{ $user->updated_at ? $user->updated_at->format('d M Y H:i') : '-' }}
                    </p>
                </div>

            </div>
        </div>

    </div>

</x-app-layout>

<script>
    function togglePassword(id) {
        const field = document.getElementById(id);
        field.type = field.type === "password" ? "text" : "password";
    }
</script>
