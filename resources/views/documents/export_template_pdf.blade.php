<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Daftar Induk Dokumen</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: middle;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: 700;
        }

        .header-green {
            background: #4f7f34;
            color: #fff;
            font-weight: 700;
            text-align: center;
        }

        .small {
            font-size: 9px;
        }

        .subtitle {
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            line-height: 1.2;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            line-height: 1.2;
            white-space: normal;
            word-break: normal;
        }

        .meta td {
            font-size: 13px;
            padding: 5px 6px;
            vertical-align: middle;
        }

        .meta-label {
            width: 52%;
            font-weight: 700;
            white-space: nowrap;
        }

        .meta-colon {
            width: 6%;
            text-align: center;
        }

        .meta-value {
            width: 42%;
            white-space: nowrap;
        }

        .head-block td {
            border: 1px solid #000;
        }

        .plain-line {
            margin-top: 8px;
            margin-bottom: 2px;
            font-size: 16px;
        }

        .plain-label {
            display: inline-block;
            width: 180px;
            font-weight: 700;
        }
    </style>
</head>

<body>
    @php
        $logoPath = public_path('logo.png');
        $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    @endphp

    <table class="head-block" style="border-collapse: collapse; table-layout: fixed;">
        <colgroup>
            <col style="width: 17%;">
            <col style="width: 63%;">
            <col style="width: 20%;">
        </colgroup>
        <tr>
            <td class="center bold" rowspan="2" style="width: 17%;">
                @if ($logoData)
                    <img src="data:image/png;base64,{{ $logoData }}" style="height: 80px;">
                @else
                    PST
                @endif
            </td>
            <td class="subtitle" style="padding-top:5px; padding-bottom:5px; width:57%;">FORMULIR</td>
            <td style="padding:0; overflow:hidden;" rowspan="2">
                <table class="meta" style="border-collapse: collapse; width:38%;">
                    <tr>
                        <td class="meta-label">Nomor Dokumen</td>
                        <td class="meta-colon">:</td>
                        <td class="meta-value">{{ $meta['header_doc_no'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Tanggal Efektif</td>
                        <td class="meta-colon">:</td>
                        <td class="meta-value">{{ $meta['header_effective_date'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Revisi</td>
                        <td class="meta-colon">:</td>
                        <td class="meta-value">{{ $meta['header_revision'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Halaman</td>
                        <td class="meta-colon">:</td>
                        <td class="meta-value">{{ $meta['header_page'] ?? '1 dari 1' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="title" style="padding-top:14px; padding-bottom:14px;">DAFTAR INDUK DOKUMEN</td>
        </tr>
    </table>

    <div class="plain-line"><span class="plain-label">PROJECT</span>: {{ $meta['project'] }}</div>
    <div class="plain-line" style="margin-bottom:14px;"><span class="plain-label">TANGGAL UPDATE</span>:
        {{ $meta['update_date_display'] ?? $meta['update_date'] }}</div>

    <table>
        <tr class="header-green">
            <td rowspan="2">NO</td>
            <td rowspan="2">DEPT</td>
            <td rowspan="2">JENIS</td>
            <td rowspan="2">NOMOR DOKUMEN</td>
            <td rowspan="2">JUDUL DOKUMEN</td>
            <td rowspan="2">DEPT TERKAIT</td>
            <td rowspan="2">ISSUED DATE</td>
            <td colspan="5">TANGGAL STATUS REVISI</td>
            <td rowspan="2">LOKASI PENYIMPANAN</td>
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
                <td class="center">{{ $row['no'] }}</td>
                <td>{{ $row['dept'] }}</td>
                <td>{{ $row['jenis'] }}</td>
                <td>{{ $row['nomor_dokumen'] }}</td>
                <td>{{ $row['judul_dokumen'] }}</td>
                <td>{{ $row['departemen_terkait'] }}</td>
                <td class="center">{{ $row['issued_date'] }}</td>
                <td class="center">{{ $row['revisi_1'] }}</td>
                <td class="center">{{ $row['revisi_2'] }}</td>
                <td class="center">{{ $row['revisi_3'] }}</td>
                <td class="center">{{ $row['revisi_4'] }}</td>
                <td class="center">{{ $row['revisi_5'] }}</td>
                <td>{{ $row['lokasi_penyimpanan'] }}</td>
                <td>{{ $row['remarks'] }}</td>
            </tr>
        @empty
            @for ($i = 0; $i < 3; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        @endforelse
    </table>
</body>

</html>
