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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
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

        $allowedPerPages = ['25', '50', '100', '200', 'all'];
        $perPageRaw = (string) $request->query('per_page', '50');
        if (!in_array($perPageRaw, $allowedPerPages, true)) {
            $perPageRaw = '50';
        }
        $isAllDocs = $perPageRaw === 'all';
        $perPage = $isAllDocs ? max(1, (clone $documents)->count()) : (int) $perPageRaw;
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
            $page = $isAllDocs ? 1 : (int) request('page', 1);
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
     * PREVIEW EXPORT TEMPLATE PERUSAHAAN
     */
    public function exportTemplatePreview(Request $request)
    {
        Gate::authorize('document.manage');
        $data = $this->buildTemplateExportData($request);
        $user = Auth::user();

        $departments = Department::query()
            ->when($user->role_id == 3, function ($q) use ($user) {
                $q->whereIn('id', array_filter([$user->department_id, Department::where('name', 'GENERAL')->value('id')]));
            })
            ->orderBy('name')
            ->get();

        $sites = Site::where('is_active', true)->orderBy('name')->get();

        return view('documents.export_template_preview', array_merge($data, [
            'departments' => $departments,
            'sites' => $sites,
        ]));
    }

    /**
     * EXPORT TEMPLATE PERUSAHAAN (PDF)
     */
    public function exportTemplatePdf(Request $request)
    {
        Gate::authorize('document.manage');
        $data = $this->buildTemplateExportData($request);
        $headerDocNo = (string) ($data['meta']['header_doc_no'] ?? '');

        $pdf = Pdf::loadView('documents.export_template_pdf', $data)
            ->setPaper('a4', 'landscape');

        $filename = $this->makeDownloadName(
            $headerDocNo . ' - Daftar Induk Dokumen - ' . now()->format('Y-m-d'),
            'pdf'
        );

        return $pdf->download($filename);
    }

    /**
     * EXPORT GABUNGAN DASHBOARD + DAFTAR INDUK DOKUMEN (PDF)
     */
    public function exportDashboardTemplatePdf(Request $request)
    {
        Gate::authorize('document.manage');
        $user = Auth::user();
        $templateData = $this->buildTemplateExportData($request);

        $docQuery = Document::with('department');
        if ($user->role_id == 3) {
            $docQuery->where('department_id', $user->department_id);
        }

        $dashboardCategoryCount = (clone $docQuery)
            ->select('kategori', DB::raw('COUNT(*) as total'))
            ->groupBy('kategori')
            ->orderBy('kategori')
            ->pluck('total', 'kategori');

        $dashboardTopDepartments = (clone $docQuery)
            ->select('department_id', DB::raw('COUNT(*) as total'))
            ->with('department')
            ->groupBy('department_id')
            ->orderBy('total', 'desc')
            ->get();
        $deptCategoryCounts = (clone $docQuery)
            ->select('department_id', 'kategori', DB::raw('COUNT(*) as total'))
            ->with('department')
            ->groupBy('department_id', 'kategori')
            ->get();

        $dashboardLatestUploaded = (clone $docQuery)
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        $deptCategoryData = [];
        foreach ($deptCategoryCounts as $row) {
            $deptName = $row->department->name ?? 'Unknown';
            if (!isset($deptCategoryData[$deptName])) {
                $deptCategoryData[$deptName] = ['SOP' => 0, 'IK' => 0, 'FORM' => 0, 'STD' => 0];
            }
            $deptCategoryData[$deptName][$row->kategori] = (int) $row->total;
        }

        $topDepartmentNames = $dashboardTopDepartments->take(6)->map(fn($d) => $d->department->name ?? 'Unknown')->toArray();
        $maxDeptScale = 1;
        foreach ($topDepartmentNames as $deptName) {
            if (!isset($deptCategoryData[$deptName])) {
                continue;
            }
            $maxDeptScale = max($maxDeptScale, max($deptCategoryData[$deptName]));
        }
        $deptCategoryCharts = [];
        foreach ($topDepartmentNames as $deptName) {
            if (!isset($deptCategoryData[$deptName])) {
                continue;
            }
            $deptCategoryCharts[] = [
                'name' => $deptName,
                'uri' => $this->makeDeptCategoryChartDataUri($deptName, $deptCategoryData[$deptName], $maxDeptScale),
            ];
        }

        $categoryPieUri = $this->makePieChartDataUri(
            $dashboardCategoryCount->keys()->toArray(),
            $dashboardCategoryCount->values()->toArray(),
            'Komposisi Kategori'
        );
        $departmentBarUri = $this->makeBarChartDataUri(
            $dashboardTopDepartments->map(fn($d) => $d->department->name ?? '-')->toArray(),
            $dashboardTopDepartments->pluck('total')->toArray(),
            'Total Dokumen per Departemen'
        );

        $pdf = Pdf::loadView('documents.export_dashboard_template_pdf', array_merge($templateData, [
            'generatedAt' => now(),
            'dashboardTotalDocuments' => (clone $docQuery)->count(),
            'dashboardCategoryCount' => $dashboardCategoryCount,
            'dashboardTopDepartments' => $dashboardTopDepartments,
            'dashboardLatestUploaded' => $dashboardLatestUploaded,
            'categoryPieUri' => $categoryPieUri,
            'departmentBarUri' => $departmentBarUri,
            'deptCategoryCharts' => $deptCategoryCharts,
        ]))->setPaper('a4', 'landscape');

        $filename = $this->makeDownloadName(
            'Weekly Report Document - ' . now()->format('Y-m-d'),
            'pdf'
        );

        return $pdf->download($filename);
    }

    /**
     * EXPORT TEMPLATE PERUSAHAAN (XLSX)
     */
    public function exportTemplateXlsx(Request $request)
    {
        Gate::authorize('document.manage');
        $data = $this->buildTemplateExportData($request);
        $headerDocNo = (string) ($data['meta']['header_doc_no'] ?? '');

        if (!class_exists(\ZipArchive::class)) {
            return back()->with('error', 'Export XLSX membutuhkan ekstensi PHP ZIP. Aktifkan extension=zip di php.ini lalu restart server.');
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'exp_xlsx_');
        if ($tmpFile === false) {
            abort(500, 'Gagal menyiapkan file export.');
        }
        @unlink($tmpFile);
        $xlsxPath = $tmpFile . '.xlsx';

        $xmlEscape = fn($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $col = function (int $n): string {
            $s = '';
            while ($n > 0) {
                $m = ($n - 1) % 26;
                $s = chr(65 + $m) . $s;
                $n = intdiv($n - 1, 26);
            }
            return $s;
        };
        $cell = function (int $c, int $r, $v, bool $numeric = false) use ($col, $xmlEscape): string {
            $ref = $col($c) . $r;
            if ($numeric && is_numeric($v)) {
                return '<c r="' . $ref . '"><v>' . $v . '</v></c>';
            }
            return '<c r="' . $ref . '" t="inlineStr"><is><t>' . $xmlEscape($v) . '</t></is></c>';
        };

        $rowsXml = [];
        $r = 1;
        $rowsXml[] = '<row r="' . $r . '">' . $cell(1, $r, 'FORMULIR') . $cell(2, $r, 'DAFTAR INDUK DOKUMEN') . '</row>';
        $r++;
        $rowsXml[] = '<row r="' . $r . '">' . $cell(1, $r, 'PROJECT') . $cell(2, $r, $data['meta']['project']) . '</row>';
        $r++;
        $rowsXml[] = '<row r="' . $r . '">' . $cell(1, $r, 'TANGGAL UPDATE') . $cell(2, $r, $data['meta']['update_date']) . '</row>';
        $r++;
        $rowsXml[] = '<row r="' . $r . '">' .
            $cell(1, $r, 'NO') .
            $cell(2, $r, 'DEPT') .
            $cell(3, $r, 'JENIS') .
            $cell(4, $r, 'NOMOR DOKUMEN') .
            $cell(5, $r, 'JUDUL DOKUMEN') .
            $cell(6, $r, 'DEPARTEMEN TERKAIT') .
            $cell(7, $r, 'ISSUED DATE') .
            $cell(8, $r, 'REVISI 1') .
            $cell(9, $r, 'REVISI 2') .
            $cell(10, $r, 'REVISI 3') .
            $cell(11, $r, 'REVISI 4') .
            $cell(12, $r, 'REVISI 5') .
            $cell(13, $r, 'LOKASI PENYIMPANAN') .
            $cell(14, $r, 'REMARKS') .
            '</row>';

        foreach ($data['rows'] as $item) {
            $r++;
            $rowsXml[] = '<row r="' . $r . '">' .
                $cell(1, $r, $item['no'], true) .
                $cell(2, $r, $item['dept']) .
                $cell(3, $r, $item['jenis']) .
                $cell(4, $r, $item['nomor_dokumen']) .
                $cell(5, $r, $item['judul_dokumen']) .
                $cell(6, $r, $item['departemen_terkait']) .
                $cell(7, $r, $item['issued_date']) .
                $cell(8, $r, $item['revisi_1']) .
                $cell(9, $r, $item['revisi_2']) .
                $cell(10, $r, $item['revisi_3']) .
                $cell(11, $r, $item['revisi_4']) .
                $cell(12, $r, $item['revisi_5']) .
                $cell(13, $r, $item['lokasi_penyimpanan']) .
                $cell(14, $r, $item['remarks']) .
                '</row>';
        }

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . implode('', $rowsXml) . '</sheetData>'
            . '</worksheet>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Daftar Induk Dokumen" sheetId="1" r:id="rId1"/></sheets></workbook>';

        $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>';

        $relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $wbRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';

        $zip = new \ZipArchive();
        if ($zip->open($xlsxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat file XLSX.');
        }
        $zip->addFromString('[Content_Types].xml', $contentTypesXml);
        $zip->addFromString('_rels/.rels', $relsXml);
        $zip->addFromString('xl/workbook.xml', $workbookXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRelsXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $filename = $this->makeDownloadName(
            $headerDocNo . ' - Daftar Induk Dokumen - ' . now()->format('Y-m-d'),
            'xlsx'
        );

        return response()->download($xlsxPath, $filename)->deleteFileAfterSend(true);
    }

    private function buildTemplateExportData(Request $request): array
    {
        $includeHo = filter_var($request->query('include_ho', '1'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $includeHo = $includeHo === null ? true : $includeHo;
        $selectedDepartmentIds = collect((array) $request->query('department_ids', []))
            ->map(fn($v) => (int) $v)
            ->filter(fn($v) => $v > 0)
            ->unique()
            ->values()
            ->all();
        $selectedSiteIds = collect((array) $request->query('site_ids', []))
            ->map(fn($v) => (int) $v)
            ->filter(fn($v) => $v > 0)
            ->unique()
            ->values()
            ->all();
        $siteDepartmentMap = collect((array) $request->query('site_department_map', []))
            ->mapWithKeys(function ($v, $k) {
                $siteId = (int) $k;
                if ($siteId <= 0) {
                    return [];
                }
                $vals = collect((array) $v)
                    ->map(fn($x) => (string) $x)
                    ->filter(fn($x) => $x !== '')
                    ->unique()
                    ->values()
                    ->all();
                if (count($vals) === 0) {
                    $vals = ['all'];
                }
                return [$siteId => $vals];
            })
            ->all();

        $documents = $this->queryDocumentsForTemplateExport($request);

        $rows = $documents->values()->map(function ($doc, $idx) {
            $revDates = [];
            for ($i = 1; $i <= 5; $i++) {
                $rev = $doc->revisions->where('revision_number', $i)->sortByDesc('revised_at')->first();
                $revDates[$i] = $rev?->revised_at?->format('Y-m-d') ?? '';
            }

            return [
                'no' => $idx + 1,
                'dept' => $doc->department->name ?? '-',
                'jenis' => $doc->kategori ?? '-',
                'nomor_dokumen' => $doc->document_number ?? '-',
                'judul_dokumen' => $doc->title ?? '-',
                'departemen_terkait' => '',
                'issued_date' => $doc->published_at?->format('Y-m-d') ?? '-',
                'revisi_1' => $revDates[1],
                'revisi_2' => $revDates[2],
                'revisi_3' => $revDates[3],
                'revisi_4' => $revDates[4],
                'revisi_5' => $revDates[5],
                'lokasi_penyimpanan' => 'Portal Dokumen CPSD',
                'remarks' => $doc->revision_note ?? '',
            ];
        });

        $rowsPerPage = max(1, (int) $request->query('rows_per_page', 25));
        $totalPages = max(1, (int) ceil(max(1, $rows->count()) / $rowsPerPage));

        $project = trim((string) $request->query('project', ''));
        $updateDate = trim((string) $request->query('update_date', ''));
        if ($updateDate === '') {
            $updateDate = now()->format('Y-m-d');
        }
        $updateDateDisplay = $updateDate;
        try {
            $updateDateDisplay = Carbon::parse($updateDate)->locale('id')->translatedFormat('l, d F Y');
        } catch (\Throwable $e) {
            // fallback tetap pakai nilai raw jika format tidak valid
        }

        $meta = [
            'project' => $project,
            'update_date' => $updateDate,
            'update_date_display' => $updateDateDisplay,
            'header_doc_no' => (string) $request->query('header_doc_no', 'PST/CPSD/F-006'),
            'header_effective_date' => (string) $request->query('header_effective_date', '8 April 2025'),
            'header_revision' => (string) $request->query('header_revision', '0'),
            'header_page' => '1 dari ' . $totalPages,
            'include_ho' => $includeHo,
            'department_ids' => $selectedDepartmentIds,
            'site_ids' => $selectedSiteIds,
            'site_department_map' => $siteDepartmentMap,
        ];

        return [
            'rows' => $rows,
            'meta' => $meta,
            'totalRows' => $rows->count(),
            'rowsPerPage' => $rowsPerPage,
            'totalPages' => $totalPages,
        ];
    }

    private function queryDocumentsForTemplateExport(Request $request)
    {
        $user = Auth::user();
        $includeHo = filter_var($request->query('include_ho', '1'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $includeHo = $includeHo === null ? true : $includeHo;
        $selectedDepartmentIds = collect((array) $request->query('department_ids', []))
            ->map(fn($v) => (int) $v)
            ->filter(fn($v) => $v > 0)
            ->unique()
            ->values()
            ->all();
        $selectedSiteIds = collect((array) $request->query('site_ids', []))
            ->map(fn($v) => (int) $v)
            ->filter(fn($v) => $v > 0)
            ->unique()
            ->values()
            ->all();
        $siteDepartmentMap = collect((array) $request->query('site_department_map', []))
            ->mapWithKeys(function ($v, $k) {
                $siteId = (int) $k;
                if ($siteId <= 0) {
                    return [];
                }
                $vals = collect((array) $v)
                    ->map(fn($x) => (string) $x)
                    ->filter(fn($x) => $x !== '')
                    ->unique()
                    ->values()
                    ->all();
                if (count($vals) === 0) {
                    $vals = ['all'];
                }
                return [$siteId => $vals];
            })
            ->all();

        $documents = Document::with([
            'department',
            'site',
            'revisions',
            'relatedDocuments.department',
        ]);

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

        $documents->where(function ($q) use ($includeHo, $selectedSiteIds, $siteDepartmentMap) {
            if ($includeHo) {
                $q->orWhereNull('site_id');
            }
            foreach ($selectedSiteIds as $siteId) {
                $siteDeptSelections = (array) ($siteDepartmentMap[$siteId] ?? ['all']);
                $q->orWhere(function ($qSite) use ($siteId, $siteDeptSelections) {
                    $qSite->where('site_id', $siteId);
                    $hasAll = in_array('all', array_map('strtolower', $siteDeptSelections), true);
                    if (!$hasAll) {
                        $deptIds = collect($siteDeptSelections)
                            ->map(fn($x) => (int) $x)
                            ->filter(fn($x) => $x > 0)
                            ->values()
                            ->all();
                        if (count($deptIds) > 0) {
                            $qSite->whereIn('department_id', $deptIds);
                        }
                    }
                });
            }
        });

        if (!$includeHo && count($selectedSiteIds) === 0) {
            $documents->whereRaw('1 = 0');
        }

        if ($includeHo && count($selectedDepartmentIds) > 0) {
            $documents->where(function ($q) use ($selectedDepartmentIds) {
                $q->whereNotNull('site_id')
                    ->orWhere(function ($qHo) use ($selectedDepartmentIds) {
                        $qHo->whereNull('site_id')
                            ->whereIn('department_id', $selectedDepartmentIds);
                    });
            });
        }

        $sort = $request->query('sort');
        $order = strtolower($request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['title', 'published_at', 'revision_number'];

        if ($sort && in_array($sort, $allowedSorts)) {
            return $documents->orderBy($sort, $order)->get();
        }

        $orderKategori = $documentTypes->pluck('name')->toArray();
        return $documents->get()->sort(function ($a, $b) use ($orderKategori) {
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

    private function buildTemplateProjectName(bool $includeHo, array $departmentIds, array $siteIds): string
    {
        $user = Auth::user();
        $sourceParts = [];
        if ($includeHo) {
            $sourceParts[] = 'Head Office';
        }
        $siteNames = Site::whereIn('id', $siteIds)->orderBy('name')->pluck('name')->all();
        if (count($siteNames) > 0) {
            $sourceParts[] = implode(', ', $siteNames);
        }

        if (count($sourceParts) === 0) {
            $sourceParts[] = 'Head Office';
        }

        $accessibleDepartments = Department::query()
            ->when($user && $user->role_id == 3, function ($q) use ($user) {
                $q->whereIn('id', array_filter([
                    $user->department_id,
                    Department::where('name', 'GENERAL')->value('id'),
                ]));
            })
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        $selectedDepartmentIds = array_values(array_unique(array_map('intval', $departmentIds)));
        $allDepartmentsSelected = count($selectedDepartmentIds) === 0
            || (count($accessibleDepartments) > 0
                && count(array_diff($accessibleDepartments, $selectedDepartmentIds)) === 0);

        $departmentNames = Department::whereIn('id', $departmentIds)->orderBy('name')->pluck('name')->all();
        if ($includeHo && !$allDepartmentsSelected && count($departmentNames) > 0) {
            return implode(' + ', $sourceParts) . ' - Dept: ' . implode(', ', $departmentNames);
        }

        return implode(' + ', $sourceParts);
    }

    private function makePieChartDataUri(array $labels, array $values, string $title = ''): string
    {
        $w = 620;
        $h = 250;
        $cx = 160;
        $cy = 138;
        $r = 76;
        $innerR = 48;
        $total = max(1, array_sum($values));
        $colors = ['#0AA03A', '#EA580C', '#2563EB', '#111827', '#9333EA', '#14B8A6', '#F59E0B', '#EF4444'];
        $start = -M_PI / 2;
        $paths = '';
        $legend = '';

        foreach ($values as $i => $v) {
            if ($v <= 0) {
                continue;
            }
            $angle = ($v / $total) * 2 * M_PI;
            $end = $start + $angle;
            $x1 = $cx + $r * cos($start);
            $y1 = $cy + $r * sin($start);
            $x2 = $cx + $r * cos($end);
            $y2 = $cy + $r * sin($end);
            $largeArc = $angle > M_PI ? 1 : 0;
            $color = $colors[$i % count($colors)];
            $paths .= '<path d="M ' . $cx . ' ' . $cy . ' L ' . round($x1, 2) . ' ' . round($y1, 2) . ' A ' . $r . ' ' . $r . ' 0 ' . $largeArc . ' 1 ' . round($x2, 2) . ' ' . round($y2, 2) . ' Z" fill="' . $color . '" />';

            $label = htmlspecialchars((string)($labels[$i] ?? '-'), ENT_QUOTES, 'UTF-8');
            $legendY = 58 + ($i * 18);
            $legend .= '<rect x="300" y="' . $legendY . '" width="10" height="10" rx="2" fill="' . $color . '" />';
            $legend .= '<text x="315" y="' . ($legendY + 9) . '" font-size="10" fill="#111827">' . $label . ' (' . $v . ')</text>';
            $start = $end;
        }

        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '">'
            . '<rect width="100%" height="100%" fill="#ffffff"/>'
            . '<rect x="8" y="8" width="' . ($w - 16) . '" height="' . ($h - 16) . '" rx="8" fill="#ffffff" stroke="#e5e7eb"/>'
            . '<text x="18" y="28" font-size="14" font-weight="700" fill="#111827">' . $safeTitle . '</text>'
            . $paths
            . '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $innerR . '" fill="#ffffff" stroke="#e5e7eb"/>'
            . '<text x="' . $cx . '" y="' . ($cy - 4) . '" font-size="10" text-anchor="middle" fill="#64748b">Total</text>'
            . '<text x="' . $cx . '" y="' . ($cy + 14) . '" font-size="18" font-weight="700" text-anchor="middle" fill="#0f172a">' . (int)$total . '</text>'
            . $legend
            . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function makeBarChartDataUri(array $labels, array $values, string $title = ''): string
    {
        $w = 620;
        $h = 250;
        $left = 115;
        $top = 52;
        $chartW = 480;
        $barH = 13;
        $gap = 7;
        $max = max(1, (int)max($values ?: [1]));

        $grid = '';
        for ($i = 0; $i <= 4; $i++) {
            $x = $left + (int)round(($i / 4) * $chartW);
            $tickVal = (int)round(($i / 4) * $max);
            $grid .= '<line x1="' . $x . '" y1="' . ($top - 6) . '" x2="' . $x . '" y2="' . ($h - 20) . '" stroke="#e5e7eb" stroke-width="1"/>';
            $grid .= '<text x="' . $x . '" y="' . ($h - 8) . '" font-size="8" text-anchor="middle" fill="#64748b">' . $tickVal . '</text>';
        }

        $bars = '';
        foreach ($values as $i => $v) {
            $y = $top + ($i * ($barH + $gap));
            $width = (int)round(($v / $max) * $chartW);
            $label = htmlspecialchars((string)($labels[$i] ?? '-'), ENT_QUOTES, 'UTF-8');
            $bars .= '<text x="' . ($left - 8) . '" y="' . ($y + 10) . '" font-size="9" text-anchor="end" fill="#111827">' . $label . '</text>';
            $bars .= '<rect x="' . $left . '" y="' . $y . '" width="' . $chartW . '" height="' . $barH . '" fill="#f1f5f9" />';
            $bars .= '<rect x="' . $left . '" y="' . $y . '" width="' . max($width, 1) . '" height="' . $barH . '" fill="#0AA03A" />';
            $valX = $left + $width + 5;
            if ($valX > ($left + $chartW - 14)) {
                $valX = $left + $chartW - 14;
            }
            $bars .= '<text x="' . $valX . '" y="' . ($y + 10) . '" font-size="8" fill="#0f172a">' . (int)$v . '</text>';
        }

        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '">'
            . '<rect width="100%" height="100%" fill="#ffffff"/>'
            . '<rect x="8" y="8" width="' . ($w - 16) . '" height="' . ($h - 16) . '" rx="8" fill="#ffffff" stroke="#e5e7eb"/>'
            . '<text x="18" y="28" font-size="14" font-weight="700" fill="#111827">' . $safeTitle . '</text>'
            . $grid
            . $bars
            . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function makeDeptCategoryChartDataUri(string $deptName, array $values, int $maxScale = 0): string
    {
        $labels = ['SOP', 'IK', 'FORM', 'STD'];
        $series = [
            (int)($values['SOP'] ?? 0),
            (int)($values['IK'] ?? 0),
            (int)($values['FORM'] ?? 0),
            (int)($values['STD'] ?? 0),
        ];

        $w = 420;
        $h = 168;
        $left = 84;
        $top = 38;
        $chartW = 300;
        $barH = 14;
        $gap = 10;
        $max = max(1, $maxScale > 0 ? $maxScale : max($series));
        $colors = ['#EA580C', '#16A34A', '#111827', '#2563EB'];
        $bars = '';

        foreach ($series as $i => $val) {
            $y = $top + ($i * ($barH + $gap));
            $width = (int)round(($val / $max) * $chartW);
            $bars .= '<text x="' . ($left - 8) . '" y="' . ($y + 11) . '" font-size="10" text-anchor="end" fill="#111827">' . $labels[$i] . '</text>';
            $bars .= '<rect x="' . $left . '" y="' . $y . '" width="' . $chartW . '" height="' . $barH . '" fill="#f1f5f9" />';
            $bars .= '<rect x="' . $left . '" y="' . $y . '" width="' . max($width, 1) . '" height="' . $barH . '" fill="' . $colors[$i] . '" />';
            $valX = $left + $width + 5;
            if ($valX > ($left + $chartW - 14)) {
                $valX = $left + $chartW - 14;
            }
            $bars .= '<text x="' . $valX . '" y="' . ($y + 11) . '" font-size="9" fill="#0f172a">' . $val . '</text>';
        }

        $safeDept = htmlspecialchars($deptName, ENT_QUOTES, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '">'
            . '<rect width="100%" height="100%" fill="#ffffff"/>'
            . '<rect x="2" y="2" width="' . ($w - 4) . '" height="' . ($h - 4) . '" rx="6" fill="#ffffff" stroke="#e5e7eb"/>'
            . '<text x="10" y="20" font-size="12" font-weight="700" fill="#065f46">' . $safeDept . '</text>'
            . '<text x="' . ($w - 10) . '" y="20" font-size="9" text-anchor="end" fill="#64748b">Skala max: ' . $max . '</text>'
            . $bars
            . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
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
        $path = $this->resolvePhysicalPath($doc->file_path);
        if (!$path) {
            abort(404, 'File tidak ditemukan');
        }

        while (ob_get_level()) ob_end_clean();

        $mime = mime_content_type($path) ?: 'application/octet-stream';
        header("Content-Type: " . $mime);
        header("Content-Length: " . filesize($path));
        header("Content-Disposition: inline; filename=\"" . basename($path) . "\"");

        readfile($path);
        exit;
    }

    /**
     * FORCE DOWNLOAD FILE UTAMA DOKUMEN
     */
    public function download($id)
    {
        $doc = Document::findOrFail($id);
        if (!Gate::allows('document.view', $doc)) {
            abort(403);
        }
        $path = $this->resolvePhysicalPath($doc->file_path);
        if (!$path) {
            abort(404, 'File tidak ditemukan');
        }
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $base = trim(($doc->document_number ?? '') . ' - ' . ($doc->title ?? ''));
        $downloadName = $this->makeDownloadName($base, $ext);
        return response()->download($path, $downloadName);
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

    public function edit(Request $request, $id)
    {
        Gate::authorize('document.manage');
        $returnQuery = $request->only([
            'search',
            'kategori',
            'published_start',
            'published_end',
            'department_id',
            'site_id',
            'sort',
            'order',
            'per_page',
            'page',
        ]);

        return view('documents.edit', [
            'document'    => Document::findOrFail($id),
            'departments' => Department::all(),
            'sites' => Site::orderBy('name')->get(),
            'documentTypes' => DocumentType::orderBy('sort_order')->orderBy('name')->get(),
            'returnQuery' => array_filter($returnQuery, fn($v) => $v !== null && $v !== ''),
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

        $returnQuery = $request->only([
            'search',
            'kategori',
            'published_start',
            'published_end',
            'department_id',
            'site_id',
            'sort',
            'order',
            'per_page',
            'page',
        ]);

        return redirect()
            ->route('documents.index', array_filter($returnQuery, fn($v) => $v !== null && $v !== ''))
            ->with('success', 'Dokumen berhasil diperbarui.');
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

    public function deleteRevision($id, $revisionId)
    {
        Gate::authorize('document.manage');
        if (!app()->environment('local')) {
            abort(403, 'Aksi ini hanya diizinkan pada environment local.');
        }

        $doc = Document::findOrFail($id);
        $revision = DocumentRevision::where('document_id', $doc->id)->findOrFail($revisionId);
        $revision->delete();

        $this->syncRevisionSummary($doc);

        return back()->with('success', 'Riwayat revisi berhasil dihapus.');
    }

    public function editRevision($id, $revisionId)
    {
        Gate::authorize('document.manage');
        $document = Document::findOrFail($id);
        $revision = DocumentRevision::where('document_id', $document->id)->findOrFail($revisionId);

        return view('documents.revisions.edit', [
            'document' => $document,
            'revision' => $revision,
        ]);
    }

    public function updateRevision(Request $request, $id, $revisionId)
    {
        Gate::authorize('document.manage');
        $document = Document::findOrFail($id);
        $revision = DocumentRevision::where('document_id', $document->id)->findOrFail($revisionId);

        $request->validate([
            'revision_number' => 'required|integer|min:0',
            'revision_note' => 'required|string',
            'revised_at' => 'nullable|date',
        ]);

        $revision->update([
            'revision_number' => (int) $request->revision_number,
            'revision_note' => (string) $request->revision_note,
            'revised_at' => $request->revised_at ? $request->revised_at : $revision->revised_at,
            'revised_by' => Auth::id(),
        ]);

        $this->syncRevisionSummary($document);

        DocumentAudit::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'action' => 'revision',
            'meta' => [
                'document_number' => $document->document_number,
                'title' => $document->title,
                'kategori' => $document->kategori,
                'revision_number' => (int) $request->revision_number,
                'revision_note' => (string) $request->revision_note,
                'event' => 'revision_edit',
            ],
        ]);

        return redirect()
            ->route('documents.show', $document->id)
            ->with('success', 'Riwayat revisi berhasil diperbarui.');
    }

    private function syncRevisionSummary(Document $doc): void
    {
        $latest = DocumentRevision::where('document_id', $doc->id)
            ->orderBy('revision_number', 'desc')
            ->orderBy('revised_at', 'desc')
            ->first();

        $doc->update([
            'revision_number' => $latest?->revision_number ?? 0,
            'revision_note' => $latest?->revision_note,
            'last_revision_at' => $latest?->revised_at,
        ]);
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
        $path = $this->resolvePhysicalPath($doc->form_description_path);
        if (!$path) {
            abort(404, 'File tidak ditemukan');
        }

        while (ob_get_level()) ob_end_clean();

        $mime = mime_content_type($path) ?: 'application/pdf';
        header("Content-Type: " . $mime);
        header("Content-Length: " . filesize($path));
        header("Content-Disposition: inline; filename=\"" . basename($path) . "\"");

        readfile($path);
        exit;
    }

    /**
     * FORCE DOWNLOAD PDF PENJELASAN FORM
     */
    public function downloadDescription($id)
    {
        $doc = Document::findOrFail($id);
        if (!Gate::allows('document.view', $doc)) {
            abort(403);
        }
        $path = $this->resolvePhysicalPath($doc->form_description_path);
        if (!$path) {
            abort(404, 'File tidak ditemukan');
        }
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $base = trim(($doc->document_number ?? '') . ' - ' . ($doc->title ?? '')) . ' - Penjelasan';
        $downloadName = $this->makeDownloadName($base, $ext);
        return response()->download($path, $downloadName);
    }

    private function resolvePhysicalPath(?string $relativePath): ?string
    {
        if (!$relativePath) {
            return null;
        }

        $relativePath = trim($relativePath);
        $relativePath = ltrim($relativePath, '/\\');
        $normalized = str_replace('\\', '/', $relativePath);

        // Jika tersimpan sebagai URL penuh, ambil path-nya saja.
        if (preg_match('#^https?://#i', $normalized)) {
            $urlPath = parse_url($normalized, PHP_URL_PATH);
            if (is_string($urlPath) && $urlPath !== '') {
                $normalized = ltrim($urlPath, '/');
            }
        }

        // Jika sudah absolute path dan file ada, pakai langsung.
        if (is_file($normalized)) {
            return $normalized;
        }
        if (is_file($relativePath)) {
            return $relativePath;
        }

        // Hilangkan prefix umum jika tersimpan tidak konsisten di DB.
        $trimmed = preg_replace(
            '#^(public_html/|portaldo/public_html/|storage/app/public/|public/storage/|storage/)#',
            '',
            $normalized
        );
        $trimmed = ltrim((string) $trimmed, '/');
        $baseParent = dirname(base_path());
        $altPublicHtml = $baseParent . DIRECTORY_SEPARATOR . 'public_html';

        $candidates = [
            storage_path('app/public/' . $trimmed),
            storage_path('app/' . $trimmed),
            public_path('storage/' . $trimmed),
            public_path('storage/app/public/' . $trimmed),
            $altPublicHtml . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $trimmed,
            $altPublicHtml . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $trimmed,
            base_path('storage/app/public/' . $trimmed),
            base_path('public/storage/' . $trimmed),
        ];

        foreach ($candidates as $fullPath) {
            if (is_file($fullPath)) {
                return $fullPath;
            }
        }

        return null;
    }

    private function makeDownloadName(string $base, string $ext = ''): string
    {
        $base = trim($base);
        $base = preg_replace('/[\\\\\\/:*?"<>|]+/', '-', $base);
        $base = preg_replace('/\s+/', ' ', (string) $base);
        $base = trim((string) $base, " .-_");

        if ($base === '') {
            $base = 'dokumen';
        }

        $ext = trim($ext);
        if ($ext === '') {
            return $base;
        }

        return $base . '.' . $ext;
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
