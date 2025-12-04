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

        // ==========================
        //  ROLE USER (role 3)
        // ==========================
        if ($user->role_id == 3) {

            // Last updated documents (10 terbaru)
            $documents = Document::where('department_id', $user->department_id)
                ->latest('updated_at')
                ->take(10)
                ->get();

            // Statistik kategori untuk departemen user
            $categoryCount = Document::select('kategori', DB::raw('count(*) as total'))
                ->where('department_id', $user->department_id)
                ->groupBy('kategori')
                ->pluck('total', 'kategori');

            // Total dokumen
            $totalDocuments = Document::where('department_id', $user->department_id)->count();

            return view('dashboard.user', [
                'documents' => $documents,
                'categoryCount' => $categoryCount,
                'totalDocuments' => $totalDocuments
            ]);
        }

        // ==========================
        //  ROLE ADMIN / SUPER USER
        // ==========================

        $categoryCount = Document::select('kategori', DB::raw('count(*) as total'))
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        $uploadPerMonth = Document::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw('count(*) as total')
        )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->take(12)
            ->get();

        $topDepartments = Document::select('department_id', DB::raw('count(*) as total'))
            ->groupBy('department_id')
            ->with('department')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        $topKategori = Document::select('kategori', DB::raw('count(*) as total'))
            ->groupBy('kategori')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        $lastUpdated = Document::with('department')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.admin', [
            'categoryCount' => $categoryCount,
            'uploadPerMonth' => $uploadPerMonth,
            'topDepartments' => $topDepartments,
            'topKategori' => $topKategori,
            'lastUpdated' => $lastUpdated,
        ]);
    }
}
