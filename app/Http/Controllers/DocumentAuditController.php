<?php

namespace App\Http\Controllers;

use App\Models\DocumentAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DocumentAuditController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('document.manage');

        $query = DocumentAudit::with(['user', 'document']);
        $allowedPerPages = ['20', '50', '100', '200', 'all'];
        $perPageRaw = (string) $request->query('per_page', '20');
        if (!in_array($perPageRaw, $allowedPerPages, true)) {
            $perPageRaw = '20';
        }
        $perPage = $perPageRaw === 'all' ? max(1, (clone $query)->count()) : (int) $perPageRaw;

        if ($request->action) {
            $query->where('action', $request->action);
        }

        if ($request->search) {
            $search = $request->search;
            $query->whereHas('document', function ($q) use ($search) {
                $q->where('document_number', 'LIKE', '%' . $search . '%')
                    ->orWhere('title', 'LIKE', '%' . $search . '%');
            });
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $audits = $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->all());

        return view('admin.audits.index', [
            'audits' => $audits,
        ]);
    }

    public function edit($id)
    {
        Gate::authorize('document.manage');
        $audit = DocumentAudit::with(['user', 'document'])->findOrFail($id);

        return view('admin.audits.edit', [
            'audit' => $audit,
        ]);
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('document.manage');
        $request->validate([
            'action' => 'required|in:create,update,revision,delete',
            'meta_json' => 'nullable|string',
        ]);

        $meta = null;
        $rawMeta = trim((string) $request->input('meta_json', ''));
        if ($rawMeta !== '') {
            $decoded = json_decode($rawMeta, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                return back()->withErrors(['meta_json' => 'Format JSON tidak valid.'])->withInput();
            }
            $meta = $decoded;
        }

        $audit = DocumentAudit::findOrFail($id);
        $audit->update([
            'action' => $request->action,
            'meta' => $meta,
        ]);

        return redirect()->route('admin.audits.index')->with('success', 'Audit log berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Gate::authorize('document.manage');
        if (!app()->environment('local')) {
            abort(403, 'Aksi ini hanya diizinkan pada environment local.');
        }

        $audit = DocumentAudit::findOrFail($id);
        $audit->delete();

        return back()->with('success', 'Audit log berhasil dihapus.');
    }

    public function destroySelected(Request $request)
    {
        Gate::authorize('document.manage');
        if (!app()->environment('local')) {
            abort(403, 'Aksi ini hanya diizinkan pada environment local.');
        }

        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:document_audits,id',
        ]);

        $ids = array_values(array_unique($request->input('ids', [])));
        $deleted = DocumentAudit::whereIn('id', $ids)->delete();

        return back()->with('success', "Berhasil menghapus {$deleted} audit log.");
    }
}
