<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard Report User</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; margin: 16px; }
        .header { background: #0aa03a; color: #fff; border: 1px solid #087c2d; padding: 10px 12px; margin-bottom: 10px; }
        .title { font-size: 16px; font-weight: 700; margin: 0; }
        .sub { font-size: 10px; margin-top: 4px; opacity: 0.95; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .meta td { border: 1px solid #d1d5db; padding: 6px 8px; }
        .meta .label { width: 140px; background: #f8fafc; font-weight: 700; }
        .section { border: 1px solid #d1d5db; margin-bottom: 10px; }
        .section-title { background: #e8fceb; border-bottom: 1px solid #d1d5db; color: #065f46; font-weight: 700; padding: 6px 8px; }
        .section-body { padding: 8px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid th, .grid td { border: 1px solid #d1d5db; padding: 6px 7px; text-align: left; vertical-align: top; }
        .grid th { background: #f3f4f6; font-weight: 700; }
        .right { text-align: right; }
        .chart { width: 100%; height: auto; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">Dashboard Report - {{ $user->department->name ?? 'Department' }}</p>
        <p class="sub">Ringkasan aktivitas dokumen departemen</p>
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
            <img src="{{ $categoryChartUri }}" alt="Chart Kategori" class="chart">
        </div>
    </div>

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
        <div class="section-title">Dokumen Terbaru</div>
        <div class="section-body">
            <table class="grid">
            <thead><tr><th>Nomor Dokumen</th><th>Judul</th><th>Departemen</th><th>Tanggal Upload</th></tr></thead>
            <tbody>
                @foreach ($latestCreated as $doc)
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
