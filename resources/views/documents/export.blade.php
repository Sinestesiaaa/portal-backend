<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Export Dokumen</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 16px; margin-bottom: 6px; }
        .meta { font-size: 11px; color: #6b7280; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
        th { background: #E8FCEB; text-align: left; }
    </style>
</head>
<body>
    @php
        $baseParent = dirname(base_path());
        $logoCandidates = [
            public_path('logo.png'),
            public_path('images/logo.png'),
            public_path('img/logo.png'),
            public_path('assets/logo.png'),
            base_path('public/logo.png'),
            base_path('public/images/logo.png'),
            base_path('public_html/logo.png'),
            base_path('public_html/images/logo.png'),
            $baseParent . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'logo.png',
            $baseParent . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'logo.png',
        ];
        $logoData = null;
        foreach ($logoCandidates as $fullPath) {
            if (!is_file($fullPath)) {
                continue;
            }
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'svg' => 'image/svg+xml',
                default => 'image/png',
            };
            $logoData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
            break;
        }
    @endphp

    <table style="width: 100%; margin-bottom: 8px;">
        <tr>
            <td style="width: 80px;">
                @if ($logoData)
                    <img src="{{ $logoData }}" style="height: 48px;">
                @endif
            </td>
            <td>
                <h1 style="margin: 0;">Daftar Dokumen</h1>
                <div class="meta">Generated: {{ $generatedAt }}</div>
            </td>
        </tr>
    </table>

    @php
        $labels = [
            'department' => 'Departemen',
            'site' => 'Site',
            'kategori' => 'Kategori',
            'document_number' => 'Nomor Dokumen',
            'title' => 'Judul',
            'revision_number' => 'Revisi',
            'last_revision_at' => 'Tgl Revisi Terakhir',
            'published_at' => 'Tgl Terbit',
            'review_date' => 'Review Berikutnya',
            'file_path' => 'File',
        ];
        $cols = $columns ?? array_keys($labels);
    @endphp

    <table>
        <thead>
            <tr>
                @foreach ($cols as $c)
                    <th>{{ $labels[$c] ?? $c }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($documents as $doc)
                <tr>
                    @foreach ($cols as $c)
                        @if ($c === 'department')
                            <td>{{ $doc->department->name ?? '-' }}</td>
                        @elseif ($c === 'kategori')
                            <td>{{ $doc->kategori }}</td>
                        @elseif ($c === 'site')
                            <td>{{ $doc->site->name ?? '-' }}</td>
                        @elseif ($c === 'document_number')
                            <td>{{ $doc->document_number }}</td>
                        @elseif ($c === 'title')
                            <td>{{ $doc->title }}</td>
                        @elseif ($c === 'revision_number')
                            <td>Rev. {{ $doc->revision_number ?? 0 }}</td>
                        @elseif ($c === 'last_revision_at')
                            <td>{{ $doc->last_revision_at?->format('Y-m-d') ?? '' }}</td>
                        @elseif ($c === 'published_at')
                            <td>{{ $doc->published_at?->format('Y-m-d') ?? '' }}</td>
                        @elseif ($c === 'review_date')
                            <td>{{ $doc->review_date?->format('Y-m-d') ?? '' }}</td>
                        @elseif ($c === 'file_path')
                            <td>{{ $doc->file_path }}</td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
