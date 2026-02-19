<x-app-layout>
    <style>
        .template-form-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 16px;
            align-items: start;
        }

        .scope-box,
        .meta-box {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            background: #f9fafb;
        }

        .scope-scroll {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 10px;
            max-height: 320px;
            overflow: auto;
            background: #fff;
        }

        @media (max-width: 1024px) {
            .template-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">
        @php
            $requestState = request()->except([
                '_token',
                'project',
                'update_date',
                'header_doc_no',
                'header_effective_date',
                'header_revision',
                'department_ids',
                'site_ids',
                'site_department_map',
                'include_ho',
                'scope_payload',
                'metadata_payload',
            ]);
            $scopePayloadState = (string) request()->input('scope_payload', '');
            $metadataPayloadState = (string) request()->input('metadata_payload', '');
            $exportState = array_merge($requestState, [
                'scope_payload' => $scopePayloadState,
                'metadata_payload' => $metadataPayloadState,
            ]);
        @endphp
        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">Preview Export Template</h1>
            <p class="text-white/90 text-sm">Daftar Induk Dokumen - format perusahaan</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-md border">
            <form method="POST" action="{{ route('documents.export_template_preview') }}" class="space-y-4" id="templatePreviewForm">
                @csrf
                @foreach ($requestState as $qKey => $qValue)
                    @if (in_array($qKey, ['project', 'include_ho', 'department_ids', 'site_ids', 'site_department_map', 'update_date', 'header_doc_no', 'header_effective_date', 'header_revision', 'scope_payload', 'metadata_payload'], true))
                        @continue
                    @endif
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

                <div class="template-form-grid">
                    <div class="scope-box">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-semibold text-gray-800">Lingkup Dokumen</label>
                            <span class="text-xs text-gray-500">Checklist HO/Site, lalu atur departemen</span>
                        </div>

                        <input type="hidden" name="include_ho" id="includeHoHidden"
                            value="{{ ($meta['include_ho'] ?? true) ? '1' : '0' }}">
                        <input type="hidden" name="scope_payload" id="scopePayload"
                            value="{{ $scopePayloadState }}">
                        <input type="hidden" name="metadata_payload" id="metadataPayload"
                            value="{{ $metadataPayloadState }}">
                        <div class="scope-scroll space-y-2">
                            <div class="pb-2 border-b border-gray-100">
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-800">
                                    <input type="checkbox" value="1"
                                        {{ ($meta['include_ho'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300"
                                        id="includeHoCheck">
                                    <span>Head Office</span>
                                </label>
                                <details class="ml-6 mt-2 border border-gray-200 rounded px-2 py-1" id="hoDeptDetails">
                                    <summary class="text-xs cursor-pointer text-gray-700">Departemen Head Office</summary>
                                    <div class="mt-2 max-h-28 overflow-auto space-y-1">
                                        @foreach ($departments as $dept)
                                            <label class="flex items-center gap-2 text-xs">
                                                <input type="checkbox" value="{{ $dept->id }}"
                                                    {{ in_array($dept->id, $meta['department_ids'] ?? []) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 ho-dept-check" data-dept-id="{{ $dept->id }}">
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
                                        <input type="checkbox" value="{{ $site->id }}"
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
                                                <input type="checkbox" value="all"
                                                    {{ in_array('all', $siteDeptSelected) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 site-dept-check-all" data-site-id="{{ $site->id }}">
                                                <span>ALL Departemen</span>
                                            </label>
                                            @foreach ($departments as $dept)
                                                <label class="flex items-center gap-2 text-xs">
                                                    <input type="checkbox" value="{{ $dept->id }}"
                                                        {{ in_array((string) $dept->id, $siteDeptSelected) ? 'checked' : '' }}
                                                        class="rounded border-gray-300 site-dept-check-item" data-site-id="{{ $site->id }}" data-dept-id="{{ $dept->id }}">
                                                    <span>{{ $dept->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </details>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="meta-box">
                        <label class="text-sm font-semibold text-gray-800 block mb-3">Metadata Dokumen</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-gray-700">Project</label>
                                <input type="text" id="metaProject" value="{{ $meta['project'] }}"
                                    class="mt-1 border border-gray-300 rounded-lg px-3 py-2 w-full bg-white">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Tanggal Update</label>
                                <input type="date" id="metaUpdateDate" value="{{ $meta['update_date'] }}"
                                    class="mt-1 border border-gray-300 rounded-lg px-3 py-2 w-full bg-white">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">No. Dok</label>
                                <input type="text" id="metaHeaderDocNo" value="{{ $meta['header_doc_no'] }}"
                                    class="mt-1 border border-gray-300 rounded-lg px-3 py-2 w-full bg-white">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Tanggal Efektif</label>
                                <input type="text" id="metaHeaderEffectiveDate" value="{{ $meta['header_effective_date'] }}"
                                    class="mt-1 border border-gray-300 rounded-lg px-3 py-2 w-full bg-white">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Revisi</label>
                                <input type="text" id="metaHeaderRevision" value="{{ $meta['header_revision'] }}"
                                    class="mt-1 border border-gray-300 rounded-lg px-3 py-2 w-full bg-white">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 pt-1">
                    <button class="px-4 py-2 bg-[#0AA03A] text-white rounded-lg hover:bg-[#087C2D]">Update Preview</button>
                    <a href="{{ route('documents.export_template_pdf', $exportState) }}" class="px-4 py-2 bg-white border border-[#0AA03A] text-[#0AA03A] rounded-lg hover:bg-[#E8FCEB]">Export PDF Template</a>
                    <a href="{{ route('documents.export_template_xlsx', $exportState) }}" class="px-4 py-2 bg-white border border-[#0AA03A] text-[#0AA03A] rounded-lg hover:bg-[#E8FCEB]">Export XLSX Template</a>
                    <a href="{{ route('documents.export_page', $requestState) }}" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Kembali</a>
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
            const includeHoHidden = document.getElementById('includeHoHidden');
            const scopePayloadInput = document.getElementById('scopePayload');
            const metadataPayloadInput = document.getElementById('metadataPayload');
            const hoDeptChecks = Array.from(document.querySelectorAll('.ho-dept-check'));
            const hoDeptDetails = document.getElementById('hoDeptDetails');
            const templatePreviewForm = document.getElementById('templatePreviewForm');
            const metaProject = document.getElementById('metaProject');
            const metaUpdateDate = document.getElementById('metaUpdateDate');
            const metaHeaderDocNo = document.getElementById('metaHeaderDocNo');
            const metaHeaderEffectiveDate = document.getElementById('metaHeaderEffectiveDate');
            const metaHeaderRevision = document.getElementById('metaHeaderRevision');

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
                if (includeHoHidden) includeHoHidden.value = enabled ? '1' : '0';
                hoDeptChecks.forEach((cb) => {
                    cb.disabled = !enabled;
                });
                if (hoDeptDetails) hoDeptDetails.classList.toggle('opacity-50', !enabled);
            }

            function buildScopePayload() {
                const includeHo = includeHoCheck ? includeHoCheck.checked : true;
                const departmentIds = hoDeptChecks
                    .filter((cb) => cb.checked && !cb.disabled)
                    .map((cb) => Number(cb.dataset.deptId))
                    .filter((v) => Number.isInteger(v) && v > 0);

                const siteIds = siteChecks
                    .filter((cb) => cb.checked)
                    .map((cb) => Number(cb.dataset.siteId))
                    .filter((v) => Number.isInteger(v) && v > 0);

                const siteDepartmentMap = {};
                siteIds.forEach((siteId) => {
                    const allCheck = siteDeptAllChecks.find((c) => Number(c.dataset.siteId) === siteId);
                    const items = siteDeptItemChecks.filter((c) => Number(c.dataset.siteId) === siteId);
                    if (allCheck && allCheck.checked) {
                        siteDepartmentMap[String(siteId)] = ['all'];
                        return;
                    }
                    const selected = items
                        .filter((c) => c.checked)
                        .map((c) => String(c.dataset.deptId))
                        .filter((v) => v !== '');
                    siteDepartmentMap[String(siteId)] = selected.length ? selected : ['all'];
                });

                const payloadObj = {
                    include_ho: includeHo,
                    department_ids: departmentIds,
                    site_ids: siteIds,
                    site_department_map: siteDepartmentMap
                };

                return btoa(unescape(encodeURIComponent(JSON.stringify(payloadObj))))
                    .replace(/\+/g, '-')
                    .replace(/\//g, '_')
                    .replace(/=+$/g, '');
            }

            function buildMetadataPayload() {
                const payloadObj = {
                    project: metaProject ? metaProject.value : '',
                    update_date: metaUpdateDate ? metaUpdateDate.value : '',
                    header_doc_no: metaHeaderDocNo ? metaHeaderDocNo.value : '',
                    header_effective_date: metaHeaderEffectiveDate ? metaHeaderEffectiveDate.value : '',
                    header_revision: metaHeaderRevision ? metaHeaderRevision.value : '',
                };

                return btoa(unescape(encodeURIComponent(JSON.stringify(payloadObj))))
                    .replace(/\+/g, '-')
                    .replace(/\//g, '_')
                    .replace(/=+$/g, '');
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
            if (templatePreviewForm) {
                templatePreviewForm.addEventListener('submit', () => {
                    if (includeHoHidden && includeHoCheck) {
                        includeHoHidden.value = includeHoCheck.checked ? '1' : '0';
                    }
                    if (scopePayloadInput) {
                        scopePayloadInput.value = buildScopePayload();
                    }
                    if (metadataPayloadInput) {
                        metadataPayloadInput.value = buildMetadataPayload();
                    }
                });
            }

            syncSiteDeptVisibility();
            syncHoDeptVisibility();
            siteChecks.forEach((cb) => syncSiteDeptAllBehavior(cb.dataset.siteId));
        })();
    </script>
</x-app-layout>
