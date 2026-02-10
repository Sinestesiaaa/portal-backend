<x-app-layout>

    <div class="max-w-3xl mx-auto px-6 py-10 space-y-6">

        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">Edit Site</h1>
            <p class="text-white/90 text-sm">Perbarui informasi site</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md border">
            <form action="{{ route('admin.sites.update', $site->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="font-semibold text-gray-700">Kode Site</label>
                    <input type="text" name="code" value="{{ old('code', $site->code) }}"
                        class="mt-1 w-full border rounded-lg px-4 py-2" required>
                    @error('code')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="font-semibold text-gray-700">Nama Site</label>
                    <input type="text" name="name" value="{{ old('name', $site->name) }}"
                        class="mt-1 w-full border rounded-lg px-4 py-2" required>
                    @error('name')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="font-semibold text-gray-700">Deskripsi</label>
                    <textarea name="description" rows="3"
                        class="mt-1 w-full border rounded-lg px-4 py-2">{{ old('description', $site->description) }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300"
                        {{ old('is_active', $site->is_active) ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Aktif</span>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-end gap-3">
                    <a href="{{ route('admin.sites.index') }}"
                        class="w-full sm:w-auto px-5 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 text-center">
                        Batal
                    </a>
                    <button type="submit"
                        class="w-full sm:w-auto px-6 py-2 bg-[#0AA03A] text-white rounded-lg shadow hover:bg-[#087C2D]">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>

