<x-app-layout>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">Export Dokumen</h1>
            <p class="text-white/90 text-sm">Export Detail dan Dafar Induk Dokumen</p>
        </div>

        @if (session('success'))
            <div class="p-3 bg-green-200 text-green-900 rounded-lg shadow-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-3 bg-red-200 text-red-900 rounded-lg shadow-sm">{{ session('error') }}</div>
        @endif

        @php
            $currentSort = request('sort');
            $currentOrder = request('order', 'asc');
            $nextOrder = $currentOrder === 'asc' ? 'desc' : 'asc';
            $sortIcon = fn($col) => $currentSort === $col ? ($currentOrder === 'asc' ? '^' : 'v') : '';
            $exportColumns = [
                'department' => 'Departemen',
                'site' => 'Site',
                'kategori' => 'Kategori',
                'document_number' => 'Nomor Dokumen',
                'title' => 'Judul',
                'revision_number' => 'Revisi',
                'last_revision_at' => 'Tgl Revisi Terakhir',
                'published_at' => 'Tgl Terbit',
                'review_date' => 'Review Berikutnya',
                'file_path' => 'File',
            ];
            $selectedColumns = request('columns', array_keys($exportColumns));
        @endphp

        <div class="bg-white p-6 rounded-xl shadow-md border">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Export Detail List Dokumen</h2>
                <p class="text-sm text-gray-500">1) Filter dokumen, 2) Cek preview tabel, lalu export CSV/PDF.</p>
            </div>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <div class="md:col-span-12">
                    <h2 class="text-sm font-semibold text-gray-800">Filter Dokumen</h2>
                </div>
                <div class="md:col-span-3">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="🔍 Cari dokumen..."
                        class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                </div>

                <div class="md:col-span-2">
                    <select name="kategori"
                        class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                        <option value="">Kategori</option>
                        @foreach ($documentTypes as $type)
                            <option value="{{ $type->name }}"
                                {{ request('kategori') == $type->name ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-4 flex flex-col sm:flex-row sm:items-center gap-2">
                    <input type="date" name="published_start" value="{{ request('published_start') }}"
                        class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                    <span class="text-gray-500 font-semibold whitespace-nowrap text-center">s/d</span>
                    <input type="date" name="published_end" value="{{ request('published_end') }}"
                        class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                </div>

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
                            <option value="{{ $site->id }}"
                                {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                {{ $site->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

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

                <div class="md:col-span-1">
                    <button class="w-full bg-[#0AA03A] text-white px-4 py-2 rounded-lg shadow hover:bg-[#087C2D]">
                        Filter
                    </button>
                </div>
                <div class="md:col-span-1">
                    <a href="{{ route('documents.export_page') }}"
                        class="block text-center w-full bg-gray-300 px-4 py-2 rounded-lg shadow hover:bg-gray-400">
                        Reset
                    </a>
                </div>

                <div class="md:col-span-12 mt-4">
                    <div class="bg-gray-50 border rounded-lg p-4">
                        <div class="mb-3">
                            <p class="text-xs text-gray-500">Pilih kolom yang ingin ditampilkan di preview dan file
                                export.</p>
                        </div>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach ($exportColumns as $key => $label)
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="columns[]" value="{{ $key }}"
                                        {{ in_array($key, $selectedColumns) ? 'checked' : '' }}
                                        class="rounded border-gray-300">
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="md:col-span-12 mt-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <button type="button" id="openPreviewModalBtn"
                            class="w-full min-h-[46px] flex items-center justify-center bg-[#0AA03A] text-white px-5 py-3 rounded-lg shadow hover:bg-[#087C2D]">
                            Lihat Preview Export
                        </button>
                        <a href="{{ route('documents.export_csv', request()->query()) }}"
                            class="w-full min-h-[46px] flex items-center justify-center bg-white border border-[#0AA03A] text-[#0AA03A] px-5 py-3 rounded-lg shadow hover:bg-[#E8FCEB]">
                            Export CSV (Filter)
                        </a>
                        <a href="{{ route('documents.export_pdf', request()->query()) }}"
                            class="w-full min-h-[46px] flex items-center justify-center bg-white border border-[#0AA03A] text-[#0AA03A] px-5 py-3 rounded-lg shadow hover:bg-[#E8FCEB]">
                            Export PDF (Filter)
                        </a>
                        <a href="{{ route('documents.index') }}"
                            class="w-full min-h-[46px] flex items-center justify-center bg-gray-600 text-white px-5 py-3 rounded-lg shadow hover:bg-gray-700">
                            Kembali ke List
                        </a>
                    </div>
                </div>

                <div class="md:col-span-12 mt-4">
                    <div class="bg-white p-4 rounded-xl shadow-sm border">
                        <div class="text-sm font-semibold text-gray-700 mb-3">Daftar Induk Dokumen</div>
                        <p class="text-xs text-gray-500 mb-3">Pengaturan dokumen (project/scope/tanggal/header) dikelola
                            di halaman Preview Daftar Induk.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <a href="{{ route('documents.export_template_preview', request()->except(['project', 'update_date', 'header_doc_no', 'header_effective_date', 'header_revision', 'include_ho', 'department_ids', 'site_ids', 'site_department_map'])) }}"
                                class="w-full min-h-[44px] flex items-center justify-center bg-[#0AA03A] text-white px-4 py-2 rounded-lg shadow hover:bg-[#087C2D]">
                                Buka Preview & Pengaturan
                            </a>
                            <a href="{{ route('documents.index') }}"
                                class="w-full min-h-[44px] flex items-center justify-center bg-gray-100 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg shadow hover:bg-gray-200">
                                Kembali ke Daftar Dokumen
                            </a>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-12 mt-4">
                    <div class="bg-white p-4 rounded-xl shadow-sm border">
                        <div class="text-sm font-semibold text-gray-700 mb-3">Export Dashboard</div>
                        <p class="text-xs text-gray-500 mb-3">Export dashboard saja atau gabungan dashboard + daftar induk dokumen dalam 1 file PDF.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <a href="{{ route('dashboard.export_pdf') }}"
                                class="w-full min-h-[44px] inline-flex items-center justify-center bg-[#0AA03A] text-white px-4 py-2 rounded-lg shadow hover:bg-[#087C2D]">
                                Export Dashboard PDF
                            </a>
                            <a href="{{ route('documents.export_dashboard_template_pdf', request()->except(['columns'])) }}"
                                class="w-full min-h-[44px] inline-flex items-center justify-center bg-white border border-[#0AA03A] text-[#0AA03A] px-4 py-2 rounded-lg shadow hover:bg-[#E8FCEB]">
                                Export Dashboard + Daftar Induk
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="flex flex-wrap gap-2 text-xs">
            @if (request('search'))
                <span class="px-2 py-1 bg-gray-100 rounded">Search: {{ request('search') }}</span>
            @endif
            @if (request('kategori'))
                <span class="px-2 py-1 bg-gray-100 rounded">Kategori: {{ request('kategori') }}</span>
            @endif
            @if (request('department_id'))
                <span class="px-2 py-1 bg-gray-100 rounded">Departemen: {{ request('department_id') }}</span>
            @endif
            @if (request('site_id'))
                <span class="px-2 py-1 bg-gray-100 rounded">
                    Site:
                    {{ optional($sites->firstWhere('id', (int) request('site_id')))->name ?? request('site_id') }}
                </span>
            @endif
            @if (request('published_start') || request('published_end'))
                <span class="px-2 py-1 bg-gray-100 rounded">
                    Terbit: {{ request('published_start') ?? '-' }} s/d {{ request('published_end') ?? '-' }}
                </span>
            @endif
        </div>

        <p class="text-gray-700 text-sm ml-1">Menampilkan <b>{{ $documents->total() }}</b> dokumen.</p>


        <div class="bg-white p-5 rounded-xl shadow-lg border overflow-x-auto">
            <form id="bulkExportForm" action="{{ route('documents.export_selected') }}" method="POST"
                class="mb-3 flex flex-col sm:flex-row sm:items-center gap-3">
                @csrf
                <button id="bulkExportBtn"
                    class="w-full sm:w-auto bg-white border border-[#0AA03A] text-[#0AA03A] px-4 py-2 rounded-lg shadow hover:bg-[#E8FCEB]"
                    disabled>
                    Export CSV Terpilih
                </button>
                <span class="text-xs text-gray-500">Pilih dokumen dari tabel di bawah. Terpilih: <b
                        id="selectedCount">0</b></span>
            </form>

            <form id="tableForm" action="{{ route('documents.export_selected') }}" method="POST">
                @csrf
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#E8FCEB] text-black font-semibold text-left">
                            <th class="p-3 w-8">
                                <input type="checkbox" id="checkAll" class="rounded border-gray-300">
                            </th>
                            <th class="p-3">Departemen</th>
                            <th class="p-3">Site</th>
                            <th class="p-3">Kategori</th>
                            <th class="p-3">Nomor Dokumen</th>
                            <th class="p-3">
                                <a class="hover:underline"
                                    href="{{ route('documents.export_page', array_merge(request()->query(), ['sort' => 'title', 'order' => $currentSort === 'title' ? $nextOrder : 'asc'])) }}">
                                    Judul {{ $sortIcon('title') }}
                                </a>
                            </th>
                            <th class="p-3 text-center">
                                <a class="hover:underline"
                                    href="{{ route('documents.export_page', array_merge(request()->query(), ['sort' => 'revision_number', 'order' => $currentSort === 'revision_number' ? $nextOrder : 'asc'])) }}">
                                    Revisi {{ $sortIcon('revision_number') }}
                                </a>
                            </th>
                            <th class="p-3">
                                <a class="hover:underline"
                                    href="{{ route('documents.export_page', array_merge(request()->query(), ['sort' => 'published_at', 'order' => $currentSort === 'published_at' ? $nextOrder : 'asc'])) }}">
                                    Terbit {{ $sortIcon('published_at') }}
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $doc)
                            <tr class="border-b hover:bg-[#F3FAF6]">
                                <td class="p-3">
                                    <input type="checkbox" name="ids[]" value="{{ $doc->id }}"
                                        class="row-check rounded border-gray-300">
                                </td>
                                <td class="p-3">{{ $doc->department->name ?? '-' }}</td>
                                <td class="p-3">{{ $doc->site->name ?? '-' }}</td>
                                <td class="p-3">{{ $doc->kategori }}</td>
                                <td class="p-3">{{ $doc->document_number }}</td>
                                <td class="p-3" title="{{ $doc->title }}">{{ $doc->title }}</td>
                                <td class="p-3 text-center">Rev. {{ $doc->revision_number ?? 0 }}</td>
                                <td class="p-3">{{ $doc->published_at?->format('Y-m-d') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-3 text-center text-gray-500">Tidak ada dokumen ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>
        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-gray-600">
                Halaman {{ $documents->currentPage() }} dari {{ $documents->lastPage() }}.
                Menampilkan {{ $documents->firstItem() ?? 0 }}-{{ $documents->lastItem() ?? 0 }}
                dari {{ $documents->total() }} dokumen.
            </div>
            <div>{{ $documents->onEachSide(1)->links('vendor.pagination.cpsd') }}</div>
            <form method="GET" class="flex items-center gap-2">
                @foreach (request()->except('page') as $qKey => $qValue)
                    @if (is_array($qValue))
                        @foreach ($qValue as $k => $v)
                            @if (is_string($k))
                                <input type="hidden" name="{{ $qKey }}[{{ $k }}]" value="{{ $v }}">
                            @else
                                <input type="hidden" name="{{ $qKey }}[]" value="{{ $v }}">
                            @endif
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $qKey }}" value="{{ $qValue }}">
                    @endif
                @endforeach
                <label for="goto-page" class="text-sm text-gray-600">Ke halaman</label>
                <input id="goto-page" type="number" name="page" min="1" max="{{ $documents->lastPage() }}"
                    value="{{ $documents->currentPage() }}"
                    class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm">
                <button type="submit"
                    class="px-3 py-1.5 text-sm bg-gray-200 rounded-lg hover:bg-gray-300">Go</button>
            </form>
        </div>

        <div id="previewExportModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-7xl bg-white rounded-xl shadow-2xl border overflow-hidden">
                <div class="px-5 py-4 border-b flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Preview Export</h2>
                        <p class="text-xs text-gray-500">Preview menampilkan data halaman ini
                            ({{ $documents->count() }} baris).</p>
                    </div>
                    <button type="button" id="closePreviewModalBtn"
                        class="px-3 py-1 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">Tutup</button>
                </div>

                <div class="px-5 py-3 border-b">
                    <div class="flex flex-wrap gap-2 text-xs">
                        @foreach ($selectedColumns as $col)
                            <span class="px-2 py-1 bg-gray-100 rounded">{{ $exportColumns[$col] ?? $col }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="p-5 overflow-auto max-h-[70vh]">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#E8FCEB] text-black font-semibold text-left">
                                @foreach ($selectedColumns as $col)
                                    <th class="p-3">{{ $exportColumns[$col] ?? $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($documents as $doc)
                                <tr class="border-b hover:bg-[#F3FAF6]">
                                    @foreach ($selectedColumns as $col)
                                        <td class="p-3">
                                            @switch($col)
                                                @case('department')
                                                    {{ $doc->department->name ?? '-' }}
                                                @break

                                                @case('site')
                                                    {{ $doc->site->name ?? '-' }}
                                                @break

                                                @case('kategori')
                                                    {{ $doc->kategori }}
                                                @break

                                                @case('document_number')
                                                    {{ $doc->document_number }}
                                                @break

                                                @case('title')
                                                    <span title="{{ $doc->title }}">{{ $doc->title }}</span>
                                                @break

                                                @case('revision_number')
                                                    Rev. {{ $doc->revision_number ?? 0 }}
                                                @break

                                                @case('last_revision_at')
                                                    {{ $doc->last_revision_at?->format('Y-m-d') ?? '-' }}
                                                @break

                                                @case('published_at')
                                                    {{ $doc->published_at?->format('Y-m-d') ?? '-' }}
                                                @break

                                                @case('review_date')
                                                    {{ $doc->review_date?->format('Y-m-d') ?? '-' }}
                                                @break

                                                @case('file_path')
                                                    {{ $doc->file_path }}
                                                @break

                                                @default
                                                    -
                                            @endswitch
                                        </td>
                                    @endforeach
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ max(count($selectedColumns), 1) }}"
                                            class="p-3 text-center text-gray-500">Tidak ada data untuk preview.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script>
                const bulkBtn = document.getElementById('bulkExportBtn');
                const tableForm = document.getElementById('tableForm');
                const bulkForm = document.getElementById('bulkExportForm');
                const checkAll = document.getElementById('checkAll');
                const rowChecks = document.querySelectorAll('.row-check');
                const openPreviewModalBtn = document.getElementById('openPreviewModalBtn');
                const closePreviewModalBtn = document.getElementById('closePreviewModalBtn');
                const previewExportModal = document.getElementById('previewExportModal');

                function syncBulkButton() {
                    const anyChecked = Array.from(rowChecks).some(c => c.checked);
                    const selected = Array.from(rowChecks).filter(c => c.checked).length;
                    if (bulkBtn) bulkBtn.disabled = !anyChecked;
                    const selectedCount = document.getElementById('selectedCount');
                    if (selectedCount) selectedCount.textContent = selected;
                }

                if (checkAll) {
                    checkAll.addEventListener('change', function() {
                        rowChecks.forEach(c => (c.checked = this.checked));
                        syncBulkButton();
                    });
                }

                rowChecks.forEach(c => c.addEventListener('change', syncBulkButton));

                if (bulkForm && tableForm) {
                    bulkForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        syncColumnsToForms();
                        tableForm.submit();
                    });
                }

                function syncColumnsToForms() {
                    const cols = Array.from(document.querySelectorAll('input[name="columns[]"]:checked'))
                        .map(c => c.value);
                    const forms = [bulkForm, tableForm];
                    forms.forEach(f => {
                        if (!f) return;
                        f.querySelectorAll('input[name="columns[]"]').forEach(el => el.remove());
                        cols.forEach(c => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'columns[]';
                            input.value = c;
                            f.appendChild(input);
                        });
                    });
                }

                // ensure export links include current columns
                document.querySelectorAll('input[name="columns[]"]').forEach(c => {
                    c.addEventListener('change', function() {
                        const qs = new URLSearchParams(new FormData(this.closest('form')));
                        const csvLink = document.querySelector('a[href^="{{ route('documents.export_csv') }}"]');
                        const pdfLink = document.querySelector('a[href^="{{ route('documents.export_pdf') }}"]');
                        if (csvLink) csvLink.href = "{{ route('documents.export_csv') }}" + "?" + qs.toString();
                        if (pdfLink) pdfLink.href = "{{ route('documents.export_pdf') }}" + "?" + qs.toString();
                    });
                });

                function openPreviewModal() {
                    if (!previewExportModal) return;
                    previewExportModal.classList.remove('hidden');
                    previewExportModal.classList.add('flex');
                }

                function closePreviewModal() {
                    if (!previewExportModal) return;
                    previewExportModal.classList.add('hidden');
                    previewExportModal.classList.remove('flex');
                }

                if (openPreviewModalBtn) {
                    openPreviewModalBtn.addEventListener('click', openPreviewModal);
                }
                if (closePreviewModalBtn) {
                    closePreviewModalBtn.addEventListener('click', closePreviewModal);
                }
                if (previewExportModal) {
                    previewExportModal.addEventListener('click', function(e) {
                        if (e.target === previewExportModal) closePreviewModal();
                    });
                }
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') closePreviewModal();
                });
            </script>

    </x-app-layout>
