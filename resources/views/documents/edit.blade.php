<x-app-layout>

    <div class="max-w-4xl mx-auto px-6 py-10">

        <h1 class="text-2xl font-bold mb-6 text-[#1e8f4d]">
            Edit Dokumen
        </h1>

        <form action="{{ route('documents.update', $document->id) }}"
            method="POST" enctype="multipart/form-data"
            class="bg-white shadow rounded-lg p-6 space-y-5">

            @csrf
            @method('PUT')

            <div>
                <label class="font-semibold">Nomor Dokumen</label>
                <input type="text" name="document_number"
                    value="{{ $document->document_number }}"
                    class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="font-semibold">Judul</label>
                <input type="text" name="title"
                    value="{{ $document->title }}"
                    class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="font-semibold">Kategori</label>
                <select name="kategori" class="w-full border p-2 rounded" required>
                    <option value="SOP" {{ $document->kategori == 'SOP' ? 'selected' : '' }}>SOP</option>
                    <option value="IK" {{ $document->kategori == 'IK' ? 'selected' : '' }}>IK</option>
                    <option value="FORM" {{ $document->kategori == 'FORM' ? 'selected' : '' }}>FORM</option>
                    <option value="STD" {{ $document->kategori == 'STD' ? 'selected' : '' }}>STD</option>
                </select>
            </div>

            <div>
                <label class="font-semibold">Departemen</label>
                <select name="department_id" class="w-full border p-2 rounded" required>
                    @foreach(\App\Models\Department::all() as $dept)
                    <option value="{{ $dept->id }}"
                        {{ $document->department_id == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-semibold">File Baru (Opsional)</label>
                <input type="file" name="file" accept="application/pdf" class="w-full border p-2 rounded">

                <p class="text-sm text-gray-500 mt-1">
                    File saat ini:
                    <a href="{{ asset('storage/' . $document->file_path) }}"
                        target="_blank"
                        class="text-blue-600 underline">
                        Lihat File
                    </a>
                </p>
            </div>

            <div class="flex justify-end">
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Update
                </button>
            </div>

        </form>

    </div>

</x-app-layout>