<x-app-layout>

    <div class="max-w-7xl mx-auto px-6 py-10">

        <h1 class="text-2xl font-bold mb-6 text-green-700">Manajemen Pengguna</h1>

        {{-- SUCCESS ALERT --}}
        @if (session('success'))
        <div class="p-3 bg-green-200 text-green-800 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        {{-- BUTTON + FILTER WRAPPER --}}
        <div class="flex justify-between items-center mb-6">

            {{-- FILTER LEFT --}}
            <form method="GET" class="flex items-center gap-3">

                <input type="text" name="name" placeholder="Cari nama..."
                    value="{{ request('name') }}"
                    class="border rounded px-3 py-2 w-64">

                <select name="role_id" class="border rounded px-3 py-2">
                    <option value="">Semua Role</option>
                    <option value="1" {{ request('role_id')==1 ? 'selected':'' }}>Admin</option>
                    <option value="2" {{ request('role_id')==2 ? 'selected':'' }}>Super User</option>
                    <option value="3" {{ request('role_id')==3 ? 'selected':'' }}>User</option>
                </select>

                <select name="department_id" class="border rounded px-3 py-2">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id')==$dept->id ? 'selected':'' }}>
                        {{ $dept->name }}
                    </option>
                    @endforeach
                </select>

                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Filter</button>

                <a href="{{ route('admin.users.index') }}"
                    class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
                    Reset
                </a>
            </form>

            {{-- BUTTON TAMBAH DI KANAN --}}
            <a href="{{ route('admin.users.create') }}"
                class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">
                + Tambah Pengguna
            </a>
        </div>


        {{-- DEPARTEMEN CONFIG --}}
        @php
        $departmentConfig = [
        'CPSD' => ['color'=>'#bbf7d0', 'text'=>'#166534', 'icon'=>'🧩'],
        'ENG' => ['color'=>'#fecaca', 'text'=>'#991b1b', 'icon'=>'🔧'],
        'SM' => ['color'=>'#e0e7ff', 'text'=>'#3730a3', 'icon'=>'🧭'],
        'SHE' => ['color'=>'#d1fae5', 'text'=>'#065f46', 'icon'=>'🛡️'],
        'SPD' => ['color'=>'#fef9c3', 'text'=>'#854d0e', 'icon'=>'📊'],
        'FAT' => ['color'=>'#dbeafe', 'text'=>'#1e3a8a', 'icon'=>'📘'],
        'PDV' => ['color'=>'#ede9fe', 'text'=>'#5b21b6', 'icon'=>'🏭'],
        'GS' => ['color'=>'#f3e8ff', 'text'=>'#6b21a8', 'icon'=>'🛠️'],
        'HC' => ['color'=>'#fee2e2', 'text'=>'#b91c1c', 'icon'=>'👥'],
        'PLANT'=> ['color'=>'#dcfce7', 'text'=>'#15803d', 'icon'=>'🌱'],
        'OPR' => ['color'=>'#e0f2fe', 'text'=>'#0369a1', 'icon'=>'⚙️'],
        ];
        @endphp


        {{-- TABLE --}}
        <div class="bg-white p-4 rounded shadow overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-green-100">
                    <tr>
                        <th class="border p-2">Nama</th>
                        <th class="border p-2">Email</th>
                        <th class="border p-2">Role</th>
                        <th class="border p-2">Departemen</th>
                        <th class="border p-2">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($users as $user)
                    <tr class="hover:bg-gray-50">

                        {{-- NAMA --}}
                        <td class="border p-2">
                            <a href="#" class="text-blue-600 hover:underline open-user"
                                data-user='@json($user)'>
                                {{ $user->name }}
                            </a>
                        </td>

                        {{-- EMAIL --}}
                        <td class="border p-2">{{ $user->email }}</td>

                        {{-- ROLE BADGE --}}
                        <td class="border p-2">
                            @if($user->role_id == 1)
                            <span class="px-3 py-1 rounded-full text-sm flex items-center gap-1 bg-red-200 text-red-900 w-fit">
                                🔑 Admin
                            </span>
                            @elseif($user->role_id == 2)
                            <span class="px-3 py-1 rounded-full text-sm flex items-center gap-1 bg-blue-200 text-blue-900 w-fit">
                                ⭐ Super User
                            </span>
                            @else
                            <span class="px-3 py-1 rounded-full text-sm flex items-center gap-1 bg-green-200 text-green-900 w-fit">
                                👤 User
                            </span>
                            @endif
                        </td>

                        {{-- DEPARTEMEN BADGE --}}
                        <td class="border p-2">
                            @php
                            $deptName = $user->department->name ?? null;
                            $dept = $departmentConfig[$deptName] ?? null;
                            @endphp

                            @if($dept)
                            <span class="px-3 py-1 rounded-full text-sm flex items-center gap-1 w-fit"
                                style="background-color: {{ $dept['color'] }}; color: {{ $dept['text'] }};">
                                {{ $dept['icon'] }} {{ $deptName }}
                            </span>
                            @else
                            <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm">-</span>
                            @endif
                        </td>


                        {{-- ACTION --}}
                        <td class="border p-2 text-center">

                            <a href="{{ route('admin.users.edit', $user->id) }}"
                                class="text-yellow-600 hover:underline">Edit</a>

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

            {{-- PAGINATION --}}
            <div class="mt-4">
                {{ $users->links() }}
            </div>

        </div>


        {{-- USER DETAIL MODAL --}}
        <div id="userModal"
            class="fixed inset-0 bg-black/40 hidden justify-center items-center z-50">

            <div class="bg-white w-96 p-6 rounded shadow-lg relative">

                <button onclick="closeUserModal()"
                    class="absolute top-2 right-3 text-red-500 font-bold">X</button>

                <h2 class="text-xl font-bold mb-4">Detail Pengguna</h2>

                <div id="modalContent" class="space-y-2 text-gray-700"></div>

            </div>
        </div>


        {{-- MODAL SCRIPT --}}
        <script>
            document.querySelectorAll('.open-user').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const u = JSON.parse(this.dataset.user);

                    document.getElementById('modalContent').innerHTML = `
                        <p><b>Nama:</b> ${u.name}</p>
                        <p><b>Email:</b> ${u.email}</p>
                        <p><b>Role:</b> ${u.role_id == 1 ? 'Admin' : u.role_id == 2 ? 'Super User' : 'User'}</p>
                        <p><b>Departemen:</b> ${u.department ? u.department.name : '-'}</p>
                    `;

                    document.getElementById('userModal').classList.remove('hidden');
                });
            });

            function closeUserModal() {
                document.getElementById('userModal').classList.add('hidden');
            }
        </script>

    </div>

</x-app-layout>