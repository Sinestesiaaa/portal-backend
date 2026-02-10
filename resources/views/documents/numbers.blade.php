<x-app-layout>
    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">Monitoring Nomor Dokumen</h1>
            <p class="text-white/90 text-sm">Halaman dasar untuk memantau nomor dokumen</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-md border">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <div class="md:col-span-4">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nomor/judul..."
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

                @canany(['isAdmin', 'isSuperUser'])
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
                @endcanany

                <div class="md:col-span-1">
                    <button class="w-full bg-[#0AA03A] text-white px-4 py-2 rounded-lg shadow hover:bg-[#087C2D]">
                        Filter
                    </button>
                </div>
                <div class="md:col-span-1">
                    <a href="{{ route('documents.numbers') }}"
                        class="block text-center w-full bg-gray-300 px-4 py-2 rounded-lg shadow hover:bg-gray-400">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <p class="text-gray-700 text-sm ml-1">Menampilkan <b>{{ $totalResult }}</b> dokumen.</p>

        <div class="bg-white p-5 rounded-xl shadow-lg border overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#E8FCEB] text-[#0A7A2D] font-semibold text-left">
                        <th class="p-3">Nomor Dokumen</th>
                        <th class="p-3">Judul</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3">Departemen</th>
                        <th class="p-3">Site</th>
                        <th class="p-3 text-center">Revisi</th>
                        <th class="p-3">Terbit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        <tr class="border-b hover:bg-[#F3FAF6]">
                            <td class="p-3 font-semibold text-gray-800">
                                <a href="{{ route('documents.show', $doc->id) }}" class="text-black hover:underline">
                                    {{ $doc->document_number }}
                                </a>
                            </td>
                            <td class="p-3">{{ $doc->title }}</td>
                            <td class="p-3">{{ $doc->kategori }}</td>
                            <td class="p-3">{{ $doc->department->name ?? '-' }}</td>
                            <td class="p-3">{{ $doc->site->name ?? '-' }}</td>
                            <td class="p-3 text-center">Rev. {{ $doc->revision_number ?? 0 }}</td>
                            <td class="p-3">{{ $doc->published_at?->format('Y-m-d') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-3 text-center text-gray-500">Tidak ada dokumen ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $documents->links() }}</div>
    </div>
</x-app-layout>

