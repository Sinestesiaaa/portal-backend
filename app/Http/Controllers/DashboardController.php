<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ==================================================
        // ROLE USER (role 3)
        // ==================================================
        if ($user->role_id == 3) {

            $documents = Document::where('department_id', $user->department_id)
                ->latest('updated_at')
                ->take(10)
                ->get();

            $categoryCount = Document::select('kategori', DB::raw('count(*) as total'))
                ->where('department_id', $user->department_id)
                ->groupBy('kategori')
                ->pluck('total', 'kategori');

            $totalDocuments = Document::where('department_id', $user->department_id)->count();

            return view('dashboard.user', [
                'documents' => $documents,
                'categoryCount' => $categoryCount,
                'totalDocuments' => $totalDocuments,
            ]);
        }

        // ==================================================
        // ADMIN & SUPER USER
        // ==================================================

        // Statistik kategori
        $categoryCount = Document::select('kategori', DB::raw('count(*) as total'))
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        // Upload bulanan (12 bulan terakhir)
        $uploadPerMonth = Document::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw('count(*) as total')
        )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->take(12)
            ->get();

        // Top 5 departemen
        $topDepartments = Document::select('department_id', DB::raw('count(*) as total'))
            ->groupBy('department_id')
            ->with('department')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        // Top kategori
        $topKategori = Document::select('kategori', DB::raw('count(*) as total'))
            ->groupBy('kategori')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        // Last updated
        $lastUpdated = Document::with('department')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // ==================================================
        //  TOTAL DOKUMEN PER DEPARTEMEN (untuk horizontal chart)
        // ==================================================
        $departmentTotals = Document::select('department_id', DB::raw('COUNT(*) as total'))
            ->groupBy('department_id')
            ->with('department')
            ->get();

        // ==================================================
        //  TOTAL DOKUMEN PER DEPARTEMEN PER KATEGORI
        // ==================================================
        $deptCategoryCounts = Document::select(
            'department_id',
            'kategori',
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('department_id', 'kategori')
            ->with('department')
            ->get();


        // Convert ke format mudah dipakai di Blade
        $deptCategoryData = [];

        foreach ($deptCategoryCounts as $row) {
            $dept = $row->department->name ?? 'Unknown';

            if (!isset($deptCategoryData[$dept])) {
                $deptCategoryData[$dept] = [
                    'SOP' => 0,
                    'IK' => 0,
                    'FORM' => 0,
                    'STD' => 0,
                ];
            }

            $deptCategoryData[$dept][$row->kategori] = $row->total;
        }

        return view('dashboard.admin', [
            'categoryCount' => $categoryCount,
            'uploadPerMonth' => $uploadPerMonth,
            'topDepartments' => $topDepartments,
            'topKategori' => $topKategori,
            'lastUpdated' => $lastUpdated,
            'departmentTotals' => $departmentTotals,
            'deptCategoryData' => $deptCategoryData,
        ]);
    }
}
