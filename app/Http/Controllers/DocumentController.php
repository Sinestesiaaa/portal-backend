<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentController extends Controller
{
    /**
     * LIST DOKUMEN
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $documents = Document::with('department', 'creator');

        // Restriksi user biasa
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

        // Filter tanggal terbit (published_at)
        if ($request->tanggal) {
            $documents->whereDate('published_at', $request->tanggal);
        }

        // Filter departemen
        if (in_array($user->role_id, [1, 2]) && $request->department_id) {
            $documents->where('department_id', $request->department_id);
        }

        // Ambil semua untuk sorting manual
        $documents = $documents->get()->sort(function ($a, $b) {

            // 1. Departemen A-Z
            $deptA = $a->department->name ?? '';
            $deptB = $b->department->name ?? '';
            if ($deptA !== $deptB) return strcmp($deptA, $deptB);

            // 2. Kategori urutan custom
            $orderKategori = ['FORM', 'IK', 'SOP', 'STD'];
            $katA = array_search($a->kategori, $orderKategori);
            $katB = array_search($b->kategori, $orderKategori);
            if ($katA !== $katB) return $katA <=> $katB;

            // 3. Nomor dokumen (angka terakhir)
            return extractDocNumber($a->document_number) <=> extractDocNumber($b->document_number);
        });

        // PAGINATION MANUAL
        $perPage = 50;
        $page = request('page', 1);
        $total = $documents->count();

        $documentsPage = $documents->slice(($page - 1) * $perPage, $perPage)->values();

        $documents = new LengthAwarePaginator(
            $documentsPage,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('documents.index', [
            'documents'   => $documents,
            'departments' => Department::all(),
            'totalResult' => $total,
        ]);
    }


    /**
     * PREVIEW PDF TANPA WHITE SCREEN
     */
    public function preview($id)
    {
        $doc = Document::findOrFail($id);
        $path = storage_path('app/public/' . $doc->file_path);

        if (!file_exists($path)) abort(404, 'File tidak ditemukan');

        while (ob_get_level()) ob_end_clean();

        header("Content-Type: application/pdf");
        header("Content-Length: " . filesize($path));
        header("Content-Disposition: inline; filename=\"" . basename($path) . "\"");

        readfile($path);
        exit;
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
            'title'           => 'required',
            'kategori'        => 'required',
            'department_id'   => 'required',
            'published_at'    => 'required|date',
            'file'            => 'required|mimes:pdf|max:51200',
        ]);

        $path = $request->file('file')->store('docs_storage', 'public');

        Document::create([
            'document_number' => $request->document_number,
            'title'           => $request->title,
            'kategori'        => $request->kategori,
            'department_id'   => $request->department_id,
            'published_at'    => $request->published_at,
            'file_path'       => $path,
            'created_by'      => Auth::id(),
        ]);

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil dibuat.');
    }


    public function edit($id)
    {
        return view('documents.edit', [
            'document'    => Document::findOrFail($id),
            'departments' => Department::all(),
        ]);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'document_number' => 'required',
            'title'           => 'required',
            'kategori'        => 'required',
            'department_id'   => 'required',
            'published_at'    => 'required|date',
            'file'            => 'nullable|mimes:pdf|max:51200',
        ]);

        $doc = Document::findOrFail($id);

        if ($request->file('file')) {
            $doc->file_path = $request->file('file')->store('docs_storage', 'public');
        }

        $doc->update([
            'document_number' => $request->document_number,
            'title'           => $request->title,
            'kategori'        => $request->kategori,
            'department_id'   => $request->department_id,
            'published_at'    => $request->published_at,
        ]);

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil diperbarui.');
    }


    public function destroy($id)
    {
        Document::findOrFail($id)->delete();

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil dihapus.');
    }
}

/**
 * Extract angka terakhir dokumen
 */
function extractDocNumber($number)
{
    if (preg_match('/(\d+)$/', $number, $m)) return intval($m[1]);
    return 9999999;
}
