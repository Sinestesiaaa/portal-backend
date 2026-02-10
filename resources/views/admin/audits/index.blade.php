<x-app-layout>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">🧾 Audit Log</h1>
            <p class="text-white/90 text-sm">Riwayat aktivitas dokumen</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-md border">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
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
                <div class="flex flex-col sm:flex-row gap-2 md:col-span-5">
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
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#E8FCEB] text-black font-semibold text-left">
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Aksi</th>
                        <th class="p-3">Dokumen</th>
                        <th class="p-3">Oleh</th>
                        <th class="p-3">Info</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($audits as $audit)
                        <tr class="border-b hover:bg-[#F3FAF6]">
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
                                    {{ $audit->meta['revision_note'] ?? $audit->meta['title'] ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">Belum ada audit log.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $audits->links() }}
            </div>
        </div>

    </div>

</x-app-layout>
