<x-app-layout>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">Manajemen Site</h1>
            <p class="text-white/90 text-sm">Tambah, ubah, dan hapus site</p>
        </div>

        @if (session('success'))
            <div class="p-3 bg-green-200 text-green-900 rounded-lg shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white p-5 rounded-xl shadow-md border">
            <form method="GET" class="flex flex-col md:flex-row gap-3 items-end">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari kode/nama site..."
                        class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-[#16A34A]">
                </div>
                <div>
                    <select name="status" class="border border-gray-300 rounded-lg px-4 py-2 w-full">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <button class="w-full md:w-auto bg-[#0AA03A] text-white px-5 py-2 rounded-lg shadow hover:bg-[#087C2D]">
                    Filter
                </button>
                <a href="{{ route('admin.sites.index') }}"
                    class="w-full md:w-auto bg-gray-300 px-5 py-2 rounded-lg shadow hover:bg-gray-400 text-center">
                    Reset
                </a>
                <a href="{{ route('admin.sites.create') }}"
                    class="w-full md:w-auto md:ml-auto bg-[#0AA03A] text-white px-5 py-2 rounded-lg shadow hover:bg-[#087C2D] text-center">
                    Tambah Site
                </a>
            </form>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-lg border overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#E8FCEB] text-black font-semibold text-left">
                        <th class="p-3">Kode</th>
                        <th class="p-3">Nama</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Deskripsi</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sites as $site)
                        <tr class="border-b hover:bg-[#F3FAF6]">
                            <td class="p-3 font-semibold text-gray-800">{{ $site->code }}</td>
                            <td class="p-3">{{ $site->name }}</td>
                            <td class="p-3">
                                <span class="inline-flex px-2 py-1 rounded text-xs {{ $site->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' }}">
                                    {{ $site->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="p-3 text-gray-700">{{ $site->description ?? '-' }}</td>
                            <td class="p-3 text-center">
                                <div class="flex justify-center gap-3">
                                    <a href="{{ route('admin.sites.edit', $site->id) }}"
                                        class="text-yellow-600 hover:text-yellow-700">Edit</a>
                                    <form action="{{ route('admin.sites.destroy', $site->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:text-red-700">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">Tidak ada site ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">{{ $sites->links() }}</div>
        </div>

    </div>

</x-app-layout>

