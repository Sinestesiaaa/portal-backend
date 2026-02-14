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
                    <select name="kategori" id="kategoriSelect"
                        class="mt-1 w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#0AA03A]">
                        @foreach ($documentTypes as $type)
                            <option value="{{ $type->name }}"
                                {{ $document->kategori == $type->name ? 'selected' : '' }}>
                                {{ $type->name }}
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
                <div>
                    <label class="font-semibold text-gray-700">Site (opsional)</label>
                    <select name="site_id"
                        class="mt-1 w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#0AA03A]">
                        <option value="">Tanpa Site</option>
                        @foreach ($sites as $site)
                            <option value="{{ $site->id }}"
                                {{ old('site_id', $document->site_id) == $site->id ? 'selected' : '' }}>
                                {{ $site->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('site_id')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>
                {{-- Tanggal Terbit --}}
                <div><label class="block mb-2 font-semibold">Tanggal Terbit Dokumen</label>
                    <input type="date" name="published_at" class="w-full border rounded-lg px-3 py-2"
                        value="{{ old('published_at', $document->published_at?->format('Y-m-d') ?? '') }}" required>
                </div>

                {{-- Review Berikutnya --}}
                <div>
                    <label class="block mb-2 font-semibold">Review Berikutnya (opsional)</label>
                    <input type="date" name="review_date" class="w-full border rounded-lg px-3 py-2"
                        value="{{ old('review_date', $document->review_date?->format('Y-m-d') ?? '') }}">
                </div>

                {{-- REVISION --}}
                <div class="border-t pt-4 space-y-3">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="isRevision" name="is_revision" value="1"
                            class="rounded border-gray-300">
                        <label for="isRevision" class="font-semibold text-gray-700">
                            Simpan sebagai revisi
                        </label>
                    </div>
                    <p class="text-xs text-gray-500">Jika dicentang, wajib upload file baru dan isi catatan revisi.</p>

                    <div id="revisionNoteWrapper" class="hidden">
                        <label class="block mb-1 font-semibold text-gray-700">Revisi ke-</label>
                        <input type="number" min="0" name="revision_number"
                            value="{{ old('revision_number', $document->revision_number ?? 0) }}"
                            class="w-full border rounded-lg px-3 py-2 mb-3">

                        <label class="block mb-1 font-semibold text-gray-700">Catatan Revisi</label>
                        <textarea name="revision_note" rows="3"
                            class="w-full border rounded-lg px-3 py-2">{{ old('revision_note') }}</textarea>
                        @error('revision_note')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

            </div>


            {{-- RIGHT SIDE — DROPZONE + PREVIEW --}}
            <div class="bg-white p-6 rounded-xl shadow-md border space-y-6">

                {{-- CURRENT PDF PREVIEW --}}
                <div>
                    <label class="font-semibold text-gray-700">Preview Dokumen Saat Ini</label>

                    <div class="mt-3 border rounded-xl overflow-hidden shadow-sm">
                        @if ($document->kategori === 'FORM')
                            @if ($document->form_description_path)
                                <iframe src="{{ route('documents.preview_description', $document->id) }}"
                                    class="w-full" style="height: 500px; border: none;"></iframe>
                            @else
                                <div class="p-4 text-sm text-gray-500">
                                    Penjelasan form belum ada.
                                </div>
                            @endif
                        @else
                            <iframe src="{{ route('documents.preview', $document->id) }}" class="w-full"
                                style="height: 500px; border: none;"></iframe>
                        @endif
                    </div>

                    {{-- FILE INFO --}}
                    <p class="text-sm text-gray-500 mt-2">
                        @if ($document->kategori === 'FORM')
                            Penjelasan: <span
                                class="font-semibold">{{ $document->form_description_path ? basename($document->form_description_path) : '-' }}</span>
                        @else
                            File: <span class="font-semibold">{{ basename($document->file_path) }}</span>
                        @endif
                    </p>
                </div>

                {{-- DROPZONE UNTUK REPLACE FILE --}}
                <div>
                    <label class="font-semibold text-gray-700" id="uploadLabel">Ganti File (Opsional)</label>

                    <div id="file-dropzone"
                        class="mt-3 dropzone border-2 border-dashed border-gray-300 rounded-xl p-5
                               flex flex-col items-center justify-center text-center cursor-pointer
                               transition hover:border-[#0AA03A]">

                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400 mb-3" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16c0 .88.39 1.67 1 2.22m0 0A3.001 3.001 0 0012 19a3.001 3.001 0 003-3 3.001 3.001 0 00-3-3 3.001 3.001 0 00-4 3m9 0H7" />
                        </svg>

                        <p class="text-gray-600 font-medium" id="uploadHint">Drop PDF atau klik untuk upload</p>
                        <p class="text-gray-400 text-sm">File baru (maksimal 50MB)</p>
                    </div>

                    <input type="file" name="file" id="real-file-input" class="hidden"
                        accept="application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" />

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

        {{-- FORM DESCRIPTION (OPTIONAL) --}}
        <div id="form-description-wrapper"
            class="mt-6 bg-white p-6 rounded-xl shadow-md border hidden">
            <label class="font-semibold text-gray-700">Ganti Penjelasan Form (PDF, opsional)</label>
            <input type="file" name="form_description" form="editForm"
                class="mt-2 w-full border rounded-lg px-3 py-2"
                accept="application/pdf" />
            <p class="text-gray-400 text-sm mt-2">Khusus kategori FORM. Maksimal 50MB.</p>
            @error('form_description')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror

            <div class="mt-3 flex items-center gap-2">
                <input type="checkbox" name="remove_form_description" value="1" form="editForm"
                    class="rounded border-gray-300">
                <span class="text-sm text-gray-700">Hapus penjelasan form</span>
            </div>
        </div>


        {{-- BOTTOM BUTTONS --}}
        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 mt-6">
            <a href="{{ route('documents.index') }}"
                class="w-full sm:w-auto px-5 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 text-center">
                Batal
            </a>

            <button type="submit" form="editForm"
                class="w-full sm:w-auto px-6 py-2 bg-[#0AA03A] text-white rounded-lg shadow hover:bg-[#087C2D]">
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
        const kategoriSelect = document.getElementById('kategoriSelect');
        const uploadLabel = document.getElementById('uploadLabel');
        const uploadHint = document.getElementById('uploadHint');
        const formDescWrapper = document.getElementById('form-description-wrapper');
        const isRevision = document.getElementById('isRevision');
        const revisionNoteWrapper = document.getElementById('revisionNoteWrapper');

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

        function updateKategoriUI() {
            const isForm = kategoriSelect.value === 'FORM';
            if (isForm) {
                uploadLabel.textContent = 'Ganti Form (XLS/XLSX/DOC/DOCX) - Opsional';
                uploadHint.textContent = 'Drop file Excel/Word atau klik untuk upload';
                fileInput.accept =
                    'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                formDescWrapper.classList.remove('hidden');
            } else {
                uploadLabel.textContent = 'Ganti File (Opsional)';
                uploadHint.textContent = 'Drop PDF atau klik untuk upload';
                fileInput.accept = 'application/pdf';
                formDescWrapper.classList.add('hidden');
            }
        }

        function updateRevisionUI() {
            if (isRevision.checked) {
                revisionNoteWrapper.classList.remove('hidden');
            } else {
                revisionNoteWrapper.classList.add('hidden');
            }
        }

        kategoriSelect.addEventListener('change', updateKategoriUI);
        updateKategoriUI();

        isRevision.addEventListener('change', updateRevisionUI);
        updateRevisionUI();
    </script>

</x-app-layout>
