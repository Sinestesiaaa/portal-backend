<x-app-layout>

    <div class="max-w-3xl mx-auto px-6 py-10 space-y-6">

        <div class="bg-gradient-to-r from-[#0AA03A] to-[#16A34A] text-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold tracking-wide">✏️ Edit Departemen</h1>
            <p class="text-white/90 text-sm">Perbarui informasi departemen</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md border">
            <form action="{{ route('admin.departments.update', $department->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="font-semibold text-gray-700">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $department->name) }}"
                        class="mt-1 w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#0AA03A]"
                        required>
                    @error('name')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="font-semibold text-gray-700">Deskripsi</label>
                    <textarea name="description" rows="3"
                        class="mt-1 w-full border rounded-lg px-4 py-2">{{ old('description', $department->description) }}</textarea>
                </div>

                <div>
                    <label class="font-semibold text-gray-700">Icon (emoji)</label>
                    <input type="text" name="icon" value="{{ old('icon', $department->icon) }}"
                        class="mt-1 w-full border rounded-lg px-4 py-2"
                        placeholder="Contoh: 🔧">
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-end gap-3">
                    <a href="{{ route('admin.departments.index') }}"
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
