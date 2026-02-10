<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentAudit;
use App\Models\Department;
use App\Models\DocumentRevision;
use App\Models\DocumentType;
use App\Models\Site;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    /**
     * LIST DOKUMEN
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $documents = Document::with('department', 'site', 'creator');
        $generalDeptId = Department::where('name', 'GENERAL')->value('id');
        $documentTypes = DocumentType::orderBy('sort_order')->orderBy('name')->get();

        // Restriksi user biasa
        if ($user->role_id == 3) {
            $documents->where(function ($q) use ($user, $generalDeptId) {
                $q->where('department_id', $user->department_id);
                if ($generalDeptId) {
                    $q->orWhere('department_id', $generalDeptId);
                }
            });
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

        // Filter tanggal terbit (range)
        if ($request->published_start && $request->published_end) {
            $documents->whereBetween('published_at', [$request->published_start, $request->published_end]);
        } elseif ($request->published_start) {
            $documents->whereDate('published_at', '>=', $request->published_start);
        } elseif ($request->published_end) {
            $documents->whereDate('published_at', '<=', $request->published_end);
        }


        // Filter departemen untuk admin/super user
        if (in_array($user->role_id, [1, 2]) && $request->department_id) {
            $documents->where('department_id', $request->department_id);
        }
        if ($request->site_id) {
            $documents->where('site_id', $request->site_id);
        }

        $perPage = 50;
        $sort = $request->query('sort');
        $order = strtolower($request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['title', 'published_at', 'revision_number'];

        if ($sort && in_array($sort, $allowedSorts)) {
            $documents = $documents->orderBy($sort, $order)->paginate($perPage)->appends($request->all());
            $total = $documents->total();
        } else {
            // Ambil semua untuk sorting manual
            $orderKategori = $documentTypes->pluck('name')->toArray();
            $documents = $documents->get()->sort(function ($a, $b) use ($orderKategori) {

                // 1. Departemen A-Z
                $deptA = $a->department->name ?? '';
                $deptB = $b->department->name ?? '';
                if ($deptA !== $deptB) return strcmp($deptA, $deptB);

                // 2. Kategori urutan custom
                $katA = array_search($a->kategori, $orderKategori);
                $katB = array_search($b->kategori, $orderKategori);
                if ($katA === false) $katA = PHP_INT_MAX;
                if ($katB === false) $katB = PHP_INT_MAX;
                if ($katA !== $katB) return $katA <=> $katB;

                // 3. Nomor dokumen (angka terakhir)
                return extractDocNumber($a->document_number) <=> extractDocNumber($b->document_number);
            });

            // PAGINATION MANUAL
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
        }

        return view('documents.index', [
            'documents'   => $documents,
            'departments' => Department::all(),
            'sites' => Site::where('is_active', true)->orderBy('name')->get(),
            'documentTypes' => $documentTypes,
            'totalResult' => $total,
        ]);
    }

    /**
     * EXPORT PAGE
     */
    public function exportPage(Request $request)
    {
        Gate::authorize('document.manage');

        $user = Auth::user();
        $documents = Document::with('department', 'site', 'creator');
        $generalDeptId = Department::where('name', 'GENERAL')->value('id');
        $documentTypes = DocumentType::orderBy('sort_order')->orderBy('name')->get();

        if ($user->role_id == 3) {
            $documents->where(function ($q) use ($user, $generalDeptId) {
                $q->where('department_id', $user->department_id);
                if ($generalDeptId) {
                    $q->orWhere('department_id', $generalDeptId);
                }
            });
        }

        if ($request->search) {
            $documents->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                    ->orWhere('document_number', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->kategori) {
            $documents->where('kategori', $request->kategori);
        }

        if ($request->published_start && $request->published_end) {
            $documents->whereBetween('published_at', [$request->published_start, $request->published_end]);
        } elseif ($request->published_start) {
            $documents->whereDate('published_at', '>=', $request->published_start);
        } elseif ($request->published_end) {
            $documents->whereDate('published_at', '<=', $request->published_end);
        }

        if (in_array($user->role_id, [1, 2]) && $request->department_id) {
            $documents->where('department_id', $request->department_id);
        }
        if ($request->site_id) {
            $documents->where('site_id', $request->site_id);
        }

        $perPage = 50;
        $sort = $request->query('sort');
        $order = strtolower($request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['title', 'published_at', 'revision_number'];

        if ($sort && in_array($sort, $allowedSorts)) {
            $documents = $documents->orderBy($sort, $order)->paginate($perPage)->appends($request->all());
            $total = $documents->total();
        } else {
            $orderKategori = $documentTypes->pluck('name')->toArray();
            $documents = $documents->get()->sort(function ($a, $b) use ($orderKategori) {
                $deptA = $a->department->name ?? '';
                $deptB = $b->department->name ?? '';
                if ($deptA !== $deptB) return strcmp($deptA, $deptB);
                $katA = array_search($a->kategori, $orderKategori);
                $katB = array_search($b->kategori, $orderKategori);
                if ($katA === false) $katA = PHP_INT_MAX;
                if ($katB === false) $katB = PHP_INT_MAX;
                if ($katA !== $katB) return $katA <=> $katB;
                return extractDocNumber($a->document_number) <=> extractDocNumber($b->document_number);
            });

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
        }

        return view('documents.export_page', [
            'documents' => $documents,
            'departments' => Department::all(),
            'sites' => Site::where('is_active', true)->orderBy('name')->get(),
            'documentTypes' => $documentTypes,
            'totalResult' => $total,
        ]);
    }

    /**
     * EXPORT LIST DOKUMEN (CSV)
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $documents = Document::with('department', 'site');
        $generalDeptId = Department::where('name', 'GENERAL')->value('id');
        $documentTypes = DocumentType::orderBy('sort_order')->orderBy('name')->get();
        $allowedColumns = [
            'department',
            'site',
            'kategori',
            'document_number',
            'title',
            'revision_number',
            'last_revision_at',
            'published_at',
            'review_date',
            'file_path',
        ];
        $columns = $request->input('columns', []);
        if (!is_array($columns) || count($columns) === 0) {
            $columns = $allowedColumns;
        } else {
            $columns = array_values(array_intersect($columns, $allowedColumns));
            if (count($columns) === 0) {
                $columns = $allowedColumns;
            }
        }

        if ($user->role_id == 3) {
            $documents->where(function ($q) use ($user, $generalDeptId) {
                $q->where('department_id', $user->department_id);
                if ($generalDeptId) {
                    $q->orWhere('department_id', $generalDeptId);
                }
            });
        }

        if ($request->search) {
            $documents->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                    ->orWhere('document_number', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->kategori) {
            $documents->where('kategori', $request->kategori);
        }

        if ($request->published_start && $request->published_end) {
            $documents->whereBetween('published_at', [$request->published_start, $request->published_end]);
        } elseif ($request->published_start) {
            $documents->whereDate('published_at', '>=', $request->published_start);
        } elseif ($request->published_end) {
            $documents->whereDate('published_at', '<=', $request->published_end);
        }

        if (in_array($user->role_id, [1, 2]) && $request->department_id) {
            $documents->where('department_id', $request->department_id);
        }
        if ($request->site_id) {
            $documents->where('site_id', $request->site_id);
        }

        $sort = $request->query('sort');
        $order = strtolower($request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['title', 'published_at', 'revision_number'];

        if ($sort && in_array($sort, $allowedSorts)) {
            $documents = $documents->orderBy($sort, $order)->get();
        } else {
            $orderKategori = $documentTypes->pluck('name')->toArray();
            $documents = $documents->get()->sort(function ($a, $b) use ($orderKategori) {
                $deptA = $a->department->name ?? '';
                $deptB = $b->department->name ?? '';
                if ($deptA !== $deptB) return strcmp($deptA, $deptB);
                $katA = array_search($a->kategori, $orderKategori);
                $katB = array_search($b->kategori, $orderKategori);
                if ($katA === false) $katA = PHP_INT_MAX;
                if ($katB === false) $katB = PHP_INT_MAX;
                if ($katA !== $katB) return $katA <=> $katB;
                return extractDocNumber($a->document_number) <=> extractDocNumber($b->document_number);
            });
        }

        $filename = 'dokumen-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($documents, $columns) {
            $out = fopen('php://output', 'w');
            $labels = [
                'department' => 'Departemen',
                'site' => 'Site',
                'kategori' => 'Kategori',
                'document_number' => 'Nomor Dokumen',
                'title' => 'Judul',
                'revision_number' => 'Revisi',
                'last_revision_at' => 'Tanggal Revisi Terakhir',
                'published_at' => 'Tanggal Terbit',
                'review_date' => 'Review Berikutnya',
                'file_path' => 'File',
            ];
            fputcsv($out, array_map(fn($c) => $labels[$c] ?? $c, $columns));

            foreach ($documents as $doc) {
                $row = [];
                foreach ($columns as $c) {
                    if ($c === 'department') $row[] = $doc->department->name ?? '-';
                    if ($c === 'site') $row[] = $doc->site->name ?? '-';
                    if ($c === 'kategori') $row[] = $doc->kategori;
                    if ($c === 'document_number') $row[] = $doc->document_number;
                    if ($c === 'title') $row[] = $doc->title;
                    if ($c === 'revision_number') $row[] = $doc->revision_number ?? 0;
                    if ($c === 'last_revision_at') $row[] = $doc->last_revision_at?->format('Y-m-d') ?? '';
                    if ($c === 'published_at') $row[] = $doc->published_at?->format('Y-m-d') ?? '';
                    if ($c === 'review_date') $row[] = $doc->review_date?->format('Y-m-d') ?? '';
                    if ($c === 'file_path') $row[] = $doc->file_path;
                }
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename);
    }

    /**
     * EXPORT LIST DOKUMEN TERPILIH (CSV)
     */
    public function exportSelected(Request $request)
    {
        Gate::authorize('document.manage');
        $user = Auth::user();
        $ids = $request->input('ids', []);
        $allowedColumns = [
            'department',
            'site',
            'kategori',
            'document_number',
            'title',
            'revision_number',
            'last_revision_at',
            'published_at',
            'review_date',
            'file_path',
        ];
        $columns = $request->input('columns', []);
        if (!is_array($columns) || count($columns) === 0) {
            $columns = $allowedColumns;
        } else {
            $columns = array_values(array_intersect($columns, $allowedColumns));
            if (count($columns) === 0) {
                $columns = $allowedColumns;
            }
        }

        if (!is_array($ids) || count($ids) === 0) {
            return back()->with('error', 'Pilih minimal 1 dokumen.');
        }

        $docs = Document::with('department', 'site')->whereIn('id', $ids);
        $generalDeptId = Department::where('name', 'GENERAL')->value('id');

        if ($user->role_id == 3) {
            $docs->where(function ($q) use ($user, $generalDeptId) {
                $q->where('department_id', $user->department_id);
                if ($generalDeptId) {
                    $q->orWhere('department_id', $generalDeptId);
                }
            });
        }

        $documents = $docs->get();
        $filename = 'dokumen-terpilih-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($documents, $columns) {
            $out = fopen('php://output', 'w');
            $labels = [
                'department' => 'Departemen',
                'site' => 'Site',
                'kategori' => 'Kategori',
                'document_number' => 'Nomor Dokumen',
                'title' => 'Judul',
                'revision_number' => 'Revisi',
                'last_revision_at' => 'Tanggal Revisi Terakhir',
                'published_at' => 'Tanggal Terbit',
                'review_date' => 'Review Berikutnya',
                'file_path' => 'File',
            ];
            fputcsv($out, array_map(fn($c) => $labels[$c] ?? $c, $columns));

            foreach ($documents as $doc) {
                $row = [];
                foreach ($columns as $c) {
                    if ($c === 'department') $row[] = $doc->department->name ?? '-';
                    if ($c === 'site') $row[] = $doc->site->name ?? '-';
                    if ($c === 'kategori') $row[] = $doc->kategori;
                    if ($c === 'document_number') $row[] = $doc->document_number;
                    if ($c === 'title') $row[] = $doc->title;
                    if ($c === 'revision_number') $row[] = $doc->revision_number ?? 0;
                    if ($c === 'last_revision_at') $row[] = $doc->last_revision_at?->format('Y-m-d') ?? '';
                    if ($c === 'published_at') $row[] = $doc->published_at?->format('Y-m-d') ?? '';
                    if ($c === 'review_date') $row[] = $doc->review_date?->format('Y-m-d') ?? '';
                    if ($c === 'file_path') $row[] = $doc->file_path;
                }
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename);
    }

    /**
     * EXPORT LIST DOKUMEN (PDF)
     */
    public function exportPdf(Request $request)
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return back()->with('error', 'PDF export belum tersedia. Jalankan composer require barryvdh/laravel-dompdf.');
        }

        $user = Auth::user();
        $documents = Document::with('department', 'site');
        $generalDeptId = Department::where('name', 'GENERAL')->value('id');
        $documentTypes = DocumentType::orderBy('sort_order')->orderBy('name')->get();
        $allowedColumns = [
            'department',
            'site',
            'kategori',
            'document_number',
            'title',
            'revision_number',
            'last_revision_at',
            'published_at',
            'review_date',
            'file_path',
        ];
        $columns = $request->input('columns', []);
        if (!is_array($columns) || count($columns) === 0) {
            $columns = $allowedColumns;
        } else {
            $columns = array_values(array_intersect($columns, $allowedColumns));
            if (count($columns) === 0) {
                $columns = $allowedColumns;
            }
        }

        if ($user->role_id == 3) {
            $documents->where(function ($q) use ($user, $generalDeptId) {
                $q->where('department_id', $user->department_id);
                if ($generalDeptId) {
                    $q->orWhere('department_id', $generalDeptId);
                }
            });
        }

        if ($request->search) {
            $documents->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                    ->orWhere('document_number', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->kategori) {
            $documents->where('kategori', $request->kategori);
        }

        if ($request->published_start && $request->published_end) {
            $documents->whereBetween('published_at', [$request->published_start, $request->published_end]);
        } elseif ($request->published_start) {
            $documents->whereDate('published_at', '>=', $request->published_start);
        } elseif ($request->published_end) {
            $documents->whereDate('published_at', '<=', $request->published_end);
        }

        if (in_array($user->role_id, [1, 2]) && $request->department_id) {
            $documents->where('department_id', $request->department_id);
        }
        if ($request->site_id) {
            $documents->where('site_id', $request->site_id);
        }

        $sort = $request->query('sort');
        $order = strtolower($request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['title', 'published_at', 'revision_number'];

        if ($sort && in_array($sort, $allowedSorts)) {
            $documents = $documents->orderBy($sort, $order)->get();
        } else {
            $orderKategori = $documentTypes->pluck('name')->toArray();
            $documents = $documents->get()->sort(function ($a, $b) use ($orderKategori) {
                $deptA = $a->department->name ?? '';
                $deptB = $b->department->name ?? '';
                if ($deptA !== $deptB) return strcmp($deptA, $deptB);
                $katA = array_search($a->kategori, $orderKategori);
                $katB = array_search($b->kategori, $orderKategori);
                if ($katA === false) $katA = PHP_INT_MAX;
                if ($katB === false) $katB = PHP_INT_MAX;
                if ($katA !== $katB) return $katA <=> $katB;
                return extractDocNumber($a->document_number) <=> extractDocNumber($b->document_number);
            });
        }

        $pdf = Pdf::loadView('documents.export', [
            'documents' => $documents,
            'generatedAt' => now()->format('Y-m-d H:i'),
            'columns' => $columns,
        ])->setPaper('a4', 'landscape');

        $filename = 'dokumen-' . now()->format('Ymd-His') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * AUTOCOMPLETE SEARCH
     */
    public function autocomplete(Request $request)
    {
        $user = Auth::user();
        $q = trim((string) $request->query('q', ''));
        $generalDeptId = Department::where('name', 'GENERAL')->value('id');

        if ($q === '') {
            return response()->json([]);
        }

        $query = Document::with('department', 'site')
            ->select('id', 'document_number', 'title', 'department_id', 'site_id')
            ->where(function ($q2) use ($q) {
                $q2->where('title', 'LIKE', "%{$q}%")
                    ->orWhere('document_number', 'LIKE', "%{$q}%");
            })
            ->orderBy('document_number')
            ->limit(10);

        if ($user->role_id == 3) {
            $query->where(function ($q2) use ($user, $generalDeptId) {
                $q2->where('department_id', $user->department_id);
                if ($generalDeptId) {
                    $q2->orWhere('department_id', $generalDeptId);
                }
            });
        }

        $results = $query->get()->map(function ($doc) {
            return [
                'id' => $doc->id,
                'label' => $doc->document_number . ' — ' . $doc->title,
                'document_number' => $doc->document_number,
                'title' => $doc->title,
                'department' => $doc->department->name ?? '-',
                'site' => $doc->site->name ?? '-',
            ];
        });

        return response()->json($results);
    }

    /**
     * PREVIEW PDF TANPA WHITE SCREEN
     */
    public function preview($id)
    {
        $doc = Document::findOrFail($id);
        if (!Gate::allows('document.view', $doc)) {
            abort(403);
        }
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
        Gate::authorize('document.manage');
        return view('documents.create', [
            'departments' => Department::all(),
            'sites' => Site::where('is_active', true)->orderBy('name')->get(),
            'documentTypes' => DocumentType::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('document.manage');
        $request->validate([
            'document_number'   => 'required',
            'title'             => 'required',
            'kategori'          => ['required', Rule::exists('document_types', 'name')],
            'department_id'     => ['required', Rule::exists('departments', 'id')],
            'site_id'           => ['nullable', Rule::exists('sites', 'id')],
            'published_at'      => 'required|date',
            'review_date'       => 'nullable|date',
            'file'              => 'required|mimes:pdf,xls,xlsx,doc,docx|max:51200',
            'form_description'  => 'nullable|mimes:pdf|max:51200',
        ]);

        // Enforce file type by kategori
        if ($request->kategori === 'FORM') {
            $request->validate([
                'file' => 'required|mimes:xls,xlsx,doc,docx|max:51200',
            ]);
        } else {
            $request->validate([
                'file' => 'required|mimes:pdf|max:51200',
            ]);
        }

        $path = $request->file('file')->store('docs_storage', 'public');
        $descPath = null;
        if ($request->kategori === 'FORM' && $request->file('form_description')) {
            $descPath = $request->file('form_description')->store('docs_storage', 'public');
        }

        $created = Document::create([
            'document_number' => $request->document_number,
            'title'           => $request->title,
            'kategori'        => $request->kategori,
            'department_id'   => $request->department_id,
            'site_id'         => $request->site_id,
            'published_at'    => $request->published_at,
            'review_date'     => $request->review_date,
            'file_path'       => $path,
            'form_description_path' => $descPath,
            'created_by'      => Auth::id(),
        ]);

        DocumentAudit::create([
            'document_id' => $created->id,
            'user_id' => Auth::id(),
            'action' => 'create',
            'meta' => [
                'document_number' => $created->document_number,
                'title' => $created->title,
                'kategori' => $created->kategori,
            ],
        ]);

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil dibuat.');
    }

    public function edit($id)
    {
        Gate::authorize('document.manage');
        return view('documents.edit', [
            'document'    => Document::findOrFail($id),
            'departments' => Department::all(),
            'sites' => Site::orderBy('name')->get(),
            'documentTypes' => DocumentType::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('document.manage');
        $request->validate([
            'document_number'   => 'required',
            'title'             => 'required',
            'kategori'          => ['required', Rule::exists('document_types', 'name')],
            'department_id'     => ['required', Rule::exists('departments', 'id')],
            'site_id'           => ['nullable', Rule::exists('sites', 'id')],
            'published_at'      => 'required|date',
            'review_date'       => 'nullable|date',
            'file'              => 'nullable|mimes:pdf,xls,xlsx,doc,docx|max:51200',
            'form_description'  => 'nullable|mimes:pdf|max:51200',
            'revision_note'     => 'nullable|string',
            'revision_number'   => 'nullable|integer|min:0',
            'is_revision'       => 'nullable|in:1',
            'remove_form_description' => 'nullable|in:1',
        ]);

        $doc = Document::findOrFail($id);

        // Enforce file type by kategori
        if ($request->kategori === 'FORM') {
            $request->validate([
                'file' => 'nullable|mimes:xls,xlsx,doc,docx|max:51200',
            ]);
        } else {
            $request->validate([
                'file' => 'nullable|mimes:pdf|max:51200',
            ]);
        }

        // If marking as revision, require file and note
        if ($request->is_revision) {
            $request->validate([
                'file'          => 'required',
                'revision_note' => 'required|string',
                'revision_number' => 'required|integer|min:0',
            ]);
        }

        if ($request->file('file')) {
            if ($request->is_revision) {
                $doc->revision_number = (int) $request->revision_number;
                $doc->revision_note = $request->revision_note;
                $doc->last_revision_at = now();
            }
            $doc->file_path = $request->file('file')->store('docs_storage', 'public');
        }

        if ($request->kategori === 'FORM' && $request->file('form_description')) {
            $doc->form_description_path = $request->file('form_description')->store('docs_storage', 'public');
        }

        if ($request->kategori === 'FORM' && $request->remove_form_description) {
            if ($doc->form_description_path) {
                Storage::disk('public')->delete($doc->form_description_path);
            }
            $doc->form_description_path = null;
        }

        if ($request->kategori !== 'FORM') {
            $doc->form_description_path = null;
        }

        if ($request->is_revision) {
            DocumentRevision::create([
                'document_id' => $doc->id,
                'revision_number' => (int) $request->revision_number,
                'revision_note' => $request->revision_note,
                'revised_by' => Auth::id(),
                'revised_at' => now(),
            ]);
        }

        $doc->update([
            'document_number' => $request->document_number,
            'title'           => $request->title,
            'kategori'        => $request->kategori,
            'department_id'   => $request->department_id,
            'site_id'         => $request->site_id,
            'published_at'    => $request->published_at,
            'review_date'     => $request->review_date,
        ]);

        $auditAction = $request->is_revision ? 'revision' : 'update';
        $meta = [
            'document_number' => $doc->document_number,
            'title' => $doc->title,
            'kategori' => $doc->kategori,
        ];
        if ($request->is_revision) {
            $meta['revision_number'] = (int) $request->revision_number;
            $meta['revision_note'] = $request->revision_note;
        }

        DocumentAudit::create([
            'document_id' => $doc->id,
            'user_id' => Auth::id(),
            'action' => $auditAction,
            'meta' => $meta,
        ]);

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Gate::authorize('document.manage');
        $doc = Document::findOrFail($id);
        DocumentAudit::create([
            'document_id' => $doc->id,
            'user_id' => Auth::id(),
            'action' => 'delete',
            'meta' => [
                'document_number' => $doc->document_number,
                'title' => $doc->title,
                'kategori' => $doc->kategori,
            ],
        ]);
        $doc->delete();
        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil dihapus.');
    }

    public function show($id)
    {
        $document = Document::with('department', 'site', 'creator', 'relatedDocuments')->findOrFail($id);
        if (!Gate::allows('document.view', $document)) {
            abort(403);
        }
        $relatedIds = $document->relatedDocuments->pluck('id')->toArray();

        $revisions = DocumentRevision::with('revisedBy')
            ->where('document_id', $document->id)
            ->orderBy('revision_number', 'desc')
            ->paginate(10);

        $audits = DocumentAudit::with('user')
            ->where('document_id', $document->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $candidates = Document::with('department')
            ->where('id', '!=', $document->id)
            ->orderBy('document_number')
            ->get();

        return view('documents.show', [
            'document' => $document,
            'relatedIds' => $relatedIds,
            'candidates' => $candidates,
            'revisions' => $revisions,
            'audits' => $audits,
        ]);
    }

    /**
     * LIST DOKUMEN PERLU REVIEW
     */
    public function reviewList(Request $request)
    {
        $user = Auth::user();
        $documents = Document::with('department', 'site', 'creator');
        $generalDeptId = Department::where('name', 'GENERAL')->value('id');
        $documentTypes = DocumentType::orderBy('sort_order')->orderBy('name')->get();

        if ($user->role_id == 3) {
            $documents->where(function ($q) use ($user, $generalDeptId) {
                $q->where('department_id', $user->department_id);
                if ($generalDeptId) {
                    $q->orWhere('department_id', $generalDeptId);
                }
            });
        }

        $documents->whereNotNull('review_date');

        if ($request->search) {
            $documents->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                    ->orWhere('document_number', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->kategori) {
            $documents->where('kategori', $request->kategori);
        }

        if (in_array($user->role_id, [1, 2]) && $request->department_id) {
            $documents->where('department_id', $request->department_id);
        }
        if ($request->site_id) {
            $documents->where('site_id', $request->site_id);
        }

        if ($request->review_start && $request->review_end) {
            $documents->whereBetween('review_date', [$request->review_start, $request->review_end]);
        } elseif ($request->review_start) {
            $documents->whereDate('review_date', '>=', $request->review_start);
        } elseif ($request->review_end) {
            $documents->whereDate('review_date', '<=', $request->review_end);
        } else {
            // Default window: tampilkan dokumen yang jatuh tempo dalam 30 hari ke depan (termasuk overdue).
            $documents->whereDate('review_date', '<=', now()->addDays(30)->toDateString());
        }

        $perPage = 50;
        $sort = $request->query('sort');
        $order = strtolower($request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['title', 'published_at', 'review_date', 'revision_number'];

        if ($sort && in_array($sort, $allowedSorts)) {
            $documents = $documents->orderBy($sort, $order)->paginate($perPage)->appends($request->all());
            $total = $documents->total();
        } else {
            $documents = $documents->orderBy('review_date')->paginate($perPage)->appends($request->all());
            $total = $documents->total();
        }

        return view('documents.review', [
            'documents'   => $documents,
            'departments' => Department::all(),
            'sites' => Site::where('is_active', true)->orderBy('name')->get(),
            'documentTypes' => $documentTypes,
            'totalResult' => $total,
        ]);
    }

    /**
     * MONITORING NOMOR DOKUMEN (RINGKAS)
     */
    public function numberList(Request $request)
    {
        $user = Auth::user();
        $documents = Document::with('department', 'site');
        $generalDeptId = Department::where('name', 'GENERAL')->value('id');
        $documentTypes = DocumentType::orderBy('sort_order')->orderBy('name')->get();

        if ($user->role_id == 3) {
            $documents->where(function ($q) use ($user, $generalDeptId) {
                $q->where('department_id', $user->department_id);
                if ($generalDeptId) {
                    $q->orWhere('department_id', $generalDeptId);
                }
            });
        }

        if ($request->search) {
            $documents->where(function ($q) use ($request) {
                $q->where('document_number', 'LIKE', "%{$request->search}%")
                    ->orWhere('title', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->kategori) {
            $documents->where('kategori', $request->kategori);
        }

        if (in_array($user->role_id, [1, 2]) && $request->department_id) {
            $documents->where('department_id', $request->department_id);
        }

        if ($request->site_id) {
            $documents->where('site_id', $request->site_id);
        }

        $documents = $documents
            ->orderBy('document_number', 'asc')
            ->paginate(50)
            ->appends($request->all());

        return view('documents.numbers', [
            'documents' => $documents,
            'documentTypes' => $documentTypes,
            'departments' => Department::all(),
            'sites' => Site::where('is_active', true)->orderBy('name')->get(),
            'totalResult' => $documents->total(),
        ]);
    }

    public function updateRelated(Request $request, $id)
    {
        Gate::authorize('document.manage');
        $document = Document::findOrFail($id);

        $request->validate([
            'related_ids' => 'nullable|array',
            'related_ids.*' => [
                'integer',
                Rule::exists('documents', 'id')->whereNot('id', $document->id),
            ],
        ]);

        $ids = $request->input('related_ids', []);

        $currentIds = $document->relatedDocuments()->pluck('documents.id')->toArray();
        $toAdd = array_values(array_diff($ids, $currentIds));
        $toRemove = array_values(array_diff($currentIds, $ids));

        $document->relatedDocuments()->sync($ids);

        foreach ($toAdd as $relatedId) {
            $related = Document::find($relatedId);
            if ($related) {
                $related->relatedDocuments()->syncWithoutDetaching([$document->id]);
            }
        }

        foreach ($toRemove as $relatedId) {
            $related = Document::find($relatedId);
            if ($related) {
                $related->relatedDocuments()->detach($document->id);
            }
        }

        return redirect()
            ->route('documents.show', $document->id)
            ->with('success', 'Dokumen terkait berhasil diperbarui.');
    }

    public function deleteRelated($id, $relatedId)
    {
        Gate::authorize('document.manage');
        $document = Document::findOrFail($id);
        $document->relatedDocuments()->detach($relatedId);

        $related = Document::find($relatedId);
        if ($related) {
            $related->relatedDocuments()->detach($document->id);
        }

        return redirect()
            ->route('documents.show', $document->id)
            ->with('success', 'Relasi dokumen berhasil dihapus.');
    }

    /**
     * PREVIEW PDF PENJELASAN FORM
     */
    public function previewDescription($id)
    {
        $doc = Document::findOrFail($id);
        if (!Gate::allows('document.view', $doc)) {
            abort(403);
        }
        $path = $doc->form_description_path
            ? storage_path('app/public/' . $doc->form_description_path)
            : null;

        if (!$path || !file_exists($path)) abort(404, 'File tidak ditemukan');

        while (ob_get_level()) ob_end_clean();

        header("Content-Type: application/pdf");
        header("Content-Length: " . filesize($path));
        header("Content-Disposition: inline; filename=\"" . basename($path) . "\"");

        readfile($path);
        exit;
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
