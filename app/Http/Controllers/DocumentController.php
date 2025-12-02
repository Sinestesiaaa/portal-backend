<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    /**
     * LIST DOKUMEN (ALL ROLES)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Base query + relasi
        $documents = Document::with('department', 'creator');

        // ============================
        // ROLE RESTRICTION
        // ============================
        if ($user->role_id == 3) {
            // User hanya bisa lihat dokumen departemen sendiri
            $documents->where('department_id', $user->department_id);
        }

        // ============================
        // FILTERS
        // ============================

        // Search (judul / nomor)
        if ($request->search) {
            $documents->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('document_number', 'LIKE', '%' . $request->search . '%');
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

        // Filter departemen (admin only)
        if (in_array($user->role_id, [1, 2]) && $request->department_id) {
            $documents->where('department_id', $request->department_id);
        }

        // Filter creator (admin only)
        // if (in_array($user->role_id, [1, 2]) && $request->creator_id) {
        //     $documents->where('created_by', $request->creator_id);
        // }

        // ============================
        // SORTING
        // ============================
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $allowedSort = [
            'document_number',
            'title',
            'kategori',
            'created_at',
        ];

        if (!in_array($sort, $allowedSort)) {
            $sort = 'created_at';
        }

        $documents = $documents->orderBy($sort, $order);

        // ============================
        // PAGINATION
        // ============================
        $documents = $documents->paginate(10);
        $documents->appends($request->all());

        // Hitung hasil filter
        $totalResult = $documents->total();

        return view('documents.index', [
            'documents' => $documents,
            'departments' => \App\Models\Department::all(),
            'totalResult' => $totalResult
        ]);
    }


    /**
     * SHOW
     */
    public function show($id)
    {
        $document = Document::with('department', 'creator')->findOrFail($id);
        return view('documents.show', compact('document'));
    }

    /**
     * CREATE (ADMIN)
     */
    public function create()
    {
        $departments = Department::all();
        return view('documents.create', compact('departments'));
    }

    /**
     * STORE (ADMIN)
     */
    public function store(Request $request)
    {
        $request->validate([
            'document_number' => 'required|string',
            'title'           => 'required|string',
            'kategori'        => 'required|string',
            'department_id'   => 'required|integer',
            'file'            => 'required|mimes:pdf|max:51200'
        ]);

        // Save file to new path
        $path = $request->file('file')->store('docs_storage', 'public');

        Document::create([
            'document_number' => $request->document_number,
            'title'           => $request->title,
            'kategori'        => $request->kategori,
            'department_id'   => $request->department_id,
            'file_path'       => $path,
            'created_by'      => Auth::id(),
        ]);

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen berhasil dibuat.');
    }

    /**
     * EDIT (ADMIN)
     */
    public function edit($id)
    {
        $document = Document::findOrFail($id);
        return view('documents.edit', compact('document'));
    }

    /**
     * UPDATE (ADMIN)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'document_number' => 'required|string',
            'title'           => 'required|string',
            'kategori'        => 'required|string',
            'department_id'   => 'required|integer',
            'file'            => 'nullable|mimes:pdf|max:51200',
        ]);

        $document = Document::findOrFail($id);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('docs_storage', 'public');
            $document->file_path = $path;
        }

        $document->update([
            'document_number' => $request->document_number,
            'title'           => $request->title,
            'kategori'        => $request->kategori,
            'department_id'   => $request->department_id,
        ]);

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen berhasil diperbarui.');
    }

    /**
     * DELETE
     */
    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen berhasil dihapus.');
    }
}
