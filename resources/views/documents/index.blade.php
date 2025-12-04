@push('styles')
    <style>
        .tooltip {
            position: relative;
            cursor: pointer;
        }

        .tooltip .tooltip-text {
            visibility: hidden;
            opacity: 0;
            transition: 0.2s;
            position: absolute;
            background: rgba(0, 0, 0, 0.75);
            color: #fff;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            white-space: nowrap;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 50;
        }

        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }

        .tag svg {
            width: 14px;
            height: 14px;
            stroke-width: 2;
        }
    </style>
@endpush


<x-app-layout>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

        {{-- PAGE HEADER --}}
        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">📄 Daftar Dokumen</h1>
            <p class="text-white/90 text-sm">Semua dokumen perusahaan ditampilkan di sini</p>
        </div>

        @if (session('success'))
            <div class="p-3 bg-green-200 text-green-900 rounded-lg shadow-sm">
                {{ session('success') }}
            </div>
        @endif


        {{-- ============================= --}}
        {{-- FILTER BAR --}}
        {{-- ============================= --}}
        <div class="bg-white p-5 rounded-xl shadow-md border">

            {{-- FILTER FORM --}}
            <form method="GET" id="filterForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">

                {{-- SEARCH --}}
                <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Cari dokumen..."
                    class="border border-gray-300 rounded-lg px-4 py-2 w-full
                focus:ring-2 focus:ring-[#16A34A] focus:border-[#0AA03A]">

                {{-- KATEGORI --}}
                <select name="kategori"
                    class="border border-gray-300 rounded-lg px-3 py-2
                focus:ring-2 focus:ring-[#16A34A] w-full">
                    <option value="">Kategori</option>
                    @foreach (['SOP', 'IK', 'FORM', 'STD'] as $k)
                        <option value="{{ $k }}" {{ request('kategori') == $k ? 'selected' : '' }}>
                            {{ $k }}
                        </option>
                    @endforeach
                </select>

                {{-- TANGGAL --}}
                <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 w-full
                focus:ring-2 focus:ring-[#16A34A]">

                {{-- DEPARTEMEN --}}
                @if (in_array(auth()->user()->role_id, [1, 2]))
                    <select name="department_id"
                        class="border border-gray-300 rounded-lg px-3 py-2
                    focus:ring-2 focus:ring-[#16A34A] w-full">
                        <option value="">Departemen</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}"
                                {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <div></div>
                @endif

            </form>

            {{-- BUTTONS --}}
            <div class="flex justify-between items-center mt-5">

                {{-- LEFT BUTTONS --}}
                <div class="flex gap-2">
                    <button form="filterForm"
                        class="bg-[#0AA03A] text-white px-5 py-2 rounded-lg shadow hover:bg-[#087C2D]">
                        Filter
                    </button>

                    <a href="{{ route('documents.index') }}"
                        class="bg-gray-300 px-5 py-2 rounded-lg shadow hover:bg-gray-400">
                        Reset
                    </a>
                </div>

                {{-- ADD DOC --}}
                @if (auth()->user()->role_id == 1)
                    <a href="{{ route('documents.create') }}"
                        class="flex items-center gap-2 bg-[#0AA03A] text-white px-5 py-2 rounded-lg shadow hover:bg-[#087C2D]">
                        + Tambah Dokumen
                    </a>
                @endif
            </div>

        </div>


        {{-- TOTAL --}}
        <p class="text-gray-700 text-sm ml-1">
            Menampilkan <b>{{ $totalResult }}</b> dokumen.
        </p>


        {{-- ============================= --}}
        {{-- BADGE CONFIG --}}
        {{-- ============================= --}}
        @php
            $categoryConfig = [
                'IK' => [
                    'color' => '#16A34A',
                    'text' => '#FFFFFF',
                    'tooltip' => 'Instruksi Kerja',
                    'icon' => '
            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke-width="2" stroke="white"
                class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 6h16M4 12h16M4 18h10" />
            </svg>
        ',
                ],

                'STD' => [
                    'color' => '#2563EB',
                    'text' => '#FFFFFF',
                    'tooltip' => 'Standar',
                    'icon' => '
            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke-width="2" stroke="white"
                class="w-4 h-4">
                <rect x="4" y="4" width="16" height="16" rx="2" />
            </svg>
        ',
                ],

                'FORM' => [
                    'color' => '#000000',
                    'text' => '#FFFFFF',
                    'tooltip' => 'Formulir',
                    'icon' => '
            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke-width="2" stroke="white"
                class="w-4 h-4">
                <rect x="6" y="4" width="12" height="16" rx="2" />
                <path d="M6 8h12" />
            </svg>
        ',
                ],

                'SOP' => [
                    'color' => '#EA580C',
                    'text' => '#FFFFFF',
                    'tooltip' => 'Standard Operating Procedure',
                    'icon' => '
            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke-width="2" stroke="white"
                class="w-4 h-4">
                <rect x="4" y="4" width="16" height="4" rx="1" />
                <rect x="4" y="12" width="16" height="8" rx="1" />
            </svg>
        ',
                ],
            ];

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


        {{-- ============================= --}}
        {{-- TABLE --}}
        {{-- ============================= --}}
        <div class="bg-white p-5 rounded-xl shadow-lg border overflow-x-auto">

            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#E8FCEB] text-[#0A7A2D] font-semibold text-left">
                        <th class="p-3">Nomor</th>
                        <th class="p-3">Judul</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3">Departemen</th>
                        <th class="p-3">Tanggal Terbit</th>
                        <th class="p-3 text-center">File</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($documents as $doc)
                        @php $deptName = $doc->department->name ?? '-'; @endphp

                        <tr class="border-b hover:bg-[#F3FAF6]">

                            <td class="p-3">{{ $doc->document_number }}</td>

                            <td class="p-3 font-medium text-gray-800">{{ $doc->title }}</td>

                            {{-- CATEGORY BADGE --}}
                            <td class="p-3">
                                @php $c = $categoryConfig[$doc->kategori] ?? null; @endphp
                                @if ($c)
                                    <span
                                        class="tooltip inline-flex items-center gap-1.5 px-3 py-1.5
    rounded-full shadow-sm text-xs font-semibold"
                                        style="background: {{ $c['color'] }}; color: {{ $c['text'] }};">

                                        {!! $c['icon'] !!}
                                        {{ $doc->kategori }}

                                        <span class="tooltip-text">{{ $c['tooltip'] }}</span>
                                    </span>
                                @endif
                            </td>

                            {{-- DEPARTMENT BADGE --}}
                            <td class="p-3">
                                @php $d = $departmentConfig[$deptName] ?? null; @endphp
                                @if ($d)
                                    <span
                                        class="tooltip inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold"
                                        style="background: {{ $d['color'] }}; color: {{ $d['text'] }};">
                                        {{ $d['icon'] }} {{ $deptName }}
                                        <span class="tooltip-text">Departemen: {{ $deptName }}</span>
                                    </span>
                                @else
                                    <span class="tag" style="background:#e5e7eb; color:#374151;">🏢
                                        {{ $deptName }}</span>
                                @endif
                            </td>

                            <td class="p-3 text-gray-600">{{ $doc->created_at->format('Y-m-d') }}</td>

                            <td class="p-3 text-center">
                                <button onclick="openPdfModal('{{ route('documents.preview', $doc->id) }}')"
                                    class="text-blue-600 hover:underline">Lihat</button>
                            </td>

                            <td class="p-3 text-center">
                                @if (auth()->user()->role_id == 1)
                                    <div class="flex justify-center gap-3">

                                        <a href="{{ route('documents.edit', $doc->id) }}"
                                            class="text-yellow-600 hover:text-yellow-700">✏️</a>

                                        <form action="{{ route('documents.destroy', $doc->id) }}" method="POST"
                                            onsubmit="return confirm('Hapus dokumen ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 hover:text-red-700">🗑️</button>
                                        </form>

                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="p-3 text-center text-gray-500">
                                Tidak ada dokumen ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

        {{-- PAGINATION --}}
        <div class="mt-4">
            {{ $documents->links() }}
        </div>

    </div>



    {{-- ============================= --}}
    {{-- MODAL PREVIEW --}}
    {{-- ============================= --}}
    <div id="pdfModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="relative bg-white rounded-xl shadow-2xl overflow-hidden w-[92%] max-w-6xl h-[92%] flex flex-col">

            <button onclick="closePdfModal()"
                class="absolute top-3 right-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-full px-4 py-1 shadow">
                X
            </button>

            <iframe id="pdfFrame" src="" class="w-full h-full" style="border:none;"></iframe>

        </div>
    </div>

    <script>
        function openPdfModal(url) {
            document.getElementById("pdfFrame").src = url + "?v=" + Date.now();
            document.getElementById("pdfModal").classList.remove("hidden");
        }

        function closePdfModal() {
            document.getElementById("pdfModal").classList.add("hidden");
            document.getElementById("pdfFrame").src = "";
        }
    </script>

</x-app-layout>
