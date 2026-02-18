<x-app-layout>
    <div class="max-w-3xl mx-auto px-6 py-10 space-y-6">
        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">Edit Riwayat Revisi</h1>
            <p class="text-white/90 text-sm">{{ $document->document_number }} - {{ $document->title }}</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md border">
            <form method="POST" action="{{ route('documents.revisions.update', [$document->id, $revision->id]) }}"
                class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block mb-2 font-semibold">Nomor Revisi</label>
                    <input type="number" min="0" name="revision_number"
                        value="{{ old('revision_number', $revision->revision_number) }}"
                        class="w-full border rounded-lg px-3 py-2" required>
                </div>

                <div>
                    <label class="block mb-2 font-semibold">Tanggal Revisi</label>
                    <input type="date" name="revised_at"
                        value="{{ old('revised_at', $revision->revised_at?->format('Y-m-d')) }}"
                        class="w-full border rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="block mb-2 font-semibold">Catatan Revisi</label>
                    <textarea name="revision_note" rows="4" class="w-full border rounded-lg px-3 py-2" required>{{ old('revision_note', $revision->revision_note) }}</textarea>
                </div>

                <div class="flex gap-2 justify-end">
                    <a href="{{ route('documents.show', $document->id) }}"
                        class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-[#0AA03A] text-white rounded-lg hover:bg-[#087C2D]">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

