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

        {{-- Tombol Tambah Dokumen --}}
        @if(auth()->user()->role_id == 1)
        <div class="flex justify-end mb-4">
            <a href="{{ route('documents.create') }}"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                Tambah Dokumen
            </a>
        </div>
        @endif

        {{-- Tabel Dokumen --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#e6f6ec] border-b">
                    <tr>
                        <th class="p-3 font-semibold text-gray-700">Nomor</th>
                        <th class="p-3 font-semibold text-gray-700">Judul</th>
                        <th class="p-3 font-semibold text-gray-700">Kategori</th>
                        <th class="p-3 font-semibold text-gray-700">Departemen</th>
                        <th class="p-3 font-semibold text-gray-700">Tanggal Upload</th>
                        <th class="p-3 font-semibold text-gray-700">File</th>
                        <th class="p-3 font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($documents as $doc)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-3 border-b">{{ $doc->document_number }}</td>
                        <td class="p-3 border-b">{{ $doc->title }}</td>
                        <td class="p-3 border-b">{{ $doc->kategori }}</td>
                        <td class="p-3 border-b">{{ $doc->department->name ?? '-' }}</td>
                        <td class="p-3 border-b">
                            {{ $doc->created_at ? $doc->created_at->format('Y-m-d') : '-' }}
                        </td>

                        <td class="p-3 border-b">
                            <a href="{{ asset('storage/' . $doc->file_path) }}"
                                target="_blank"
                                class="text-blue-600 hover:underline">
                                Lihat
                            </a>
                        </td>

                        <td class="p-3 border-b">
                            {{-- Aksi Khusus Admin --}}
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
    </div>

</x-app-layout>