<x-app-layout>

    <div class="max-w-4xl mx-auto px-6 py-10">

        <h1 class="text-2xl font-bold mb-6 text-[#1e8f4d]">Tambah Dokumen</h1>

        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data"
            class="bg-white shadow rounded-lg p-6 space-y-5">
            @csrf

            <div>
                <label class="font-semibold">Nomor Dokumen</label>
                <input type="text" name="document_number" class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="font-semibold">Judul</label>
                <input type="text" name="title" class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="font-semibold">Kategori</label>
                <select name="kategori" class="w-full border p-2 rounded" required>
                    <option value="SOP">SOP</option>
                    <option value="IK">IK</option>
                    <option value="FORM">FORM</option>
                    <option value="STD">STD</option>
                </select>
            </div>

            <div>
                <label class="font-semibold">Departemen</label>
                <select name="department_id" class="w-full border p-2 rounded" required>
                    @foreach(\App\Models\Department::all() as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-semibold">Upload File (PDF)</label>
                <input type="file" name="file" accept="application/pdf" class="w-full border p-2 rounded" required>
            </div>

            <div class="flex justify-end">
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Simpan
                </button>
            </div>

        </form>

    </div>

</x-app-layout>