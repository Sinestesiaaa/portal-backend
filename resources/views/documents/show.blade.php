<x-app-layout>
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">{{ $document->title }}</h1>

        <p><strong>Nomor:</strong> {{ $document->document_number }}</p>
        <p><strong>Kategori:</strong> {{ $document->kategori }}</p>
        <p><strong>Departemen:</strong> {{ $document->department->name ?? '-' }}</p>

        <a href="{{ asset('storage/'.$document->file_path) }}"
            class="text-blue-600 underline"
            target="_blank">Lihat File</a>
    </div>
</x-app-layout>
