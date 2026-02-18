<x-app-layout>
    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">
        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">Preview Export Template</h1>
            <p class="text-white/90 text-sm">Daftar Induk Dokumen - format perusahaan</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-md border">
            <form method="GET" action="{{ route('documents.export_template_preview') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4" id="templatePreviewForm">
                @foreach (request()->except(['project', 'include_ho', 'department_ids', 'site_ids', 'site_department_map', 'update_date', 'header_doc_no', 'header_effective_date', 'header_revision']) as $qKey => $qValue)
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

                <div class="md:col-span-7">
                    <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-semibold text-gray-800">Lingkup Dokumen</label>
                            <span class="text-xs text-gray-500">Checklist HO/Site, lalu atur departemen</span>
                        </div>

                        <input type="hidden" name="include_ho" value="0">
                        <div class="border border-gray-300 rounded-lg px-3 py-2 max-h-72 overflow-auto space-y-2 bg-white">
                            <div class="pb-2 border-b border-gray-100">
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-800">
                                    <input type="checkbox" name="include_ho" value="1"
                                        {{ ($meta['include_ho'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300"
                                        id="includeHoCheck">
                                    <span>Head Office</span>
                                </label>
                                <details class="ml-6 mt-2 border border-gray-200 rounded px-2 py-1" id="hoDeptDetails">
                                    <summary class="text-xs cursor-pointer text-gray-700">Departemen Head Office</summary>
                                    <div class="mt-2 max-h-28 overflow-auto space-y-1">
                                        @foreach ($departments as $dept)
                                            <label class="flex items-center gap-2 text-xs">
                                                <input type="checkbox" name="department_ids[]" value="{{ $dept->id }}"
                                                    {{ in_array($dept->id, $meta['department_ids'] ?? []) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 ho-dept-check">
                                                <span>{{ $dept->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <p class="mt-1 text-[11px] text-gray-500">Kosong = ALL Departemen (HO).</p>
                                </details>
                            </div>

                            @foreach ($sites as $site)
                                <div class="space-y-1 pb-2 border-b border-gray-100 last:border-b-0" data-site-row="{{ $site->id }}">
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="site_ids[]" value="{{ $site->id }}"
                                            {{ in_array($site->id, $meta['site_ids'] ?? []) ? 'checked' : '' }}
                                            class="rounded border-gray-300 site-check"
                                            data-site-id="{{ $site->id }}">
                                        <span class="font-medium text-gray-800">Site - {{ $site->name }}</span>
                                    </label>
                                    @php
                                        $siteDeptSelected = (array) ($meta['site_department_map'][$site->id] ?? ['all']);
                                        $siteDeptSelected = array_map('strval', $siteDeptSelected);
                                    @endphp
                                    <details class="ml-6 border border-gray-200 rounded px-2 py-1 site-dept-details" data-site-id="{{ $site->id }}">
                                        <summary class="text-xs cursor-pointer text-gray-700">Departemen Site</summary>
                                        <div class="mt-2 max-h-24 overflow-auto space-y-1">
                                            <label class="flex items-center gap-2 text-xs">
                                                <input type="checkbox" name="site_department_map[{{ $site->id }}][]" value="all"
                                                    {{ in_array('all', $siteDeptSelected) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 site-dept-check-all" data-site-id="{{ $site->id }}">
                                                <span>ALL Departemen</span>
                                            </label>
                                            @foreach ($departments as $dept)
                                                <label class="flex items-center gap-2 text-xs">
                                                    <input type="checkbox" name="site_department_map[{{ $site->id }}][]" value="{{ $dept->id }}"
                                                        {{ in_array((string) $dept->id, $siteDeptSelected) ? 'checked' : '' }}
                                                        class="rounded border-gray-300 site-dept-check-item" data-site-id="{{ $site->id }}">
                                                    <span>{{ $dept->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </details>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="md:col-span-5">
                    <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                        <label class="text-sm font-semibold text-gray-800 block mb-3">Metadata Dokumen</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-gray-700">Project</label>
                                <input type="text" name="project" value="{{ $meta['project'] }}" class="mt-1 border border-gray-300 rounded-lg px-3 py-2 w-full bg-white">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Tanggal Update</label>
                                <input type="date" name="update_date" value="{{ $meta['update_date'] }}" class="mt-1 border border-gray-300 rounded-lg px-3 py-2 w-full bg-white">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">No. Dok</label>
                                <input type="text" name="header_doc_no" value="{{ $meta['header_doc_no'] }}" class="mt-1 border border-gray-300 rounded-lg px-3 py-2 w-full bg-white">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Tanggal Efektif</label>
                                <input type="text" name="header_effective_date" value="{{ $meta['header_effective_date'] }}" class="mt-1 border border-gray-300 rounded-lg px-3 py-2 w-full bg-white">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Revisi</label>
                                <input type="text" name="header_revision" value="{{ $meta['header_revision'] }}" class="mt-1 border border-gray-300 rounded-lg px-3 py-2 w-full bg-white">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-12 flex flex-wrap gap-2 pt-1">
                    <button class="px-4 py-2 bg-[#0AA03A] text-white rounded-lg hover:bg-[#087C2D]">Update Preview</button>
                    <a href="{{ route('documents.export_template_pdf', request()->query()) }}" class="px-4 py-2 bg-white border border-[#0AA03A] text-[#0AA03A] rounded-lg hover:bg-[#E8FCEB]">Export PDF Template</a>
                    <a href="{{ route('documents.export_template_xlsx', request()->query()) }}" class="px-4 py-2 bg-white border border-[#0AA03A] text-[#0AA03A] rounded-lg hover:bg-[#E8FCEB]">Export XLSX Template</a>
                    <a href="{{ route('documents.export_page', request()->except(['project', 'update_date', 'header_doc_no', 'header_effective_date', 'header_revision', 'include_ho', 'department_ids', 'site_ids', 'site_department_map'])) }}" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Kembali</a>
                </div>
            </form>
        </div>

        @include('documents.partials.export_template_table', ['rows' => $rows, 'meta' => $meta, 'totalRows' => $totalRows])
    </div>
    <script>
        (function() {
            const siteChecks = Array.from(document.querySelectorAll('.site-check'));
            const siteDeptDetails = Array.from(document.querySelectorAll('.site-dept-details'));
            const siteDeptAllChecks = Array.from(document.querySelectorAll('.site-dept-check-all'));
            const siteDeptItemChecks = Array.from(document.querySelectorAll('.site-dept-check-item'));
            const includeHoCheck = document.getElementById('includeHoCheck');
            const hoDeptChecks = Array.from(document.querySelectorAll('.ho-dept-check'));
            const hoDeptDetails = document.getElementById('hoDeptDetails');

            function syncSiteDeptVisibility() {
                siteChecks.forEach((cb) => {
                    const siteId = cb.dataset.siteId;
                    const details = siteDeptDetails.find((d) => d.dataset.siteId === siteId);
                    if (!details) return;
                    details.classList.toggle('opacity-50', !cb.checked);
                    details.querySelectorAll('input[type="checkbox"]').forEach((input) => {
                        input.disabled = !cb.checked;
                    });
                });
            }

            function syncSiteDeptAllBehavior(siteId) {
                const allCheck = siteDeptAllChecks.find((c) => c.dataset.siteId === siteId);
                const items = siteDeptItemChecks.filter((c) => c.dataset.siteId === siteId);
                if (!allCheck) return;
                if (allCheck.checked) items.forEach((i) => (i.checked = false));
            }

            function syncHoDeptVisibility() {
                const enabled = includeHoCheck ? includeHoCheck.checked : true;
                hoDeptChecks.forEach((cb) => {
                    cb.disabled = !enabled;
                });
                if (hoDeptDetails) hoDeptDetails.classList.toggle('opacity-50', !enabled);
            }

            siteChecks.forEach((cb) => cb.addEventListener('change', syncSiteDeptVisibility));
            siteDeptAllChecks.forEach((cb) => cb.addEventListener('change', () => syncSiteDeptAllBehavior(cb.dataset.siteId)));
            siteDeptItemChecks.forEach((cb) => {
                cb.addEventListener('change', () => {
                    const siteId = cb.dataset.siteId;
                    const allCheck = siteDeptAllChecks.find((c) => c.dataset.siteId === siteId);
                    if (cb.checked && allCheck) allCheck.checked = false;
                });
            });
            if (includeHoCheck) includeHoCheck.addEventListener('change', syncHoDeptVisibility);

            syncSiteDeptVisibility();
            syncHoDeptVisibility();
            siteChecks.forEach((cb) => syncSiteDeptAllBehavior(cb.dataset.siteId));
        })();
    </script>
</x-app-layout>
