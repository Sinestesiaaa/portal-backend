<x-app-layout>

    {{-- HEADER --}}
    <div class="max-w-7xl mx-auto px-6 py-10">
        <div class="p-6 rounded-xl bg-gradient-to-r from-[#0AA03A] to-[#16A34A] shadow-lg text-white mb-8">
            <h1 class="text-2xl font-bold">📄 Buat Dokumen Baru</h1>
            <p class="text-white/90 text-sm">Tambahkan dokumen baru ke dalam sistem portal.</p>
        </div>

        {{-- FORM WRAPPER --}}
        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="createForm"
            class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            @csrf

            {{-- LEFT SIDE — FORM INPUT --}}
            <div class="bg-white p-6 rounded-xl shadow-md border space-y-5">

                {{-- Nomor Dokumen --}}
                <div>
                    <label class="font-semibold text-gray-700">Nomor Dokumen</label>
                    <input type="text" name="document_number" value="{{ old('document_number') }}"
                        class="mt-1 w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#0AA03A]" required>
                    @error('document_number')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Judul --}}
                <div>
                    <label class="font-semibold text-gray-700">Judul Dokumen</label>
                    <input type="text" name="title" value="{{ old('title') }}"
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
                        <option value="">Pilih kategori</option>
                        <option value="SOP">SOP</option>
                        <option value="IK">IK</option>
                        <option value="FORM">FORM</option>
                        <option value="STD">STD</option>
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
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

            </div>


            {{-- RIGHT SIDE — DROPZONE --}}
            <div class="bg-white p-6 rounded-xl shadow-md border">

                <label class="font-semibold text-gray-700">Upload Dokumen (PDF)</label>

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
                    <p class="text-gray-400 text-sm">Maksimal 50MB</p>
                </div>

                <input type="file" name="file" id="real-file-input" class="hidden" accept="application/pdf"
                    required />

                @error('file')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror

                {{-- Preview file name --}}
                <div id="file-preview" class="mt-4 hidden">
                    <div class="flex justify-between items-center bg-gray-100 p-3 rounded-lg border">
                        <span id="file-preview-name" class="text-sm font-medium text-gray-700"></span>

                        <button type="button" onclick="removeSelectedFile()"
                            class="text-red-600 font-semibold text-sm">
                            Hapus
                        </button>
                    </div>
                </div>

            </div>

        </form>

        {{-- BOTTOM BUTTONS --}}
        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('documents.index') }}" class="px-5 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">
                Batal
            </a>

            <button type="submit" form="createForm"
                class="px-6 py-2 bg-[#0AA03A] text-white rounded-lg shadow hover:bg-[#087C2D]">
                Simpan Dokumen
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
