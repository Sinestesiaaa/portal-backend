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

        .table-sticky thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #E8FCEB;
        }
    </style>
@endpush

<x-app-layout>
    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="page-title">📌 Dokumen Perlu Review</h1>
            <p class="page-subtitle">Daftar dokumen overdue dan dokumen dengan jadwal review H-30 hari</p>
        </div>

        @if (session('success'))
            <div class="p-3 bg-green-200 text-green-900 rounded-lg shadow-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-3 bg-red-200 text-red-900 rounded-lg shadow-sm">{{ session('error') }}</div>
        @endif

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
                    <input type="date" name="review_start" value="{{ request('review_start') }}"
                        class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                    <span class="text-gray-500 font-semibold whitespace-nowrap text-center">s/d</span>
                    <input type="date" name="review_end" value="{{ request('review_end') }}"
                        class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                </div>

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

                <div class="md:col-span-1">
                    <button class="w-full bg-[#0AA03A] text-white px-4 py-2 rounded-lg shadow hover:bg-[#087C2D]">
                        Filter
                    </button>
                </div>
                <div class="md:col-span-1">
                    <a href="{{ route('documents.review') }}"
                        class="block text-center w-full bg-gray-300 px-4 py-2 rounded-lg shadow hover:bg-gray-400">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <p class="text-gray-700 text-sm ml-1">Menampilkan <b>{{ $totalResult }}</b> dokumen.</p>

        <div class="bg-white p-5 rounded-xl shadow-lg border overflow-x-auto">
            <table class="w-full text-sm table-sticky">
                <thead>
                    <tr class="bg-[#E8FCEB] text-[#0A7A2D] font-semibold text-left">
                        <th class="p-3">Departemen</th>
                        <th class="p-3">Site</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3">Nomor Dokumen</th>
                        <th class="p-3">Judul</th>
                        <th class="p-3 text-center">Review Berikutnya</th>
                        <th class="p-3">Terbit</th>
                        <th class="p-3 text-center">File</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        @php
                            $isOverdue = $doc->review_date && $doc->review_date->lt(now()->startOfDay());
                            $isDueSoon = $doc->review_date && $doc->review_date->between(now()->startOfDay(), now()->addDays(30)->endOfDay());
                        @endphp
                        <tr class="border-b hover:bg-[#F3FAF6]">
                            <td class="p-3">{{ $doc->department->name ?? '-' }}</td>
                            <td class="p-3">{{ $doc->site->name ?? '-' }}</td>
                            <td class="p-3">{{ $doc->kategori }}</td>
                            <td class="p-3 font-semibold text-gray-800">
                                <a href="{{ route('documents.show', $doc->id) }}" class="text-black hover:underline">
                                    {{ $doc->document_number }}
                                </a>
                            </td>
                            <td class="p-3 font-medium text-gray-800">
                                <a href="{{ route('documents.show', $doc->id) }}" class="text-black hover:underline">
                                    {{ $doc->title }}
                                </a>
                            </td>
                            <td class="p-3 text-center">
                                <span class="{{ $isOverdue ? 'text-red-600 font-semibold' : ($isDueSoon ? 'text-amber-600 font-semibold' : 'text-gray-700') }}">
                                    {{ $doc->review_date?->format('Y-m-d') ?? '-' }}
                                </span>
                            </td>
                            <td class="p-3 text-gray-600">{{ $doc->published_at?->format('Y-m-d') ?? '-' }}</td>
                            <td class="p-3 text-center">
                                @if ($doc->kategori === 'FORM')
                                    <a href="{{ route('documents.download', $doc->id) }}"
                                        class="text-blue-600 hover:underline">
                                        <span class="tooltip" aria-label="Unduh Formulir">
                                            <span>⬇️</span>
                                            <span class="tooltip-text">Unduh Formulir</span>
                                        </span>
                                    </a>
                                    @if ($doc->form_description_path)
                                        <button
                                            onclick="openPdfModal('{{ route('documents.preview_description', $doc->id) }}')"
                                            class="text-blue-600 hover:underline ml-2">
                                            <span class="tooltip" aria-label="Lihat Penjelasan">
                                                <span>👁️</span>
                                                <span class="tooltip-text">Lihat Penjelasan</span>
                                            </span>
                                        </button>
                                    @endif
                                @else
                                    <button onclick="openPdfModal('{{ route('documents.preview', $doc->id) }}')"
                                        class="text-blue-600 hover:underline">
                                        <span class="tooltip" aria-label="Lihat">
                                            <span>👁️</span>
                                            <span class="tooltip-text">Lihat</span>
                                        </span>
                                    </button>
                                    <a href="{{ route('documents.download', $doc->id) }}"
                                        class="ml-2 text-blue-600 hover:underline">
                                        <span class="tooltip" aria-label="Unduh">
                                            <span>⬇️</span>
                                            <span class="tooltip-text">Unduh</span>
                                        </span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-3 text-center text-gray-500">Tidak ada dokumen ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $documents->links() }}</div>
    </div>

    {{-- PDF MODAL --}}
    <div id="pdfModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="relative bg-white rounded-xl shadow-2xl overflow-hidden w-[92%] max-w-6xl h-[92%] flex flex-col">
            <button onclick="closePdfModal()"
                class="absolute top-3 right-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-full px-4 py-1 shadow">X</button>
            <iframe id="pdfFrame" class="w-full h-full" style="border:none;" allow="fullscreen"
                loading="eager"></iframe>
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
