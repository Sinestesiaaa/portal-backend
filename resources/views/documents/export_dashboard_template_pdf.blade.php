<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Dashboard + Daftar Induk Dokumen</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #0f172a;
            margin: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header {
            background: #0aa03a;
            color: #fff;
            border: 1px solid #087c2d;
            padding: 10px 12px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        .sub {
            font-size: 10px;
            margin-top: 4px;
            opacity: 0.95;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .meta td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
        }

        .meta .label {
            width: 140px;
            background: #f8fafc;
            font-weight: 700;
        }

        .section {
            border: 1px solid #d1d5db;
            margin-bottom: 8px;
        }

        .section-title {
            background: #e8fceb;
            border-bottom: 1px solid #d1d5db;
            color: #065f46;
            font-weight: 700;
            padding: 6px 8px;
        }

        .section-body {
            padding: 6px;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
        }

        .grid th,
        .grid td {
            border: 1px solid #d1d5db;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
            font-size: 10px;
        }

        .grid th {
            background: #f3f4f6;
            font-weight: 700;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .chart {
            width: 100%;
            height: auto;
            border: 1px solid #e5e7eb;
            margin-bottom: 6px;
        }

        .chart-sm {
            width: 100%;
            height: auto;
            border: 1px solid #e5e7eb;
            margin-bottom: 4px;
        }

        .two-col {
            width: 100%;
            border-collapse: collapse;
        }

        .two-col td {
            width: 50%;
            vertical-align: top;
            padding: 4px;
            border: none;
        }

        .page-break {
            page-break-before: always;
        }

        .header-green {
            background: #4f7f34;
            color: #fff;
            font-weight: 700;
            text-align: center;
        }

        .bold {
            font-weight: 700;
        }

        .subtitle {
            font-size: 18px;
            font-weight: 700;
            text-align: center;
        }

        .head-title {
            font-size: 22px;
            font-weight: 700;
            text-align: center;
        }

        .plain-line {
            margin-top: 8px;
            margin-bottom: 2px;
            font-size: 13px;
        }

        .plain-label {
            display: inline-block;
            width: 180px;
            font-weight: 700;
        }

        .di-head td {
            border: 1px solid #000;
        }

        .di-meta td {
            border: 1px solid #000 !important;
            font-size: 12px;
            padding: 5px 6px;
        }

        .di-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .di-table th,
        .di-table td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: middle;
            font-size: 10px;
        }

        .di-table th {
            font-size: 9px;
            letter-spacing: .2px;
            word-break: keep-all;
        }

        .di-title {
            font-size: 20px;
            font-weight: 700;
            text-align: center;
        }

        .di-table td {
            overflow-wrap: break-word;
            word-break: normal;
        }

        .di-no {
            text-align: center;
            white-space: nowrap;
        }

        .di-dept,
        .di-jenis,
        .di-issued,
        .di-rev,
        .di-related,
        .di-remarks {
            text-align: center;
        }

        .di-docno {
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
            line-height: 1.2;
            font-size: 9px;
        }

        .di-title-col {
            text-align: left;
            line-height: 1.25;
        }

        .di-location {
            text-align: left;
            line-height: 1.25;
        }
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
            <td>{{ $dashboardTotalDocuments }}</td>
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
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dashboardCategoryCount as $kategori => $total)
                        <tr>
                            <td>{{ $kategori }}</td>
                            <td class="right">{{ $total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Departemen Paling Aktif</div>
        <div class="section-body">
            <table class="grid">
                <thead>
                    <tr>
                        <th>Departemen</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dashboardTopDepartments->take(8) as $dept)
                        <tr>
                            <td>{{ $dept->department->name ?? '-' }}</td>
                            <td class="right">{{ $dept->total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Dokumen Terbaru (Upload)</div>
        <div class="section-body">
            <table class="grid">
                <thead>
                    <tr>
                        <th>Nomor Dokumen</th>
                        <th>Judul</th>
                        <th>Departemen</th>
                        <th>Tanggal Upload</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (($dashboardLatestUploaded ?? collect())->take(15) as $doc)
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

    <div class="page-break"></div>

    @php
        $logoPath = public_path('logo.png');
        $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    @endphp

    <table class="di-head" style="table-layout: fixed;">
        <colgroup>
            <col style="width: 17%;">
            <col style="width: 63%;">
            <col style="width: 20%;">
        </colgroup>
        <tr>
            <td class="center bold" rowspan="2">
                @if ($logoData)
                    <img src="data:image/png;base64,{{ $logoData }}" style="height: 80px; width: 17%;">
                @else
                    PST
                @endif
            </td>
            <td class="subtitle">FORMULIR</td>
            <td style="padding:0;" rowspan="2">
                <table class="di-meta" style="width:100%; border-collapse: collapse;">
                    <tr>
                        <td class="bold">Nomor Dokumen</td>
                        <td class="center">:</td>
                        <td>{{ $meta['header_doc_no'] }}</td>
                    </tr>
                    <tr>
                        <td class="bold">Tanggal Efektif</td>
                        <td class="center">:</td>
                        <td>{{ $meta['header_effective_date'] }}</td>
                    </tr>
                    <tr>
                        <td class="bold">Revisi</td>
                        <td class="center">:</td>
                        <td>{{ $meta['header_revision'] }}</td>
                    </tr>
                    <tr>
                        <td class="bold">Halaman</td>
                        <td class="center">:</td>
                        <td>{{ $meta['header_page'] ?? '1 dari 1' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="di-title">DAFTAR INDUK DOKUMEN</td>
        </tr>
    </table>

    <div class="plain-line"><span class="plain-label">PROJECT</span>: {{ $meta['project'] }}</div>
    <div class="plain-line" style="margin-bottom:10px;"><span class="plain-label">TANGGAL UPDATE</span>:
        {{ $meta['update_date_display'] ?? $meta['update_date'] }}</div>

    <table class="di-table">
        <colgroup>
            <col style="width: 3%;">
            <col style="width: 6%;">
            <col style="width: 6%;">
            <col style="width: 13%;">
            <col style="width: 24%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 4%;">
            <col style="width: 4%;">
            <col style="width: 4%;">
            <col style="width: 4%;">
            <col style="width: 4%;">
            <col style="width: 9%;">
            <col style="width: 3%;">
        </colgroup>
        <tr class="header-green">
            <td rowspan="2">NO</td>
            <td rowspan="2">DEPT</td>
            <td rowspan="2">JENIS</td>
            <td rowspan="2">NOMOR DOKUMEN</td>
            <td rowspan="2">JUDUL DOKUMEN</td>
            <td rowspan="2">DEPT TERKAIT</td>
            <td rowspan="2">ISSUED DATE</td>
            <td colspan="5">TANGGAL STATUS REVISI</td>
            <td rowspan="2">LOKASI<br>PENYIMPANAN</td>
            <td rowspan="2">REMARKS</td>
        </tr>
        <tr class="header-green">
            <td>Revisi 1</td>
            <td>Revisi 2</td>
            <td>Revisi 3</td>
            <td>Revisi 4</td>
            <td>Revisi 5</td>
        </tr>
        @forelse ($rows as $row)
            <tr>
                <td class="di-no">{{ $row['no'] }}</td>
                <td class="di-dept">{{ $row['dept'] }}</td>
                <td class="di-jenis">{{ $row['jenis'] }}</td>
                <td class="di-docno">{{ $row['nomor_dokumen'] }}</td>
                <td class="di-title-col">{{ $row['judul_dokumen'] }}</td>
                <td class="di-related">{{ $row['departemen_terkait'] }}</td>
                <td class="di-issued">{{ $row['issued_date'] }}</td>
                <td class="di-rev">{{ $row['revisi_1'] }}</td>
                <td class="di-rev">{{ $row['revisi_2'] }}</td>
                <td class="di-rev">{{ $row['revisi_3'] }}</td>
                <td class="di-rev">{{ $row['revisi_4'] }}</td>
                <td class="di-rev">{{ $row['revisi_5'] }}</td>
                <td class="di-location">{{ $row['lokasi_penyimpanan'] }}</td>
                <td class="di-remarks">{{ $row['remarks'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="14" class="center">Tidak ada data</td>
            </tr>
        @endforelse
    </table>
</body>

</html>
