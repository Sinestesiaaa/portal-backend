<x-app-layout>

    @push('styles')
        <style>
            .tag {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 4px 10px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 600;
                white-space: nowrap;
            }
        </style>
    @endpush

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

        {{-- PAGE HEADER --}}
        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">👥 Manajemen Pengguna</h1>
            <p class="text-white/90 text-sm">Kelola akun pengguna, role, dan departemen</p>
        </div>

        {{-- SUCCESS ALERT --}}
        @if (session('success'))
            <div class="p-3 bg-green-200 text-green-900 rounded-lg shadow-sm">
                {{ session('success') }}
            </div>
        @endif


        {{-- FILTER CARD --}}
        <div class="bg-white p-5 rounded-xl shadow-md border">

            {{-- FORM FILTER --}}
            <form method="GET" id="filterForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">

                {{-- SEARCH NAME --}}
                <input type="text" name="name" value="{{ request('name') }}" placeholder="🔍 Cari nama..."
                    class="border border-gray-300 rounded-lg px-4 py-2 w-full
                           focus:ring-2 focus:ring-[#16A34A] focus:border-[#0AA03A]">

                {{-- ROLE --}}
                <select name="role_id"
                    class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#16A34A] w-full">
                    <option value="">Semua Role</option>
                    <option value="1" {{ request('role_id') == 1 ? 'selected' : '' }}>Admin</option>
                    <option value="2" {{ request('role_id') == 2 ? 'selected' : '' }}>Super User</option>
                    <option value="3" {{ request('role_id') == 3 ? 'selected' : '' }}>User</option>
                </select>

                {{-- DEPARTMEN --}}
                <select name="department_id"
                    class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#16A34A] w-full">
                    <option value="">Semua Departemen</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}"
                            {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>

                <div></div>
            </form>

            {{-- ACTION BUTTONS --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mt-5">

                <div class="flex flex-col sm:flex-row gap-2">
                    <button type="submit" form="filterForm"
                        class="w-full sm:w-auto bg-[#0AA03A] text-white px-5 py-2 rounded-lg shadow hover:bg-[#087C2D]">
                        Filter
                    </button>

                    <a href="{{ route('admin.users.index') }}"
                        class="w-full sm:w-auto bg-gray-300 px-5 py-2 rounded-lg shadow hover:bg-gray-400 text-center">
                        Reset
                    </a>
                </div>

                @if (auth()->user()->role_id == 1)
                    <a href="{{ route('admin.users.create') }}"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 bg-[#0AA03A] text-white px-5 py-2 rounded-lg shadow hover:bg-[#087C2D]">
                        Tambah Pengguna
                    </a>
                @endif

            </div>

        </div>


        {{-- BADGE CONFIG --}}
        @php
            $departmentConfig = [
                'CPSD' => ['color' => '#bbf7d0', 'text' => '#166534', 'icon' => '🧩'],
                'ENG' => ['color' => '#fecaca', 'text' => '#991b1b', 'icon' => '🔧'],
                'SM' => ['color' => '#e0e7ff', 'text' => '#3730a3', 'icon' => '🧭'],
                'SHE' => ['color' => '#d1fae5', 'text' => '#065f46', 'icon' => '🛡️'],
                'SPD' => ['color' => '#fef9c3', 'text' => '#854d0e', 'icon' => '📊'],
                'FAT' => ['color' => '#dbeafe', 'text' => '#1e3a8a', 'icon' => '📘'],
                'PDV' => ['color' => '#ede9fe', 'text' => '#5b21b6', 'icon' => '🏭'],
                'GS' => ['color' => '#f3e8ff', 'text' => '#6b21a8', 'icon' => '🛠️'],
                'HC' => ['color' => '#fee2e2', 'text' => '#b91c1c', 'icon' => '👥'],
                'PLANT' => ['color' => '#dcfce7', 'text' => '#15803d', 'icon' => '🌱'],
                'OPR' => ['color' => '#e0f2fe', 'text' => '#0369a1', 'icon' => '⚙️'],
            ];
        @endphp


        {{-- TABLE --}}
        <div class="bg-white p-5 rounded-xl shadow-lg border overflow-x-auto">

            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#E8FCEB] text-[#0A7A2D] font-semibold text-left">
                        <th class="p-3">Nama</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">Departemen</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        @php
                            $deptName = $user->department->name ?? '-';
                            $dept = $departmentConfig[$deptName] ?? null;
                            $deptIcon = $user->department->icon ?? ($dept['icon'] ?? '🏢');
                        @endphp

                        <tr class="border-b hover:bg-[#F3FAF6]">

                            <td class="p-3 font-medium text-gray-800">
                                <a href="#" class="open-user text-blue-600 hover:underline"
                                    data-user='@json($user)'>
                                    {{ $user->name }}
                                </a>
                            </td>

                            <td class="p-3 text-gray-700">{{ $user->email }}</td>

                            {{-- ROLE --}}
                            <td class="p-3">
                                @if ($user->role_id == 1)
                                    <span class="tag bg-red-200 text-red-900">🔑 Admin</span>
                                @elseif($user->role_id == 2)
                                    <span class="tag bg-blue-200 text-blue-900">⭐ Super User</span>
                                @else
                                    <span class="tag bg-green-200 text-green-900">👤 User</span>
                                @endif
                            </td>

                            {{-- DEPARTEMEN --}}
                            <td class="p-3">
                                @if ($dept)
                                    <span class="tag"
                                        style="background: {{ $dept['color'] }}; color: {{ $dept['text'] }};">
                                        {{ $deptIcon }} {{ $deptName }}
                                    </span>
                                @else
                                    <span class="tag bg-gray-200 text-gray-700">{{ $deptIcon }}
                                        {{ $deptName }}</span>
                                @endif
                            </td>

                            <td class="p-3 text-center">
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                    class="text-yellow-600 hover:text-yellow-700 mr-3">✏️</a>

                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                    class="inline" onsubmit="return confirm('Hapus pengguna ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:text-red-700">🗑️</button>
                                </form>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">
                                Tidak ada pengguna ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $users->links() }}
            </div>

        </div>


        {{-- MODAL DETAIL USER --}}
        <div id="userModal" class="fixed inset-0 bg-black/40 hidden justify-center items-center z-50">

            <div class="bg-white w-[92%] max-w-md p-6 rounded shadow-lg relative">

                <button onclick="closeUserModal()" class="absolute top-2 right-3 text-red-500 font-bold">X</button>

                <h2 class="text-xl font-bold mb-4">Detail Pengguna</h2>

                <div id="modalContent" class="space-y-2 text-gray-700"></div>

            </div>
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
                    <p><b>Role:</b> ${
                        u.role_id == 1 ? 'Admin' :
                        u.role_id == 2 ? 'Super User' :
                        'User'
                    }</p>
                    <p><b>Departemen:</b> ${u.department ? u.department.name : '-'}</p>
                `;

                document.getElementById('userModal').classList.remove('hidden');
            });
        });

        function closeUserModal() {
            document.getElementById('userModal').classList.add('hidden');
        }
    </script>

</x-app-layout>
