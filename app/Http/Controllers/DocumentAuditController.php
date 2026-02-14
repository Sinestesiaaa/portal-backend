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

        $audits = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->all());

        return view('admin.audits.index', [
            'audits' => $audits,
        ]);
    }
}
