<x-app-layout>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">🧾 Audit Log</h1>
            <p class="text-white/90 text-sm">Riwayat aktivitas dokumen</p>
        </div>

        @if (session('success'))
            <div class="p-3 bg-green-200 text-green-900 rounded-lg shadow-sm">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="p-3 bg-red-200 text-red-900 rounded-lg shadow-sm">{{ $errors->first() }}</div>
        @endif

        <div class="bg-white p-5 rounded-xl shadow-md border">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
                <div class="md:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="🔍 Cari nomor/judul..."
                        class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                </div>
                <div>
                    <select name="action"
                        class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                        <option value="">Semua Aksi</option>
                        @foreach (['create', 'update', 'revision', 'delete'] as $act)
                            <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>
                                {{ strtoupper($act) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                </div>
                <div>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                </div>
                <div>
                    <select name="per_page"
                        class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                        @foreach (['20', '50', '100', '200', 'all'] as $size)
                            <option value="{{ $size }}" {{ (string) request('per_page', '20') === $size ? 'selected' : '' }}>
                                {{ $size === 'all' ? 'ALL LOG' : $size . ' / halaman' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 md:col-span-6">
                    <button class="w-full sm:w-auto bg-[#0AA03A] text-white px-5 py-2 rounded-lg shadow hover:bg-[#087C2D]">
                        Filter
                    </button>
                    <a href="{{ route('admin.audits.index') }}"
                        class="w-full sm:w-auto bg-gray-300 px-5 py-2 rounded-lg shadow hover:bg-gray-400 text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-lg border overflow-x-auto">
            <div class="mb-3">
                <button type="button" id="toggleCrudMode"
                    class="px-3 py-2 text-sm bg-[#0AA03A] text-white rounded-lg hover:bg-[#087C2D]">
                    Kelola Audit
                </button>
            </div>

            @if (app()->environment('local'))
                <div id="auditCrudToolbar" class="mb-3 flex items-center gap-2 hidden">
                    <button type="button" id="btnToggleAll"
                        class="px-3 py-2 text-sm bg-gray-200 rounded-lg hover:bg-gray-300">Centang Semua</button>
                    <form id="bulkDeleteForm" method="POST" action="{{ route('admin.audits.destroy_selected') }}"
                        onsubmit="return confirm('Hapus semua audit log yang dicentang?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-3 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus Terpilih</button>
                    </form>
                </div>
            @endif
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#E8FCEB] text-black font-semibold text-left">
                        @if (app()->environment('local'))
                            <th class="p-3 text-center w-12 audit-crud hidden">#</th>
                        @endif
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Aksi</th>
                        <th class="p-3">Dokumen</th>
                        <th class="p-3">Oleh</th>
                        <th class="p-3">Info</th>
                        <th class="p-3 text-center audit-crud hidden">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($audits as $audit)
                        <tr class="border-b hover:bg-[#F3FAF6]">
                            @if (app()->environment('local'))
                                <td class="p-3 text-center audit-crud hidden">
                                    <input type="checkbox" class="audit-check" value="{{ $audit->id }}">
                                </td>
                            @endif
                            <td class="p-3">{{ $audit->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="p-3">{{ strtoupper($audit->action) }}</td>
                            <td class="p-3">
                                @if ($audit->document)
                                    <a href="{{ route('documents.show', $audit->document_id) }}"
                                        class="text-blue-600 hover:underline">
                                        {{ $audit->document->document_number }}
                                    </a>
                                    <div class="text-xs text-gray-500">{{ $audit->document->title }}</div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-3">{{ $audit->user->name ?? '-' }}</td>
                            <td class="p-3">
                                @if (!empty($audit->meta))
                                    <div>{{ $audit->meta['revision_note'] ?? $audit->meta['title'] ?? '-' }}</div>
                                    <details class="mt-1">
                                        <summary class="text-xs text-blue-600 cursor-pointer">Lihat full log</summary>
                                        <pre class="mt-1 p-2 text-xs bg-gray-100 rounded overflow-x-auto">{{ json_encode($audit->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-3 text-center audit-crud hidden">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ route('admin.audits.edit', $audit->id) }}"
                                        class="text-blue-600 hover:underline">Edit</a>
                                    @if (app()->environment('local'))
                                        <form method="POST" action="{{ route('admin.audits.destroy', $audit->id) }}"
                                            onsubmit="return confirm('Hapus audit log ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:text-red-700">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">Belum ada audit log.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-gray-600">
                    Halaman {{ $audits->currentPage() }} dari {{ $audits->lastPage() }}.
                    Menampilkan {{ $audits->firstItem() ?? 0 }}-{{ $audits->lastItem() ?? 0 }}
                    dari {{ $audits->total() }} log audit.
                </div>
                <div>{{ $audits->onEachSide(1)->links('vendor.pagination.cpsd') }}</div>
                <form method="GET" class="flex items-center gap-2">
                    @foreach (request()->except('page') as $qKey => $qValue)
                        <input type="hidden" name="{{ $qKey }}" value="{{ $qValue }}">
                    @endforeach
                    <label for="goto-page-audit" class="text-sm text-gray-600">Ke halaman</label>
                    <input id="goto-page-audit" type="number" name="page" min="1" max="{{ $audits->lastPage() }}"
                        value="{{ $audits->currentPage() }}"
                        class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm">
                    <button type="submit" class="px-3 py-1.5 text-sm bg-gray-200 rounded-lg hover:bg-gray-300">Go</button>
                </form>
            </div>
        </div>

    </div>

    @if (app()->environment('local'))
        <script>
            const crudStorageKey = 'auditCrudOpen';
            const toggleCrudMode = document.getElementById('toggleCrudMode');
            const auditCrudToolbar = document.getElementById('auditCrudToolbar');
            const btnToggleAll = document.getElementById('btnToggleAll');
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');
            const crudCells = Array.from(document.querySelectorAll('.audit-crud'));
            let crudOpen = localStorage.getItem(crudStorageKey) === '1';

            function applyCrudMode() {
                crudCells.forEach(el => el.classList.toggle('hidden', !crudOpen));
                if (auditCrudToolbar) {
                    auditCrudToolbar.classList.toggle('hidden', !crudOpen);
                }
                if (toggleCrudMode) {
                    toggleCrudMode.textContent = crudOpen ? 'Tutup Kelola Audit' : 'Kelola Audit';
                }
                localStorage.setItem(crudStorageKey, crudOpen ? '1' : '0');
            }

            applyCrudMode();

            if (toggleCrudMode) {
                toggleCrudMode.addEventListener('click', function() {
                    crudOpen = !crudOpen;
                    applyCrudMode();
                });
            }

            if (btnToggleAll) {
                btnToggleAll.addEventListener('click', function() {
                    const checks = Array.from(document.querySelectorAll('.audit-check'));
                    const allChecked = checks.length > 0 && checks.every(c => c.checked);
                    checks.forEach(c => c.checked = !allChecked);
                    btnToggleAll.textContent = allChecked ? 'Centang Semua' : 'Batal Centang';
                });
            }

            if (bulkDeleteForm) {
                bulkDeleteForm.addEventListener('submit', function(e) {
                    const selected = Array.from(document.querySelectorAll('.audit-check:checked')).map(c => c.value);
                    bulkDeleteForm.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());

                    if (selected.length === 0) {
                        e.preventDefault();
                        alert('Pilih minimal 1 audit log.');
                        return;
                    }

                    selected.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        bulkDeleteForm.appendChild(input);
                    });
                });
            }
        </script>
    @else
        <script>
            const crudStorageKey = 'auditCrudOpen';
            const toggleCrudMode = document.getElementById('toggleCrudMode');
            const crudCells = Array.from(document.querySelectorAll('.audit-crud'));
            let crudOpen = localStorage.getItem(crudStorageKey) === '1';

            function applyCrudMode() {
                crudCells.forEach(el => el.classList.toggle('hidden', !crudOpen));
                if (toggleCrudMode) {
                    toggleCrudMode.textContent = crudOpen ? 'Tutup Kelola Audit' : 'Kelola Audit';
                }
                localStorage.setItem(crudStorageKey, crudOpen ? '1' : '0');
            }

            applyCrudMode();

            if (toggleCrudMode) {
                toggleCrudMode.addEventListener('click', function() {
                    crudOpen = !crudOpen;
                    applyCrudMode();
                });
            }
        </script>
    @endif

</x-app-layout>
