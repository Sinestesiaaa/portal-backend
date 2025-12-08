<x-app-layout>

    {{-- HEADER --}}
    <div class="max-w-7xl mx-auto px-6 py-10">
        <div class="p-6 rounded-xl bg-gradient-to-r from-[#0AA03A] to-[#16A34A] shadow-lg text-white mb-8">
            <h1 class="text-2xl font-bold">✏️ Edit Dokumen</h1>
            <p class="text-white/90 text-sm">Perbarui informasi dokumen yang sudah ada.</p>
        </div>

        {{-- FORM WRAPPER --}}
        <form action="{{ route('documents.update', $document->id) }}" method="POST" enctype="multipart/form-data"
            id="editForm" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            @csrf
            @method('PUT')

            {{-- LEFT SIDE — FORM INPUT --}}
            <div class="bg-white p-6 rounded-xl shadow-md border space-y-5">

                {{-- Nomor Dokumen --}}
                <div>
                    <label class="font-semibold text-gray-700">Nomor Dokumen</label>
                    <input type="text" name="document_number"
                        value="{{ old('document_number', $document->document_number) }}"
                        class="mt-1 w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#0AA03A]" required>
                    @error('document_number')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Judul --}}
                <div>
                    <label class="font-semibold text-gray-700">Judul Dokumen</label>
                    <input type="text" name="title" value="{{ old('title', $document->title) }}"
                        class="mt-1 w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#0AA03A]" required>
                    @error('title')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="font-semibold text-gray-700">Kategori</label>
                    <select name="kategori"
                        class="mt-1 w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#0AA03A]">
                        @foreach (['SOP', 'IK', 'FORM', 'STD'] as $kat)
                            <option value="{{ $kat }}" {{ $document->kategori == $kat ? 'selected' : '' }}>
                                {{ $kat }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Departemen --}}
                <div>
                    <label class="font-semibold text-gray-700">Departemen</label>
                    <select name="department_id"
                        class="mt-1 w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#0AA03A]">
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}"
                                {{ $document->department_id == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                {{-- Tanggal Terbit --}}
                <div><label class="block mb-2 font-semibold">Tanggal Terbit Dokumen</label>
                    <input type="date" name="published_at" class="w-full border rounded-lg px-3 py-2"
                        value="{{ old('published_at', $document->published_at ?? '') }}" required>
                </div>

            </div>


            {{-- RIGHT SIDE — DROPZONE + PREVIEW --}}
            <div class="bg-white p-6 rounded-xl shadow-md border space-y-6">

                {{-- CURRENT PDF PREVIEW --}}
                <div>
                    <label class="font-semibold text-gray-700">Preview Dokumen Saat Ini</label>

                    <div class="mt-3 border rounded-xl overflow-hidden shadow-sm">
                        <iframe src="{{ route('documents.preview', $document->id) }}" class="w-full"
                            style="height: 500px; border: none;"></iframe>
                    </div>

                    {{-- FILE INFO --}}
                    <p class="text-sm text-gray-500 mt-2">
                        File: <span class="font-semibold">{{ basename($document->file_path) }}</span>
                    </p>
                </div>


                {{-- DROPZONE UNTUK REPLACE FILE --}}
                <div>
                    <label class="font-semibold text-gray-700">Ganti File (Opsional)</label>

                    <div id="file-dropzone"
                        class="mt-3 dropzone border-2 border-dashed border-gray-300 rounded-xl p-5
                               flex flex-col items-center justify-center text-center cursor-pointer
                               transition hover:border-[#0AA03A]">

                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400 mb-3" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16c0 .88.39 1.67 1 2.22m0 0A3.001 3.001 0 0012 19a3.001 3.001 0 003-3 3.001 3.001 0 00-3-3 3.001 3.001 0 00-4 3m9 0H7" />
                        </svg>

                        <p class="text-gray-600 font-medium">Drop PDF atau klik untuk upload</p>
                        <p class="text-gray-400 text-sm">File baru (maksimal 50MB)</p>
                    </div>

                    <input type="file" name="file" id="real-file-input" class="hidden" accept="application/pdf" />

                    {{-- PREVIEW FILE NAME --}}
                    <div id="file-preview" class="mt-4 hidden">
                        <div class="flex justify-between items-center bg-gray-100 p-3 rounded-lg border">
                            <span id="file-preview-name" class="text-sm font-medium text-gray-700"></span>
                            <button type="button" onclick="removeSelectedFile()"
                                class="text-red-600 font-semibold text-sm">
                                Hapus
                            </button>
                        </div>
                    </div>

                    @error('file')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

        </form>


        {{-- BOTTOM BUTTONS --}}
        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('documents.index') }}" class="px-5 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">
                Batal
            </a>

            <button type="submit" form="editForm"
                class="px-6 py-2 bg-[#0AA03A] text-white rounded-lg shadow hover:bg-[#087C2D]">
                Update Dokumen
            </button>
        </div>

    </div>


    {{-- DROPZONE SCRIPT --}}
    <script>
        const dz = document.getElementById('file-dropzone');
        const fileInput = document.getElementById('real-file-input');
        const preview = document.getElementById('file-preview');
        const previewName = document.getElementById('file-preview-name');

        dz.addEventListener('click', () => fileInput.click());

        dz.addEventListener('dragover', function(e) {
            e.preventDefault();
            dz.classList.add('border-[#0AA03A]', 'bg-green-50');
        });

        dz.addEventListener('dragleave', function() {
            dz.classList.remove('border-[#0AA03A]', 'bg-green-50');
        });

        dz.addEventListener('drop', function(e) {
            e.preventDefault();
            dz.classList.remove('border-[#0AA03A]', 'bg-green-50');

            fileInput.files = e.dataTransfer.files;
            showPreview();
        });

        fileInput.addEventListener('change', showPreview);

        function showPreview() {
            const file = fileInput.files[0];
            if (!file) return;

            previewName.textContent = file.name;
            preview.classList.remove('hidden');
        }

        function removeSelectedFile() {
            fileInput.value = '';
            preview.classList.add('hidden');
        }
    </script>

</x-app-layout>
