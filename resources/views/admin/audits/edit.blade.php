<x-app-layout>
    <div class="max-w-4xl mx-auto px-6 py-10 space-y-6">
        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">Edit Audit Log</h1>
            <p class="text-white/90 text-sm">Mode local development</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md border space-y-4">
            <form method="POST" action="{{ route('admin.audits.update', $audit->id) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 font-semibold">Waktu</label>
                        <input type="text" value="{{ $audit->created_at?->format('Y-m-d H:i:s') ?? '-' }}"
                            class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold">Dokumen</label>
                        <input type="text"
                            value="{{ $audit->document->document_number ?? '-' }} - {{ $audit->document->title ?? '-' }}"
                            class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                    </div>
                </div>

                <div>
                    <label class="block mb-2 font-semibold">Aksi</label>
                    <select name="action" class="w-full border rounded-lg px-3 py-2">
                        @foreach (['create', 'update', 'revision', 'delete'] as $action)
                            <option value="{{ $action }}" {{ old('action', $audit->action) === $action ? 'selected' : '' }}>
                                {{ strtoupper($action) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-2 font-semibold">Meta (JSON)</label>
                    <textarea name="meta_json" rows="8" class="w-full border rounded-lg px-3 py-2 font-mono text-sm">{{ old('meta_json', $audit->meta ? json_encode($audit->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                    @error('meta_json')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-2 justify-end">
                    <a href="{{ route('admin.audits.index') }}"
                        class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-[#0AA03A] text-white rounded-lg hover:bg-[#087C2D]">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

