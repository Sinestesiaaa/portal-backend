@push('styles')
<style>
    .tooltip {
        position: relative;
        cursor: pointer;
    }

    .tooltip .tooltip-text {
        visibility: hidden;
        opacity: 0;
        transition: 0.2s ease-in-out;
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
        font-size: 13px;
        font-weight: 600;
        color: white;
    }

    .tag svg {
        width: 16px;
        height: 16px;
        stroke-width: 2;
    }
</style>
@endpush

<x-app-layout>

    <div class="max-w-7xl mx-auto px-6 py-10">

        <h1 class="text-2xl font-bold mb-6 text-[#1e8f4d]">Daftar Dokumen</h1>

        @if (session('success'))
        <div class="p-3 bg-green-200 text-green-900 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        {{-- FILTER --}}
        <div class="flex items-center justify-between mb-4">

            <form method="GET" class="flex items-center gap-2">

                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari dokumen..."
                    class="border rounded px-3 py-2 w-64">

                <select name="kategori" class="border rounded px-3 py-2">
                    <option value="">Semua Kategori</option>
                    <option value="SOP" {{ request('kategori')=='SOP'  ? 'selected' : '' }}>SOP</option>
                    <option value="IK" {{ request('kategori')=='IK'   ? 'selected' : '' }}>IK</option>
                    <option value="FORM" {{ request('kategori')=='FORM' ? 'selected' : '' }}>FORM</option>
                    <option value="STD" {{ request('kategori')=='STD'  ? 'selected' : '' }}>STD</option>
                </select>

                <input type="date" name="tanggal"
                    value="{{ request('tanggal') }}"
                    class="border rounded px-3 py-2">

                @if(in_array(auth()->user()->role_id, [1, 2]))
                <select name="department_id" class="border rounded px-3 py-2">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                    @endforeach
                </select>
                @endif

                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Filter</button>

                <a href="{{ route('documents.index') }}"
                    class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
                    Reset
                </a>
            </form>

            @if(auth()->user()->role_id == 1)
            <a href="{{ route('documents.create') }}"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 shadow">
                Tambah Dokumen
            </a>
            @endif
        </div>

        <p class="text-gray-700 mb-4">
            Menampilkan <b>{{ $totalResult }}</b> dokumen hasil filter.
        </p>

        {{-- BADGE CONFIG --}}
        @php
        $categoryConfig = [
        'IK' => ['color'=>'background-color:#16A34A', 'icon'=>'<svg fill="none" stroke="white" viewBox="0 0 24 24">
            <path d="M4 6h16M4 12h16M4 18h10" />
        </svg>', 'tooltip'=>'Instruksi Kerja'],
        'STD' => ['color'=>'background-color:#2563EB', 'icon'=>'<svg fill="none" stroke="white" viewBox="0 0 24 24">
            <path d="M4 4h16v16H4z" />
        </svg>', 'tooltip'=>'Standar'],
        'FORM' => ['color'=>'background-color:#000000','icon'=>'<svg fill="none" stroke="white" viewBox="0 0 24 24">
            <path d="M6 4h12v16H6z" />
            <path d="M6 8h12" />
        </svg>', 'tooltip'=>'Formulir'],
        'SOP' => ['color'=>'background-color:#EA580C','icon'=>'<svg fill="none" stroke="white" viewBox="0 0 24 24">
            <path d="M4 4h16v4H4z" />
            <path d="M4 12h16v8H4z" />
        </svg>', 'tooltip'=>'Standar Operasional Prosedur'],
        ];

        $departmentConfig = [
        'CPSD'=>['color'=>'background-color:#bbf7d0;color:#166534','icon'=>'🧩'],
        'ENG' =>['color'=>'background-color:#fecaca;color:#991b1b','icon'=>'🔧'],
        'SM' =>['color'=>'background-color:#e0e7ff;color:#3730a3','icon'=>'🧭'],
        'SHE' =>['color'=>'background-color:#d1fae5;color:#065f46','icon'=>'🛡️'],
        'SPD' =>['color'=>'background-color:#fef9c3;color:#854d0e','icon'=>'📊'],
        'FAT' =>['color'=>'background-color:#dbeafe;color:#1e3a8a','icon'=>'📘'],
        'PDV' =>['color'=>'background-color:#ede9fe;color:#5b21b6','icon'=>'🏭'],
        'GS' =>['color'=>'background-color:#f3e8ff;color:#6b21a8','icon'=>'🛠️'],
        'HC' =>['color'=>'background-color:#fee2e2;color:#b91c1c','icon'=>'👥'],
        'PLANT'=>['color'=>'background-color:#dcfce7;color:#15803d','icon'=>'🌱'],
        'OPR' =>['color'=>'background-color:#e0f2fe;color:#0369a1','icon'=>'⚙️'],
        ];
        @endphp

        {{-- TABLE --}}
        <div class="bg-white p-4 rounded-lg shadow border overflow-x-auto">

            <table class="w-full border-collapse">
                <thead class="bg-[#e6f6ec]">
                    <tr>
                        <th class="p-3">Nomor</th>
                        <th class="p-3">Judul</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3">Departemen</th>
                        <th class="p-3">Tanggal Terbit</th>
                        <th class="p-3">File</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($documents as $doc)
                    @php
                    $deptName = $doc->department->name ?? '-';
                    @endphp

                    <tr class="hover:bg-gray-50 border-b">

                        <td class="p-3">{{ $doc->document_number }}</td>

                        <td class="p-3">{{ $doc->title }}</td>

                        <td class="p-3">
                            @if(isset($categoryConfig[$doc->kategori]))
                            @php $ct = $categoryConfig[$doc->kategori]; @endphp
                            <div class="tooltip">
                                <span class="tag" style="{{ $ct['color'] }}">
                                    {!! $ct['icon'] !!}
                                    {{ $doc->kategori }}
                                </span>
                                <span class="tooltip-text">{{ $ct['tooltip'] }}</span>
                            </div>
                            @else
                            {{ $doc->kategori }}
                            @endif
                        </td>

                        <td class="p-3">
                            @if(isset($departmentConfig[$deptName]))
                            @php $dp = $departmentConfig[$deptName]; @endphp
                            <div class="tooltip">
                                <span class="tag"
                                    style="background:none; border:1px solid #ddd; {{ $dp['color'] }}">
                                    {{ $dp['icon'] }} {{ $deptName }}
                                </span>
                                <span class="tooltip-text">Departemen: {{ $deptName }}</span>
                            </div>
                            @else
                            <span class="tag" style="background:#e5e7eb; color:#374151;">
                                🏢 {{ $deptName }}
                            </span>
                            @endif
                        </td>

                        <td class="p-3">
                            {{ $doc->created_at ? $doc->created_at->format('Y-m-d') : '-' }}
                        </td>

                        <td class="p-3">
                            <button onclick="openPdfModal('{{ route('documents.preview', $doc->id) }}')"
                                class="text-blue-600 hover:underline">
                                Lihat
                            </button>
                        </td>

                        <td class="p-3">
                            @if(auth()->user()->role_id == 1)
                            <a href="{{ route('documents.edit', $doc->id) }}"
                                class="text-yellow-600 mr-2">Edit</a>

                            <form action="{{ route('documents.destroy', $doc->id) }}" method="POST"
                                class="inline" onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600">Hapus</button>
                            </form>
                            @else
                            <span class="text-gray-400">Tidak ada aksi</span>
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

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $documents->links() }}
        </div>

    </div>




    {{-- MODAL PREVIEW --}}
    <div id="pdfModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="relative bg-white rounded-xl shadow-2xl overflow-hidden w-[90%] max-w-6xl h-[90%] flex flex-col">

            <button onclick="closePdfModal()"
                class="absolute top-2 right-1 bg-red-600 hover:bg-red-700 text-white font-bold rounded-full px-4 py-1">
                X
            </button>

            <iframe id="pdfFrame"
                src=""
                class="w-full h-full rounded-lg"
                style="border:none;"></iframe>

        </div>
    </div>

    <script>
        function openPdfModal(url) {
            document.getElementById('pdfFrame').src = url + '?v=' + Date.now();
            document.getElementById('pdfModal').classList.remove('hidden');
        }

        function closePdfModal() {
            document.getElementById('pdfModal').classList.add('hidden');
            document.getElementById('pdfFrame').src = '';
        }
    </script>

</x-app-layout>
