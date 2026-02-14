<x-app-layout>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">📤 Export Dokumen</h1>
            <p class="text-white/90 text-sm">Export dokumen berdasarkan filter atau pilihan</p>
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
            $sortIcon = fn($col) => $currentSort === $col ? ($currentOrder === 'asc' ? '▲' : '▼') : '';
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

        <div class="bg-white p-5 rounded-xl shadow-md border">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
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
                            <option value="{{ $type->name }}" {{ request('kategori') == $type->name ? 'selected' : '' }}>
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
                            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                {{ $site->name }}
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

                <div class="md:col-span-12 mt-3">
                    <div class="bg-gray-50 border rounded-lg p-4">
                        <label class="text-sm font-semibold text-gray-700">Kolom yang diexport</label>
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

                <div class="md:col-span-12 mt-3">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <a href="{{ route('documents.export_csv', request()->query()) }}"
                            class="w-full flex items-center justify-center bg-white border border-[#0AA03A] text-[#0AA03A] px-5 py-3 rounded-lg shadow hover:bg-[#E8FCEB]">
                            Export CSV (Filter)
                        </a>
                        <a href="{{ route('documents.export_pdf', request()->query()) }}"
                            class="w-full flex items-center justify-center bg-white border border-[#0AA03A] text-[#0AA03A] px-5 py-3 rounded-lg shadow hover:bg-[#E8FCEB]">
                            Export PDF (Filter)
                        </a>
                        <a href="{{ route('documents.index') }}"
                            class="w-full flex items-center justify-center bg-[#0AA03A] text-white px-5 py-3 rounded-lg shadow hover:bg-[#087C2D]">
                            Kembali ke List
                        </a>
                    </div>
                </div>

                <div class="md:col-span-12 mt-3">
                    <div class="bg-white p-4 rounded-xl shadow-sm border">
                        <div class="text-sm font-semibold text-gray-700 mb-3">Export cepat</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            <select id="exportDeptCsv"
                                class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                                <option value="">Export CSV Departemen...</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <select id="exportDeptPdf"
                                class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                                <option value="">Export PDF Departemen...</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <select id="exportCatCsv"
                                class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                                <option value="">Export CSV Kategori...</option>
                                @foreach ($documentTypes as $type)
                                    <option value="{{ $type->name }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            <select id="exportCatPdf"
                                class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                                <option value="">Export PDF Kategori...</option>
                                @foreach ($documentTypes as $type)
                                    <option value="{{ $type->name }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
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
                    Site: {{ optional($sites->firstWhere('id', (int) request('site_id')))->name ?? request('site_id') }}
                </span>
            @endif
            @if (request('published_start') || request('published_end'))
                <span class="px-2 py-1 bg-gray-100 rounded">
                    Terbit: {{ request('published_start') ?? '-' }} s/d {{ request('published_end') ?? '-' }}
                </span>
            @endif
        </div>


        <div class="bg-white p-5 rounded-xl shadow-lg border overflow-x-auto">
            <form id="bulkExportForm" action="{{ route('documents.export_selected') }}" method="POST"
                class="mb-3 flex flex-col sm:flex-row sm:items-center gap-3">
                @csrf
                <button id="bulkExportBtn"
                    class="w-full sm:w-auto bg-white border border-[#0AA03A] text-[#0AA03A] px-4 py-2 rounded-lg shadow hover:bg-[#E8FCEB]"
                    disabled>
                    Export CSV Terpilih
                </button>
                <span class="text-xs text-gray-500">Pilih dokumen dari tabel di bawah.</span>
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
                                <td class="p-3">{{ $doc->title }}</td>
                                <td class="p-3 text-center">Rev. {{ $doc->revision_number ?? 0 }}</td>
                                <td class="p-3">{{ $doc->published_at?->format('Y-m-d') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-3 text-center text-gray-500">Tidak ada dokumen ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>

        <div class="mt-4">{{ $documents->links() }}</div>
    </div>

    <script>
        const baseQuery = @json(request()->query());
        function goExport(route, params) {
            const qs = new URLSearchParams(Object.assign({}, baseQuery, params));
            window.location.href = route + '?' + qs.toString();
        }

        const exportDeptCsv = document.getElementById('exportDeptCsv');
        const exportDeptPdf = document.getElementById('exportDeptPdf');
        const exportCatCsv = document.getElementById('exportCatCsv');
        const exportCatPdf = document.getElementById('exportCatPdf');
        if (exportDeptCsv) {
            exportDeptCsv.addEventListener('change', function() {
                if (this.value) goExport('{{ route('documents.export_csv') }}', {
                    department_id: this.value
                });
            });
        }
        if (exportDeptPdf) {
            exportDeptPdf.addEventListener('change', function() {
                if (this.value) goExport('{{ route('documents.export_pdf') }}', {
                    department_id: this.value
                });
            });
        }
        if (exportCatCsv) {
            exportCatCsv.addEventListener('change', function() {
                if (this.value) goExport('{{ route('documents.export_csv') }}', {
                    kategori: this.value
                });
            });
        }
        if (exportCatPdf) {
            exportCatPdf.addEventListener('change', function() {
                if (this.value) goExport('{{ route('documents.export_pdf') }}', {
                    kategori: this.value
                });
            });
        }

        const bulkBtn = document.getElementById('bulkExportBtn');
        const tableForm = document.getElementById('tableForm');
        const bulkForm = document.getElementById('bulkExportForm');
        const checkAll = document.getElementById('checkAll');
        const rowChecks = document.querySelectorAll('.row-check');

        function syncBulkButton() {
            const anyChecked = Array.from(rowChecks).some(c => c.checked);
            if (bulkBtn) bulkBtn.disabled = !anyChecked;
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
    </script>

</x-app-layout>
