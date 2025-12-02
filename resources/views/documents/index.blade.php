<x-app-layout>

    <div class="max-w-7xl mx-auto px-6 py-10">

        <h1 class="text-2xl font-bold mb-6 text-[#1e8f4d]">
            Daftar Dokumen
        </h1>

        {{-- Alerts --}}
        @if (session('success'))
        <div class="mb-4 p-3 bg-green-200 text-green-900 rounded-md">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="mb-4 p-3 bg-red-200 text-red-900 rounded-md">
            {{ session('error') }}
        </div>
        @endif


        {{-- SEARCH HIGHLIGHT --}}
        @php
        function highlight($text, $keyword) {
        if (!$keyword) return $text;
        return preg_replace('/('.preg_quote($keyword,'/').')/i',
        '<span class="bg-yellow-200">$1</span>', $text);
        }
        @endphp


        {{-- SORT ICON HELPER --}}
        @php
        function sort_icon($field) {
        if (request('sort') != $field) {
        return '<span class="opacity-40 text-sm">↕</span>';
        }

        return request('order') == 'asc'
        ? '<span class="text-sm">↑</span>'
        : '<span class="text-sm">↓</span>';
        }
        @endphp


        {{-- FILTER FORM --}}
        <form method="GET" class="flex items-center justify-between mb-4">

            <div class="flex items-center gap-2">

                {{-- Search --}}
                <input type="text" name="search" placeholder="Cari dokumen..."
                    value="{{ request('search') }}"
                    class="border rounded px-3 py-2 w-64">

                {{-- Kategori --}}
                <select name="kategori" class="border rounded px-3 py-2">
                    <option value="">Semua Kategori</option>
                    @foreach(['SOP','IK','FORM','STD'] as $cat)
                    <option value="{{ $cat }}" {{ request('kategori') == $cat ? 'selected' : '' }}>
                        {{ $cat }}
                    </option>
                    @endforeach
                </select>

                {{-- Tanggal --}}
                <input type="date" name="tanggal"
                    value="{{ request('tanggal') }}"
                    class="border rounded px-3 py-2">

                {{-- Filter Departemen (Admin & Super User Only) --}}
                @if(in_array(auth()->user()->role_id, [1, 2]))
                <select name="department_id" class="border rounded px-3 py-2">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}"
                        {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                    @endforeach
                </select>
                @endif


                {{-- Filter --}}
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Filter
                </button>

                {{-- Reset --}}
                <a href="{{ route('documents.index') }}"
                    class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">
                    Reset
                </a>
            </div>

            {{-- Tambah Dokumen --}}
            @if(auth()->user()->role_id == 1)
            <a href="{{ route('documents.create') }}"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                Tambah Dokumen
            </a>
            @endif

        </form>


        {{-- TOTAL RESULT --}}
        <p class="text-gray-600 mb-3">
            Menampilkan <b>{{ $totalResult }}</b> dokumen hasil filter.
        </p>


        {{-- TABLE --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#e6f6ec] border-b">
                    <tr>
                        <th class="p-3 font-semibold text-gray-700">
                            <a href="?sort=document_number&order={{ request('order')=='asc'?'desc':'asc' }}">
                                Nomor {!! sort_icon('document_number') !!}
                            </a>
                        </th>

                        <th class="p-3 font-semibold text-gray-700">
                            <a href="?sort=title&order={{ request('order')=='asc'?'desc':'asc' }}">
                                Judul {!! sort_icon('title') !!}
                            </a>
                        </th>

                        <th class="p-3 font-semibold text-gray-700">
                            <a href="?sort=kategori&order={{ request('order')=='asc'?'desc':'asc' }}">
                                Kategori {!! sort_icon('kategori') !!}
                            </a>
                        </th>

                        <th class="p-3 font-semibold text-gray-700">
                            Departemen
                        </th>

                        <th class="p-3 font-semibold text-gray-700">
                            <a href="?sort=created_at&order={{ request('order')=='asc'?'desc':'asc' }}">
                                Tanggal Terbit {!! sort_icon('created_at') !!}
                            </a>
                        </th>

                        <th class="p-3 font-semibold text-gray-700">File</th>
                        <th class="p-3 font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($documents as $doc)
                    <tr class="hover:bg-gray-50 transition">

                        <td class="p-3 border-b">{!! highlight($doc->document_number, request('search')) !!}</td>

                        <td class="p-3 border-b">{!! highlight($doc->title, request('search')) !!}</td>

                        <td class="p-3 border-b">

                            @php
                            // Warna kategori
                            $color = match($doc->kategori) {
                            'IK' => 'bg-green-100 text-green-800',
                            'STD' => 'bg-blue-100 text-blue-800',
                            'SOP' => 'bg-orange-100 text-orange-800',
                            'FORM' => 'bg-gray-800 text-white',
                            default => 'bg-gray-100 text-gray-700',
                            };

                            // Ikon kategori
                            $icon = match($doc->kategori) {
                            'IK' => '📗',
                            'STD' => '📘',
                            'SOP' => '📙',
                            'FORM' => '📄',
                            default => '📁',
                            };
                            @endphp

                            <span class="px-3 py-1 rounded-full text-sm flex items-center gap-1 {{ $color }}">
                                <span>{{ $icon }}</span> {{ $doc->kategori }}
                            </span>
                        </td>


                        <td class="p-3 border-b">{{ $doc->department->name ?? '-' }}</td>

                        <td class="p-3 border-b">{{ $doc->created_at->format('Y-m-d') }}</td>

                        <td class="p-3 border-b">
                            <a href="#"
                                class="text-blue-600 hover:underline open-pdf"
                                data-url="{{ asset('storage/' . $doc->file_path) }}">
                                Lihat
                            </a>
                        </td>

                        <td class="p-3 border-b">

                            @if(auth()->user()->role_id == 1)

                            <a href="{{ route('documents.edit', $doc->id) }}"
                                class="text-yellow-600 hover:text-yellow-800 mr-3">
                                Edit
                            </a>

                            <form action="{{ route('documents.destroy', $doc->id) }}"
                                method="POST" class="inline-block"
                                onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">

                                @csrf
                                @method('DELETE')

                                <button class="text-red-600 hover:text-red-800">Hapus</button>
                            </form>

                            @else
                            <span class="text-gray-400 text-sm">Tidak ada aksi</span>
                            @endif

                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-3 text-center text-gray-500">
                            Belum ada dokumen.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        {{-- PAGINATION --}}
        <div class="mt-4 flex justify-center">
            {{ $documents->links() }}
        </div>

    </div>


    {{-- PDF MODAL --}}
    <div id="pdfModal"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center">

        <div class="bg-white w-11/12 h-[90vh] shadow-xl rounded-lg relative">

            <button onclick="closePdfModal()"
                class="absolute top-2 right-2 bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                X
            </button>

            <iframe id="pdfFrame"
                src=""
                class="w-full h-full rounded-lg">
            </iframe>

        </div>
    </div>


    {{-- SCRIPT MODAL --}}
    <script>
        // klik Lihat → buka modal
        document.querySelectorAll('.open-pdf').forEach(el => {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                openPdfModal(this.dataset.url);
            });
        });

        function openPdfModal(url) {
            document.getElementById('pdfFrame').src = url;
            document.getElementById('pdfModal').classList.remove('hidden');
        }

        function closePdfModal() {
            document.getElementById('pdfModal').classList.add('hidden');
            document.getElementById('pdfFrame').src = "";
        }
    </script>

</x-app-layout>