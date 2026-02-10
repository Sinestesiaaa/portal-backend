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
        // ROLE USER (role_id = 3)
        // ==================================================
        if ($user->role_id == 3) {

            // Dokumen terbaru berdasarkan CREATED_AT
            $latestCreated = Document::where('department_id', $user->department_id)
                ->orderBy('created_at', 'desc')
                ->take(6)
                ->get();

            // Hitung kategori hanya untuk departemen user
            $categoryCount = Document::select('kategori', DB::raw('COUNT(*) as total'))
                ->where('department_id', $user->department_id)
                ->groupBy('kategori')
                ->pluck('total', 'kategori');

            $totalDocuments = Document::where('department_id', $user->department_id)->count();

            return view('dashboard.user', [
                'latestCreated'   => $latestCreated,
                'categoryCount'   => $categoryCount,
                'totalDocuments'  => $totalDocuments,
            ]);
        }

        // ==================================================
        // ADMIN & SUPER USER DASHBOARD
        // ==================================================

        // 1. Statistik kategori
        $categoryCount = Document::select('kategori', DB::raw('COUNT(*) as total'))
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        // 2. Upload bulanan (12 bulan terakhir)
        $uploadPerMonth = Document::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw("COUNT(*) as total")
        )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->take(12)
            ->get();

        // 3. Semua departemen (urut paling aktif)
        $topDepartments = Document::select('department_id', DB::raw('COUNT(*) as total'))
            ->with('department')
            ->groupBy('department_id')
            ->orderBy('total', 'desc')
            ->get();

        // 4. Top kategori
        $topKategori = Document::select('kategori', DB::raw('COUNT(*) as total'))
            ->groupBy('kategori')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        // 5. Last updated (UPDATED_AT)
        $lastUpdated = Document::with('department')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // 6. Total per departemen (horizontal bar chart)
        $departmentTotals = Document::select('department_id', DB::raw('COUNT(*) as total'))
            ->with('department')
            ->groupBy('department_id')
            ->get();

        // 7. Total dokumen per departemen per kategori
        $deptCategoryCounts = Document::select(
            'department_id',
            'kategori',
            DB::raw('COUNT(*) as total')
        )
            ->with('department')
            ->groupBy('department_id', 'kategori')
            ->get();

        // Format agar mudah dipakai di Blade charts
        $deptCategoryData = [];

        foreach ($deptCategoryCounts as $row) {
            $deptName = $row->department->name ?? 'Unknown';

            if (!isset($deptCategoryData[$deptName])) {
                $deptCategoryData[$deptName] = [
                    'SOP' => 0,
                    'IK'  => 0,
                    'FORM' => 0,
                    'STD' => 0,
                ];
            }

            $deptCategoryData[$deptName][$row->kategori] = $row->total;
        }

        return view('dashboard.admin', [
            'categoryCount'      => $categoryCount,
            'uploadPerMonth'     => $uploadPerMonth,
            'topDepartments'     => $topDepartments,
            'topKategori'        => $topKategori,
            'lastUpdated'        => $lastUpdated,
            'departmentTotals'   => $departmentTotals,
            'deptCategoryData'   => $deptCategoryData,
        ]);
    }
}
