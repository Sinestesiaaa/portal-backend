<x-app-layout>
    <style>
        .mini-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            line-height: 1;
            vertical-align: middle;
        }

        .tooltip {
            position: relative;
            display: inline-flex;
        }

        .tooltip .tooltip-text {
            position: absolute;
            bottom: calc(100% + 6px);
            left: 50%;
            transform: translateX(-50%);
            background: #111827;
            color: #fff;
            font-size: 12px;
            line-height: 1;
            white-space: nowrap;
            padding: 6px 8px;
            border-radius: 6px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.15s ease;
            z-index: 60;
            pointer-events: none;
        }

        .tooltip:hover .tooltip-text {
            opacity: 1;
            visibility: visible;
        }
    </style>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

        {{-- HEADER --}}
        <div class="p-6 rounded-xl bg-gradient-to-r from-[#0AA03A] to-[#16A34A] shadow-lg text-white">
            <h1 class="text-2xl font-bold">📄 Detail Dokumen</h1>
            <p class="text-white/90 text-sm">Informasi lengkap dan dokumen terkait</p>
        </div>

        @if (session('success'))
            <div class="p-3 bg-green-200 text-green-900 rounded-lg shadow-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- LEFT: DETAIL --}}
            <div class="bg-white p-6 rounded-xl shadow-md border space-y-5">

                <div>
                    <label class="font-semibold text-gray-700">Nomor Dokumen</label>
                    <div class="mt-1 w-full border rounded-lg px-4 py-2 bg-gray-50">
                        {{ $document->document_number }}
                    </div>
                </div>

                <div>
                    <label class="font-semibold text-gray-700">Judul Dokumen</label>
                    <div class="mt-1 w-full border rounded-lg px-4 py-2 bg-gray-50">
                        {{ $document->title }}
                    </div>
                </div>

                <div>
                    <label class="font-semibold text-gray-700">Kategori</label>
                    <div class="mt-1 w-full border rounded-lg px-4 py-2 bg-gray-50">
                        {{ $document->kategori }}
                    </div>
                </div>

                <div>
                    <label class="font-semibold text-gray-700">Departemen</label>
                    <div class="mt-1 w-full border rounded-lg px-4 py-2 bg-gray-50">
                        {{ $document->department->name ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="font-semibold text-gray-700">Site</label>
                    <div class="mt-1 w-full border rounded-lg px-4 py-2 bg-gray-50">
                        {{ $document->site->name ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="font-semibold text-gray-700">Tanggal Terbit</label>
                    <div class="mt-1 w-full border rounded-lg px-4 py-2 bg-gray-50">
                        {{ $document->published_at?->format('Y-m-d') ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="font-semibold text-gray-700">Review Berikutnya</label>
                    <div class="mt-1 w-full border rounded-lg px-4 py-2 bg-gray-50">
                        {{ $document->review_date?->format('Y-m-d') ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="font-semibold text-gray-700">Revisi</label>
                    <div class="mt-1 w-full border rounded-lg px-4 py-2 bg-gray-50">
                        Rev. {{ $document->revision_number ?? 0 }}
                    </div>
                </div>

                <div>
                    <label class="font-semibold text-gray-700">Tanggal Revisi Terakhir</label>
                    <div class="mt-1 w-full border rounded-lg px-4 py-2 bg-gray-50">
                        {{ $document->last_revision_at?->format('Y-m-d') ?? '-' }}
                    </div>
                </div>

                @if ($document->revision_note)
                    <div>
                        <label class="font-semibold text-gray-700">Catatan Revisi</label>
                        <div class="mt-1 w-full border rounded-lg px-4 py-2 bg-gray-50">
                            {{ $document->revision_note }}
                        </div>
                    </div>
                @endif

                @if ($audits->count())
                    @php
                        $last = $audits->first();
                    @endphp
                    <div class="text-xs text-gray-500">
                        Update terakhir: {{ $last->user->name ?? '-' }} - {{ $last->created_at?->format('Y-m-d H:i') ?? '-' }}
                    </div>
                @endif

                <div class="pt-2 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('documents.index') }}"
                        class="w-full sm:w-auto px-5 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 text-center">Kembali</a>
                    @can('document.manage')
                        <a href="{{ route('documents.edit', $document->id) }}"
                            class="w-full sm:w-auto px-5 py-2 bg-[#0AA03A] text-white rounded-lg shadow hover:bg-[#087C2D] text-center">
                            Edit
                        </a>
                    @endcan
                </div>
            </div>

            {{-- RIGHT: PREVIEW --}}
            <div class="bg-white p-6 rounded-xl shadow-md border space-y-4">
                <label class="font-semibold text-gray-700">Preview Dokumen</label>

                <div class="border rounded-xl overflow-hidden shadow-sm">
                    @if ($document->kategori === 'FORM')
                        @if ($document->form_description_path)
                            <iframe src="{{ route('documents.preview_description', $document->id) }}"
                                class="w-full" style="height: 500px; border: none;"></iframe>
                        @else
                            <div class="p-4 text-sm text-gray-500">Penjelasan form belum ada.</div>
                        @endif
                    @else
                        <iframe src="{{ route('documents.preview', $document->id) }}" class="w-full"
                            style="height: 500px; border: none;"></iframe>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if ($document->kategori !== 'FORM' || $document->form_description_path)
                        <button type="button"
                            onclick="openPdfModal('{{ $document->kategori === 'FORM' ? route('documents.preview_description', $document->id) : route('documents.preview', $document->id) }}')"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 hover:bg-gray-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 3c4.418 0 7.418 3.503 8.58 5.285a1.25 1.25 0 0 1 0 1.43C17.418 11.497 14.418 15 10 15s-7.418-3.503-8.58-5.285a1.25 1.25 0 0 1 0-1.43C2.582 6.503 5.582 3 10 3Zm0 2.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Z"/>
                            </svg>
                            <span>Lihat Dokumen</span>
                        </button>
                    @endif
                    <a href="{{ route('documents.download', $document->id) }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 hover:bg-gray-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 2a1 1 0 0 1 1 1v7.586l2.293-2.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L9 10.586V3a1 1 0 0 1 1-1Z"/>
                            <path d="M3 14a1 1 0 0 1 1 1v1h12v-1a1 1 0 1 1 2 0v2a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1Z"/>
                        </svg>
                        <span>Unduh File</span>
                    </a>
                </div>

            </div>
        </div>

        {{-- RELATED DOCUMENTS --}}
        <div class="bg-white p-6 rounded-xl shadow-md border space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Dokumen Terkait</h2>
            </div>

            @if ($document->relatedDocuments->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#E8FCEB] text-[#0A7A2D] font-semibold text-left">
                                <th class="p-3">Kategori</th>
                                <th class="p-3">Nomor Dokumen</th>
                                <th class="p-3">Judul</th>
                                <th class="p-3">Departemen</th>
                                <th class="p-3 text-center">File</th>
                                @can('document.manage')
                                    <th class="p-3 text-center">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($document->relatedDocuments as $rel)
                                <tr class="border-b hover:bg-[#F3FAF6]">
                                    <td class="p-3">{{ $rel->kategori }}</td>
                                    <td class="p-3 font-semibold text-gray-800">
                                        <a href="{{ route('documents.show', $rel->id) }}"
                                            class="text-gray-900 hover:underline">
                                            {{ $rel->document_number }}
                                        </a>
                                    </td>
                                    <td class="p-3 font-medium text-gray-800">
                                        <a href="{{ route('documents.show', $rel->id) }}"
                                            class="text-gray-900 hover:underline"
                                            title="{{ $rel->title }}">
                                            {{ $rel->title }}
                                        </a>
                                    </td>
                                    <td class="p-3">{{ $rel->department->name ?? '-' }}</td>
                                    <td class="p-3 text-center">
                                        @if ($rel->kategori === 'FORM')
                                            <a href="{{ route('documents.download', $rel->id) }}"
                                                class="tooltip mini-icon"
                                                aria-label="Unduh Formulir">
                                                <span>⬇️</span>
                                                <span class="tooltip-text">Unduh Formulir</span>
                                            </a>
                                            @if ($rel->form_description_path)
                                                <button type="button"
                                                    onclick="openPdfModal('{{ route('documents.preview_description', $rel->id) }}')"
                                                    class="tooltip mini-icon ml-2"
                                                    aria-label="Lihat Penjelasan">
                                                    <span>👁️</span>
                                                    <span class="tooltip-text">Lihat Penjelasan</span>
                                                </button>
                                            @endif
                                        @else
                                            <button type="button"
                                                onclick="openPdfModal('{{ route('documents.preview', $rel->id) }}')"
                                                class="tooltip mini-icon"
                                                aria-label="Lihat Dokumen">
                                                <span>👁️</span>
                                                <span class="tooltip-text">Lihat Dokumen</span>
                                            </button>
                                            <a href="{{ route('documents.download', $rel->id) }}"
                                                class="tooltip mini-icon ml-2"
                                                aria-label="Unduh Dokumen">
                                                <span>⬇️</span>
                                                <span class="tooltip-text">Unduh Dokumen</span>
                                            </a>
                                        @endif
                                    </td>
                                    @can('document.manage')
                                        <td class="p-3 text-center">
                                            <a href="{{ route('documents.edit', $rel->id) }}"
                                                class="tooltip mini-icon mr-2"
                                                aria-label="Edit Dokumen">
                                                <span>✏️</span>
                                                <span class="tooltip-text">Edit Dokumen</span>
                                            </a>
                                            <form
                                                action="{{ route('documents.related.delete', [$document->id, $rel->id]) }}"
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Hapus relasi dokumen ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="tooltip mini-icon text-red-600"
                                                    aria-label="Hapus Relasi">
                                                    <span>🗑️</span>
                                                    <span class="tooltip-text">Hapus Relasi</span>
                                                </button>
                                            </form>
                                        </td>
                                    @endcan
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500">Belum ada dokumen terkait.</p>
            @endif

            @can('document.manage')
                <div class="pt-4 border-t">
                    <button type="button" onclick="openRelatedModal()"
                        class="bg-[#0AA03A] text-white px-4 py-2 rounded-lg shadow hover:bg-[#087C2D]">
                        Tambah Dokumen Terkait
                    </button>
                </div>
            @endcan
        </div>

    </div>

    <div class="max-w-7xl mx-auto px-6 pb-10 space-y-6">
        {{-- REVISION HISTORY --}}
        <div class="bg-white p-6 rounded-xl shadow-md border space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Riwayat Revisi</h2>

        @if ($revisions->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#E8FCEB] text-black font-semibold text-left">
                            <th class="p-3">Revisi</th>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Catatan</th>
                            <th class="p-3">Oleh</th>
                            @can('document.manage')
                                <th class="p-3 text-center">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($revisions as $rev)
                            <tr class="border-b hover:bg-[#F3FAF6]">
                                <td class="p-3">Rev. {{ $rev->revision_number }}</td>
                                <td class="p-3">{{ $rev->revised_at?->format('Y-m-d') ?? '-' }}</td>
                                <td class="p-3">{{ $rev->revision_note }}</td>
                                <td class="p-3">{{ $rev->revisedBy->name ?? '-' }}</td>
                                @can('document.manage')
                                    <td class="p-3 text-center">
                                        <div class="flex items-center justify-center gap-3">
                                            <a href="{{ route('documents.revisions.edit', [$document->id, $rev->id]) }}"
                                                class="text-blue-600 hover:underline">Edit</a>
                                            @if (app()->environment('local'))
                                                <form method="POST"
                                                    action="{{ route('documents.revisions.delete', [$document->id, $rev->id]) }}"
                                                    onsubmit="return confirm('Hapus riwayat revisi ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="text-red-600 hover:text-red-700">Hapus</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $revisions->links() }}
            </div>
        @else
            <p class="text-sm text-gray-500">Belum ada riwayat revisi.</p>
        @endif
        </div>

        {{-- AUDIT LOG --}}
        <div class="bg-white p-6 rounded-xl shadow-md border space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Audit Log</h2>

        @if ($audits->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#E8FCEB] text-black font-semibold text-left">
                            <th class="p-3">Waktu</th>
                            <th class="p-3">Aksi</th>
                            <th class="p-3">Oleh</th>
                            <th class="p-3">Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($audits as $audit)
                            <tr class="border-b hover:bg-[#F3FAF6]">
                                <td class="p-3">{{ $audit->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td class="p-3">{{ strtoupper($audit->action) }}</td>
                                <td class="p-3">{{ $audit->user->name ?? '-' }}</td>
                                <td class="p-3">
                                    @if (!empty($audit->meta))
                                        {{ $audit->meta['revision_note'] ?? $audit->meta['title'] ?? '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500">Belum ada audit log.</p>
        @endif
        </div>
    </div>


    {{-- PDF MODAL PREVIEW --}}
    <div id="pdfModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl w-[98%] max-w-[1700px] h-[96vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="font-semibold text-gray-800">Preview Dokumen</h3>
                <button type="button" onclick="closePdfModal()"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold rounded-full px-3 py-1 shadow">X</button>
            </div>
            <iframe id="pdfFrame" src="" class="w-full flex-1" style="border: none;"></iframe>
        </div>
    </div>

    <script>
        function openPdfModal(url) {
            const modal = document.getElementById('pdfModal');
            const frame = document.getElementById('pdfFrame');
            if (!modal || !frame) return;
            frame.src = url;
            modal.classList.remove('hidden');
        }

        function closePdfModal() {
            const modal = document.getElementById('pdfModal');
            const frame = document.getElementById('pdfFrame');
            if (!modal || !frame) return;
            frame.src = '';
            modal.classList.add('hidden');
        }
    </script>

    @can('document.manage')
        {{-- RELATED MODAL --}}
        <div id="relatedModal"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl w-[92%] max-w-3xl max-h-[90%] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="font-semibold text-gray-800">Tambah Dokumen Terkait</h3>
                    <button onclick="closeRelatedModal()"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold rounded-full px-3 py-1 shadow">X</button>
                </div>

                <form action="{{ route('documents.related.update', $document->id) }}" method="POST"
                    class="p-4 flex flex-col gap-4">
                    @csrf

                    <div>
                        <label class="font-semibold text-gray-700">Cari Dokumen</label>
                        <input type="text" id="relatedSearch"
                            class="mt-2 w-full border rounded-lg px-3 py-2"
                            placeholder="Ketik nomor dokumen atau judul...">
                    </div>

                    <div class="border rounded-lg max-h-[50vh] overflow-y-auto">
                        <ul id="relatedList" class="divide-y">
                            @foreach ($candidates as $cand)
                                @php
                                    $text = $cand->document_number . ' — ' . $cand->title;
                                @endphp
                                <li class="p-3 flex items-start gap-3"
                                    data-text="{{ strtolower($text) }}">
                                    <input type="checkbox" name="related_ids[]" value="{{ $cand->id }}"
                                        class="mt-1"
                                        {{ in_array($cand->id, $relatedIds) ? 'checked' : '' }}>
                                    <div>
                                        <div class="text-sm text-gray-600">{{ $cand->document_number }}</div>
                                        <div class="font-semibold text-gray-800">{{ $cand->title }}</div>
                                        <div class="text-xs text-gray-500">{{ $cand->department->name ?? '-' }}</div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeRelatedModal()"
                            class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 bg-[#0AA03A] text-white rounded-lg shadow hover:bg-[#087C2D]">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openRelatedModal() {
                document.getElementById('relatedModal').classList.remove('hidden');
                document.getElementById('relatedSearch').focus();
            }

            function closeRelatedModal() {
                document.getElementById('relatedModal').classList.add('hidden');
            }

            const relatedSearch = document.getElementById('relatedSearch');
            const relatedList = document.getElementById('relatedList');

            if (relatedSearch) {
                relatedSearch.addEventListener('input', function() {
                    const q = this.value.toLowerCase().trim();
                    relatedList.querySelectorAll('li').forEach(li => {
                        const text = li.getAttribute('data-text') || '';
                        li.style.display = text.includes(q) ? '' : 'none';
                    });
                });
            }
        </script>
    @endcan

</x-app-layout>
