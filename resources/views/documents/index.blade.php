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

        .table-sticky thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #E8FCEB;
        }

        .data-table th,
        .data-table td {
            vertical-align: middle;
        }

        .action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            transition: background-color 0.15s ease;
        }

        .action-icon:hover {
            background: #eef2f7;
        }

        .title-cell {
            max-width: 360px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

    </style>
@endpush

<x-app-layout>
    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">📄 Daftar Dokumen</h1>
            <p class="text-white/90 text-sm">Semua dokumen perusahaan ditampilkan di sini</p>
        </div>

        {{-- SUCCESS --}}
        @if (session('success'))
            <div class="p-3 bg-green-200 text-green-900 rounded-lg shadow-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-3 bg-red-200 text-red-900 rounded-lg shadow-sm">{{ session('error') }}</div>
        @endif

        {{-- FILTER BAR --}}
        <div class="bg-white p-5 rounded-xl shadow-md border">
            <form method="GET" id="filterForm" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">

                {{-- SEARCH --}}
                <div class="md:col-span-3 relative">
                    <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                        placeholder="🔍 Cari dokumen..." autocomplete="off"
                        class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                    <div id="autocompleteBox"
                        class="hidden absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-lg">
                        <ul id="autocompleteList" class="divide-y"></ul>
                    </div>
                </div>

                {{-- KATEGORI --}}
                <div class="md:col-span-2">
                    <select name="kategori"
                        class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                        <option value="">Kategori</option>
                        @foreach ($documentTypes as $type)
                            <option value="{{ $type->name }}" {{ request('kategori') == $type->name ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-4 flex flex-col sm:flex-row sm:items-center gap-2">
                    <input type="date" name="published_start" value="{{ request('published_start') }}"
                        class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">

                    <span class="text-gray-500 font-semibold whitespace-nowrap text-center">
                        s/d
                    </span>

                    <input type="date" name="published_end" value="{{ request('published_end') }}"
                        class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                </div>


                {{-- DEPARTEMEN --}}
                @if (auth()->user()->isAdmin() || auth()->user()->isSuperUser())
                    <div class="md:col-span-2">
                        <select name="department_id"
                            class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                            <option value="">Departemen</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <select name="site_id"
                            class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                            <option value="">Site</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                    {{ $site->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- JUMLAH DATA PER HALAMAN --}}
                <div class="md:col-span-2">
                    <select name="per_page"
                        class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                        @foreach ([25, 50, 100, 200, 'all'] as $size)
                            <option value="{{ $size }}"
                                {{ (string) request('per_page', '50') === (string) $size ? 'selected' : '' }}>
                                {{ $size === 'all' ? 'ALL DOCUMENT' : $size . ' / halaman' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- BUTTON FILTER --}}
                <div class="md:col-span-1">
                    <button class="w-full bg-[#0AA03A] text-white px-4 py-2 rounded-lg shadow hover:bg-[#087C2D]">
                        Filter
                    </button>
                </div>

                {{-- BUTTON RESET --}}
                <div class="md:col-span-1">
                    <a href="{{ route('documents.index') }}"
                        class="block text-center w-full bg-gray-300 px-4 py-2 rounded-lg shadow hover:bg-gray-400">
                        Reset
                    </a>
                </div>

                {{-- TAMBAH DOKUMEN --}}
                @can('document.manage')
                    <div class="md:col-span-12 mt-3">
                        <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                            <a href="{{ route('documents.create') }}"
                                class="w-full sm:w-auto flex items-center justify-center bg-[#0AA03A] text-white px-5 py-3 rounded-lg shadow hover:bg-[#087C2D]">
                                Tambah Dokumen
                            </a>
                            <a href="{{ route('documents.export_page', request()->query()) }}"
                                class="w-full sm:w-auto flex items-center justify-center bg-white border border-[#0AA03A] text-[#0AA03A] px-5 py-3 rounded-lg shadow hover:bg-[#E8FCEB]">
                                Export
                            </a>
                            <a href="{{ route('admin.audits.index') }}"
                                class="w-full sm:w-auto flex items-center justify-center bg-white border border-[#0AA03A] text-[#0AA03A] px-5 py-3 rounded-lg shadow hover:bg-[#E8FCEB]">
                                Audit Log
                            </a>
                            <a href="{{ route('admin.departments.index') }}"
                                class="w-full sm:w-auto flex items-center justify-center bg-white border border-[#0AA03A] text-[#0AA03A] px-5 py-3 rounded-lg shadow hover:bg-[#E8FCEB]">
                                Kelola Departemen
                            </a>
                            <a href="{{ route('admin.document-types.index') }}"
                                class="w-full sm:w-auto flex items-center justify-center bg-white border border-[#0AA03A] text-[#0AA03A] px-5 py-3 rounded-lg shadow hover:bg-[#E8FCEB]">
                                Kelola Tipe Dokumen
                            </a>
                            <a href="{{ route('admin.sites.index') }}"
                                class="w-full sm:w-auto flex items-center justify-center bg-white border border-[#0AA03A] text-[#0AA03A] px-5 py-3 rounded-lg shadow hover:bg-[#E8FCEB]">
                                Kelola Site
                            </a>
                        </div>
                    </div>
                @endcan
            </form>
        </div>





        {{-- TOTAL --}}
        <p class="text-gray-700 text-sm ml-1">Menampilkan <b>{{ $totalResult }}</b> dokumen.</p>

        @php
            $categoryConfig = [
                'IK' => [
                    'color' => '#16A34A',
                    'text' => '#fff',
                    'tooltip' => 'Instruksi Kerja',
                    'icon' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10"/></svg>',
                ],
                'STD' => [
                    'color' => '#2563EB',
                    'text' => '#fff',
                    'tooltip' => 'Standar',
                    'icon' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-4 h-4"><rect x="4" y="4" width="16" height="16" rx="2"/></svg>',
                ],
                'FORM' => [
                    'color' => '#000',
                    'text' => '#fff',
                    'tooltip' => 'Formulir',
                    'icon' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-4 h-4"><rect x="6" y="4" width="12" height="16" rx="2"/><path d="M6 8h12"/></svg>',
                ],
                'SOP' => [
                    'color' => '#EA580C',
                    'text' => '#fff',
                    'tooltip' => 'Standard Operating Procedure',
                    'icon' =>
                        '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-4 h-4"><rect x="4" y="4" width="16" height="4" rx="1"/><rect x="4" y="12" width="16" height="8" rx="1"/></svg>',
                ],
            ];
            $departmentConfig = [
                'GENERAL' => ['color' => '#e5e7eb', 'text' => '#111827', 'icon' => '📌'],
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

        @php
            $currentSort = request('sort');
            $currentOrder = request('order', 'asc');
            $nextOrder = $currentOrder === 'asc' ? 'desc' : 'asc';
            $sortIcon = fn($col) => $currentSort === $col ? ($currentOrder === 'asc' ? '▲' : '▼') : '';
        @endphp

        {{-- TABLE --}}
        @php
            $emptyColspan = auth()->user()->can('document.manage') ? 9 : 8;
        @endphp
        <div class="bg-white p-5 rounded-xl shadow-lg border overflow-x-auto">
            <table class="w-full text-sm table-sticky data-table">
                <thead>
                    <tr class="bg-[#E8FCEB] text-[#0A7A2D] font-semibold text-left">
                        <th class="p-3">Departemen</th>
                        <th class="p-3">Site</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3">Nomor Dokumen</th>
                        <th class="p-3">
                            <a class="hover:underline"
                                href="{{ route('documents.index', array_merge(request()->query(), ['sort' => 'title', 'order' => $currentSort === 'title' ? $nextOrder : 'asc'])) }}">
                                Judul {{ $sortIcon('title') }}
                            </a>
                        </th>
                        <th class="p-3 text-center">
                            <a class="hover:underline"
                                href="{{ route('documents.index', array_merge(request()->query(), ['sort' => 'revision_number', 'order' => $currentSort === 'revision_number' ? $nextOrder : 'asc'])) }}">
                                Revisi {{ $sortIcon('revision_number') }}
                            </a>
                        </th>
                        <th class="p-3">
                            <a class="hover:underline"
                                href="{{ route('documents.index', array_merge(request()->query(), ['sort' => 'published_at', 'order' => $currentSort === 'published_at' ? $nextOrder : 'asc'])) }}">
                                Terbit {{ $sortIcon('published_at') }}
                            </a>
                        </th>
                        <th class="p-3 text-center">File</th>
                        @can('document.manage')
                            <th class="p-3 text-center">Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        @php
                            $deptName = $doc->department->name ?? '-';
                            $deptCfg = $departmentConfig[$deptName] ?? null;
                            $deptIcon = $doc->department->icon ?? ($deptCfg['icon'] ?? '🏢');
                            $catCfg = $categoryConfig[$doc->kategori] ?? null;
                        @endphp
                        <tr class="border-b hover:bg-[#F3FAF6]">
                            <td class="p-3">
                                @if ($deptCfg)
                                    <span
                                        class="tooltip inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold"
                                        style="background: {{ $deptCfg['color'] }}; color: {{ $deptCfg['text'] }}">
                                        {{ $deptIcon }} {{ $deptName }}
                                        <span class="tooltip-text">Departemen: {{ $deptName }}</span>
                                    </span>
                                @else
                                    <span class="tag bg-gray-300 text-gray-700">{{ $deptIcon }} {{ $deptName }}</span>
                                @endif
                            </td>
                            <td class="p-3 text-gray-700">
                                {{ $doc->site->name ?? '-' }}
                            </td>
                            <td class="p-3">
                                @if ($catCfg)
                                    <span
                                        class="tooltip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full shadow-sm text-xs font-semibold"
                                        style="background: {{ $catCfg['color'] }}; color: {{ $catCfg['text'] }}">
                                        {!! $catCfg['icon'] !!} {{ $doc->kategori }}
                                        <span class="tooltip-text">{{ $catCfg['tooltip'] }}</span>
                                    </span>
                                @else
                                    <span class="tag bg-gray-300 text-gray-700">{{ $doc->kategori }}</span>
                                @endif
                            </td>
                            <td class="p-3 font-semibold text-gray-800">
                                <a href="{{ route('documents.show', $doc->id) }}" class="text-black hover:underline">
                                    {{ $doc->document_number }}
                                </a>
                            </td>
                            <td class="p-3 font-medium text-gray-800 title-cell">
                                <a href="{{ route('documents.show', $doc->id) }}" class="text-black hover:underline block"
                                    title="{{ $doc->title }}">
                                    {{ $doc->title }}
                                </a>
                            </td>
                            <td class="p-3 text-center text-gray-700">
                                <div>Rev. {{ $doc->revision_number ?? 0 }}</div>
                            </td>
                            <td class="p-3 text-gray-600 whitespace-nowrap">{{ $doc->published_at?->format('Y-m-d') ?? '-' }}</td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                @if ($doc->kategori === 'FORM')
                                    <a href="{{ route('documents.download', $doc->id) }}"
                                        class="text-blue-600 action-icon">
                                        <span class="tooltip" aria-label="Unduh Formulir">
                                            <span>⬇️</span>
                                            <span class="tooltip-text">Unduh Formulir</span>
                                        </span>
                                    </a>
                                    @if ($doc->form_description_path)
                                        <button
                                            onclick="openPdfModal('{{ route('documents.preview_description', $doc->id) }}')"
                                            class="text-blue-600 action-icon">
                                            <span class="tooltip" aria-label="Lihat Penjelasan">
                                                <span>👁️</span>
                                                <span class="tooltip-text">Lihat Penjelasan</span>
                                            </span>
                                        </button>
                                    @endif
                                @else
                                    <button onclick="openPdfModal('{{ route('documents.preview', $doc->id) }}')"
                                        class="text-blue-600 action-icon">
                                        <span class="tooltip" aria-label="Lihat">
                                            <span>👁️</span>
                                            <span class="tooltip-text">Lihat</span>
                                        </span>
                                    </button>
                                    <a href="{{ route('documents.download', $doc->id) }}"
                                        class="text-blue-600 action-icon">
                                        <span class="tooltip" aria-label="Unduh">
                                            <span>⬇️</span>
                                            <span class="tooltip-text">Unduh</span>
                                        </span>
                                    </a>
                                @endif
                                </div>
                            </td>
                            @can('document.manage')
                                <td class="p-3 text-center">
                                    <div class="flex justify-center gap-3">
                                        <a href="{{ route('documents.edit', array_merge(['id' => $doc->id], request()->only(['search', 'kategori', 'published_start', 'published_end', 'department_id', 'site_id', 'sort', 'order', 'per_page', 'page']))) }}"
                                            class="text-yellow-600 hover:text-yellow-700 action-icon">
                                            <span class="tooltip" aria-label="Edit">
                                                <span>✏️</span>
                                                <span class="tooltip-text">Edit</span>
                                            </span>
                                        </a>
                                        <form action="{{ route('documents.destroy', $doc->id) }}" method="POST"
                                            onsubmit="return confirm('Hapus dokumen ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:text-red-700 action-icon">
                                                <span class="tooltip" aria-label="Hapus">
                                                    <span>🗑️</span>
                                                    <span class="tooltip-text">Hapus</span>
                                                </span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $emptyColspan }}" class="p-3 text-center text-gray-500">Tidak ada dokumen ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-gray-600">
                Halaman {{ $documents->currentPage() }} dari {{ $documents->lastPage() }}.
                Menampilkan {{ $documents->firstItem() ?? 0 }}-{{ $documents->lastItem() ?? 0 }}
                dari {{ $documents->total() }} dokumen.
            </div>
            <div>{{ $documents->onEachSide(1)->links('vendor.pagination.cpsd') }}</div>
            <form method="GET" class="flex items-center gap-2">
                @foreach (request()->except('page') as $qKey => $qValue)
                    <input type="hidden" name="{{ $qKey }}" value="{{ $qValue }}">
                @endforeach
                <label for="goto-page" class="text-sm text-gray-600">Ke halaman</label>
                <input id="goto-page" type="number" name="page" min="1" max="{{ $documents->lastPage() }}"
                    value="{{ $documents->currentPage() }}"
                    class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm">
                <button type="submit"
                    class="px-3 py-1.5 text-sm bg-gray-200 rounded-lg hover:bg-gray-300">Go</button>
            </form>
        </div>

    </div>

    {{-- PDF MODAL --}}
    <div id="pdfModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden w-[98%] max-w-[1700px] h-[96vh] flex flex-col">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="font-semibold text-gray-800">Preview Dokumen</h3>
                <button type="button" onclick="closePdfModal()"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold rounded-full px-3 py-1 shadow">X</button>
            </div>
            <iframe id="pdfFrame" class="w-full flex-1" style="border:none;" allow="fullscreen"
                loading="eager"></iframe>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const autocompleteBox = document.getElementById('autocompleteBox');
        const autocompleteList = document.getElementById('autocompleteList');
        let acTimer = null;

        function hideAutocomplete() {
            autocompleteBox.classList.add('hidden');
        }

        function showAutocomplete() {
            autocompleteBox.classList.remove('hidden');
        }

        function setSuggestions(items) {
            autocompleteList.innerHTML = '';
            if (!items.length) {
                hideAutocomplete();
                return;
            }
            items.forEach(item => {
                const li = document.createElement('li');
                li.className = 'p-3 hover:bg-gray-50 cursor-pointer';
                li.innerHTML = `
                    <div class="text-sm text-gray-600">${item.document_number}</div>
                    <div class="font-semibold text-gray-800">${item.title}</div>
                    <div class="text-xs text-gray-500">${item.department} | ${item.site}</div>
                `;
                li.addEventListener('click', () => {
                    searchInput.value = item.document_number;
                    hideAutocomplete();
                });
                autocompleteList.appendChild(li);
            });
            showAutocomplete();
        }

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const q = this.value.trim();
                if (acTimer) clearTimeout(acTimer);
                if (q.length < 2) {
                    hideAutocomplete();
                    return;
                }
                acTimer = setTimeout(async () => {
                    try {
                        const res = await fetch(
                            `{{ route('documents.autocomplete') }}?q=${encodeURIComponent(q)}`);
                        const data = await res.json();
                        setSuggestions(data);
                    } catch (e) {
                        hideAutocomplete();
                    }
                }, 250);
            });

            document.addEventListener('click', (e) => {
                if (!autocompleteBox.contains(e.target) && e.target !== searchInput) {
                    hideAutocomplete();
                }
            });
        }

        function openPdfModal(url) {
            document.getElementById("pdfFrame").src = url + "?v=" + Date.now();
            document.getElementById("pdfModal").classList.remove("hidden");
        }

        function closePdfModal() {
            document.getElementById("pdfModal").classList.add("hidden");
            document.getElementById("pdfFrame").src = "";
        }

        // Compact mode toggle (persist)
    </script>
</x-app-layout>
