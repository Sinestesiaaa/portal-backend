<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DocumentTypeController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('document-type.manage');
        $query = DocumentType::query();

        if ($request->search) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $types = $query->orderBy('sort_order')->orderBy('name')->paginate(20)->appends($request->all());

        return view('admin.document-types.index', [
            'types' => $types,
        ]);
    }

    public function create()
    {
        Gate::authorize('document-type.manage');
        return view('admin.document-types.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('document-type.manage');
        $request->validate([
            'name' => 'required|unique:document_types,name',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        DocumentType::create([
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.document-types.index')->with('success', 'Tipe dokumen berhasil dibuat.');
    }

    public function edit($id)
    {
        Gate::authorize('document-type.manage');
        $type = DocumentType::findOrFail($id);
        return view('admin.document-types.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('document-type.manage');
        $type = DocumentType::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:document_types,name,' . $type->id,
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $type->update([
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.document-types.index')->with('success', 'Tipe dokumen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Gate::authorize('document-type.manage');
        $type = DocumentType::findOrFail($id);
        $type->delete();

        return redirect()->route('admin.document-types.index')->with('success', 'Tipe dokumen berhasil dihapus.');
    }
}
