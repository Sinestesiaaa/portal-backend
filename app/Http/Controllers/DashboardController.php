<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function exportPdf()
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return back()->with('error', 'PDF export belum tersedia. Jalankan composer require barryvdh/laravel-dompdf.');
        }

        $user = Auth::user();

        if ($user->role_id == 3) {
            $latestCreated = Document::where('department_id', $user->department_id)
                ->with('department')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            $categoryCount = Document::select('kategori', DB::raw('COUNT(*) as total'))
                ->where('department_id', $user->department_id)
                ->groupBy('kategori')
                ->orderBy('kategori')
                ->pluck('total', 'kategori');

            $totalDocuments = Document::where('department_id', $user->department_id)->count();
            $categoryChartUri = $this->makeBarChartDataUri(
                $categoryCount->keys()->toArray(),
                $categoryCount->values()->toArray(),
                'Dokumen per Kategori'
            );

            $pdf = Pdf::loadView('dashboard.export_pdf_user', [
                'generatedAt' => now(),
                'totalDocuments' => $totalDocuments,
                'categoryCount' => $categoryCount,
                'latestCreated' => $latestCreated,
                'categoryChartUri' => $categoryChartUri,
                'user' => $user,
            ])->setPaper('a4', 'portrait');

            return $pdf->download('Document Report - ' . now()->format('Y-m-d') . '.pdf');
        }

        $categoryCount = Document::select('kategori', DB::raw('COUNT(*) as total'))
            ->groupBy('kategori')
            ->orderBy('kategori')
            ->pluck('total', 'kategori');

        $topDepartments = Document::select('department_id', DB::raw('COUNT(*) as total'))
            ->with('department')
            ->groupBy('department_id')
            ->orderBy('total', 'desc')
            ->get();

        $lastUpdated = Document::with('department')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
        $latestUploaded = Document::with('department')
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get();
        $deptCategoryCounts = Document::select(
            'department_id',
            'kategori',
            DB::raw('COUNT(*) as total')
        )
            ->with('department')
            ->groupBy('department_id', 'kategori')
            ->get();

        $deptCategoryData = [];
        foreach ($deptCategoryCounts as $row) {
            $deptName = $row->department->name ?? 'Unknown';
            if (!isset($deptCategoryData[$deptName])) {
                $deptCategoryData[$deptName] = [
                    'SOP' => 0,
                    'IK' => 0,
                    'FORM' => 0,
                    'STD' => 0,
                ];
            }
            $deptCategoryData[$deptName][$row->kategori] = (int)$row->total;
        }

        // Batasi agar PDF ringkas dan tidak terlalu banyak halaman.
        $topDepartmentNames = $topDepartments->take(6)->map(fn($d) => $d->department->name ?? 'Unknown')->toArray();
        $deptCategoryCharts = [];
        $maxDeptScale = 1;
        foreach ($topDepartmentNames as $deptName) {
            if (!isset($deptCategoryData[$deptName])) {
                continue;
            }
            $maxDeptScale = max($maxDeptScale, max($deptCategoryData[$deptName]));
        }
        foreach ($topDepartmentNames as $deptName) {
            if (!isset($deptCategoryData[$deptName])) {
                continue;
            }
            $values = $deptCategoryData[$deptName];
            $deptCategoryCharts[] = [
                'name' => $deptName,
                'uri' => $this->makeDeptCategoryChartDataUri($deptName, $values, $maxDeptScale),
            ];
        }
        $categoryPieUri = $this->makePieChartDataUri(
            $categoryCount->keys()->toArray(),
            $categoryCount->values()->toArray(),
            'Komposisi Kategori'
        );
        $departmentBarUri = $this->makeBarChartDataUri(
            $topDepartments->map(fn($d) => $d->department->name ?? '-')->toArray(),
            $topDepartments->pluck('total')->toArray(),
            'Total Dokumen per Departemen'
        );

        $pdf = Pdf::loadView('dashboard.export_pdf_admin', [
            'generatedAt' => now(),
            'categoryCount' => $categoryCount,
            'topDepartments' => $topDepartments,
            'lastUpdated' => $lastUpdated,
            'latestUploaded' => $latestUploaded,
            'categoryPieUri' => $categoryPieUri,
            'departmentBarUri' => $departmentBarUri,
            'deptCategoryCharts' => $deptCategoryCharts,
            'totalDocuments' => array_sum($categoryCount->toArray()),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Document Report - ' . now()->format('Y-m-d') . '.pdf');
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
            if ($valX > ($left + $chartW - 18)) {
                $valX = $left + $chartW - 18;
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
            ->orderBy('total', 'desc')
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
