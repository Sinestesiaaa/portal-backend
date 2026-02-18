<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; margin: 16px; }
        .header { background: #0aa03a; color: #fff; border: 1px solid #087c2d; padding: 10px 12px; margin-bottom: 10px; }
        .title { font-size: 18px; font-weight: 700; margin: 0; }
        .sub { font-size: 10px; margin-top: 4px; opacity: 0.95; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .meta td { border: 1px solid #d1d5db; padding: 6px 8px; }
        .meta .label { width: 140px; background: #f8fafc; font-weight: 700; }
        .section { border: 1px solid #d1d5db; margin-bottom: 8px; }
        .section-title { background: #e8fceb; border-bottom: 1px solid #d1d5db; color: #065f46; font-weight: 700; padding: 6px 8px; }
        .section-body { padding: 6px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid th, .grid td { border: 1px solid #d1d5db; padding: 5px 6px; text-align: left; vertical-align: top; font-size: 10px; }
        .grid th { background: #f3f4f6; font-weight: 700; }
        .right { text-align: right; }
        .chart { width: 100%; height: auto; border: 1px solid #e5e7eb; margin-bottom: 6px; }
        .chart-sm { width: 100%; height: auto; border: 1px solid #e5e7eb; margin-bottom: 4px; }
        .two-col { width: 100%; border-collapse: collapse; }
        .two-col td { width: 50%; vertical-align: top; padding: 4px; border: none; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">Dashboard Report</p>
        <p class="sub">Ringkasan aktivitas dokumen perusahaan</p>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Generated At</td>
            <td>{{ $generatedAt->format('Y-m-d H:i') }}</td>
            <td class="label">Total Dokumen</td>
            <td>{{ $totalDocuments }}</td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Visual Ringkasan</div>
        <div class="section-body">
            <table class="two-col">
                <tr>
                    <td><img src="{{ $categoryPieUri }}" alt="Chart Kategori" class="chart-sm"></td>
                    <td><img src="{{ $departmentBarUri }}" alt="Chart Departemen" class="chart-sm"></td>
                </tr>
            </table>
        </div>
    </div>

    @if (!empty($deptCategoryCharts))
        <div class="section">
            <div class="section-title">Visual per Departemen (Top 6)</div>
            <div class="section-body">
                <table class="two-col">
                    @foreach (array_chunk($deptCategoryCharts, 2) as $row)
                        <tr>
                            @foreach ($row as $chart)
                                <td><img src="{{ $chart['uri'] }}" alt="Chart {{ $chart['name'] }}" class="chart"></td>
                            @endforeach
                            @if (count($row) === 1)
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endif

    <div class="section">
        <div class="section-title">Total per Kategori</div>
        <div class="section-body">
            <table class="grid">
            <thead><tr><th>Kategori</th><th>Total</th></tr></thead>
            <tbody>
                @foreach ($categoryCount as $kategori => $total)
                    <tr><td>{{ $kategori }}</td><td class="right">{{ $total }}</td></tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Departemen Paling Aktif</div>
        <div class="section-body">
            <table class="grid">
            <thead><tr><th>Departemen</th><th>Total</th></tr></thead>
            <tbody>
                @foreach ($topDepartments->take(8) as $dept)
                    <tr><td>{{ $dept->department->name ?? '-' }}</td><td class="right">{{ $dept->total }}</td></tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Dokumen Terbaru (Upload)</div>
        <div class="section-body">
            <table class="grid">
            <thead><tr><th>Nomor Dokumen</th><th>Judul</th><th>Departemen</th><th>Tanggal Upload</th></tr></thead>
            <tbody>
                @foreach (($latestUploaded ?? collect())->take(15) as $doc)
                    <tr>
                        <td>{{ $doc->document_number }}</td>
                        <td>{{ $doc->title }}</td>
                        <td>{{ $doc->department->name ?? '-' }}</td>
                        <td>{{ optional($doc->created_at)->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</body>
</html>
