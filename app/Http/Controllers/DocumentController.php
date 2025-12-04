<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    /**
     * LIST DOKUMEN
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $documents = Document::with('department', 'creator');

        // Restriksi role
        if ($user->role_id == 3) {
            $documents->where('department_id', $user->department_id);
        }

        // Search
        if ($request->search) {
            $documents->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                    ->orWhere('document_number', 'LIKE', "%{$request->search}%");
            });
        }

        // Filter kategori
        if ($request->kategori) {
            $documents->where('kategori', $request->kategori);
        }

        // Filter tanggal
        if ($request->tanggal) {
            $documents->whereDate('created_at', $request->tanggal);
        }

        // Filter departemen
        if (in_array($user->role_id, [1, 2]) && $request->department_id) {
            $documents->where('department_id', $request->department_id);
        }

        // Sorting
        $allowedSort = ['document_number', 'title', 'kategori', 'created_at'];
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        if (!in_array($sort, $allowedSort)) {
            $sort = 'created_at';
        }

        $documents->orderBy($sort, $order);

        // Pagination
        $documents = $documents->paginate(10);
        $documents->appends($request->all());

        return view('documents.index', [
            'documents' => $documents,
            'departments' => Department::all(),
            'totalResult' => $documents->total(),
        ]);
    }

    /**
     * PREVIEW PDF DALAM MODAL
     */
    public function preview($id)
    {
        $doc = Document::findOrFail($id);

        $path = storage_path('app/public/' . $doc->file_path);

        if (!file_exists($path)) {
            abort(404, "File tidak ditemukan.");
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'X-Content-Type-Options' => 'nosniff'
        ]);
    }

    public function show($id)
    {
        $document = Document::with('department', 'creator')->findOrFail($id);
        return view('documents.show', compact('document'));
    }

    public function create()
    {
        return view('documents.create', [
            'departments' => Department::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_number' => 'required',
            'title' => 'required',
            'kategori' => 'required',
            'department_id' => 'required',
            'file' => 'required|mimes:pdf|max:51200',
        ]);

        $path = $request->file('file')->store('docs_storage', 'public');

        Document::create([
            'document_number' => $request->document_number,
            'title' => $request->title,
            'kategori' => $request->kategori,
            'department_id' => $request->department_id,
            'file_path' => $path,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil dibuat.');
    }

    public function edit($id)
    {
        return view('documents.edit', [
            'document' => Document::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'document_number' => 'required',
            'title' => 'required',
            'kategori' => 'required',
            'department_id' => 'required',
            'file' => 'nullable|mimes:pdf|max:51200',
        ]);

        $doc = Document::findOrFail($id);

        if ($request->file('file')) {
            $doc->file_path = $request->file('file')->store('docs_storage', 'public');
        }

        $doc->update([
            'document_number' => $request->document_number,
            'title' => $request->title,
            'kategori' => $request->kategori,
            'department_id' => $request->department_id,
        ]);

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Document::findOrFail($id)->delete();

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil dihapus.');
    }
}
