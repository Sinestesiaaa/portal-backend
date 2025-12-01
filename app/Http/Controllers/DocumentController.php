<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    /**
     * LISTING DOKUMEN
     */
    public function index()
    {
        $user = Auth::user();

        // ADMIN (1) & SUPERUSER (2) → semua dokumen
        if (in_array($user->role_id, [1, 2])) {
            $documents = Document::with('department', 'creator')
                ->latest()
                ->get();
        }
        // USER (3) → hanya dokumen departemen sendiri
        else {
            $documents = Document::with('department', 'creator')
                ->where('department_id', $user->department_id)
                ->latest()
                ->get();
        }

        return view('documents.index', compact('documents'));
    }


    /**
     * DETAIL DOKUMEN (SHOW)
     * Semua role bisa melihat detail
     */
    public function show($id)
    {
        $document = Document::with('department', 'creator')->findOrFail($id);
        return view('documents.show', compact('document'));
    }


    /**
     * FORM CREATE (ADMIN ONLY)
     */
    public function create()
    {
        $this->ensureAdmin();

        // Ambil semua departemen
        $departments = \App\Models\Department::all();

        return view('documents.create', compact('departments'));
    }



    /**
     * SIMPAN DATA (ADMIN ONLY)
     */
    public function store(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'document_number' => 'required|string',
            'title'           => 'required|string',
            'kategori'        => 'required|string',
            'department_id'   => 'required|integer',
            'file'            => 'required|mimes:pdf|max:51200',
        ]);

        // Upload file
        $path = $request->file('file')->store('documents', 'public');

        Document::create([
            'document_number' => $request->document_number,
            'title'           => $request->title,
            'kategori'        => $request->kategori,
            'department_id'   => $request->department_id,
            'file_path'       => $path,
            'created_by'      => Auth::id(),  // 100% aman
        ]);

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen berhasil dibuat.');
    }


    /**
     * FORM EDIT (ADMIN ONLY)
     */
    public function edit($id)
    {
        $this->ensureAdmin();
        $document = Document::findOrFail($id);

        return view('documents.edit', compact('document'));
    }


    /**
     * UPDATE DOKUMEN (ADMIN ONLY)
     */
    public function update(Request $request, $id)
    {
        $this->ensureAdmin();
        $document = Document::findOrFail($id);

        $request->validate([
            'document_number' => 'required|string',
            'title'           => 'required|string',
            'kategori'        => 'required|string',
            'department_id'   => 'required|integer',
            'file'            => 'nullable|mimes:pdf|max:51200',
        ]);

        // Update file jika diupload
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('documents', 'public');
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
     * HAPUS DOKUMEN (ADMIN ONLY)
     */
    public function destroy($id)
    {
        $this->ensureAdmin();

        $document = Document::findOrFail($id);
        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen berhasil dihapus.');
    }


    /**
     * VALIDASI ADMIN
     */
    private function ensureAdmin()
    {
        if (!Auth::check() || Auth::user()->role_id !== 1) {
            abort(403, 'Akses ditolak. Hanya Admin yang boleh melakukan tindakan ini.');
        }
    }
}
