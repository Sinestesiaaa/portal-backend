<div class="bg-white p-4 rounded-xl shadow-md border overflow-x-auto">
    <table class="w-full border border-black text-sm table-fixed" style="border-collapse: collapse;">
        <colgroup>
            <col style="width: 15%;">
            <col style="width: 61%;">
            <col style="width: 24%;">
        </colgroup>
        <tr>
            <td class="border border-black text-center align-middle" rowspan="2">
                <img src="{{ asset('logo.png') }}" alt="Logo PST" style="height: 78px; margin: 0 auto;">
            </td>
            <td class="border border-black text-center font-bold leading-tight py-3" style="font-size: 22px;">FORMULIR
            </td>
            <td class="border border-black p-0 align-top overflow-hidden" rowspan="2" style="width: 330px;">
                <table class="w-full leading-tight" style="font-size: 13px; border-collapse: collapse;">
                    <tr>
                        <td class="border-b border-black p-1.5 font-semibold whitespace-nowrap" style="width: 52%;">
                            Nomor Dokumen</td>
                        <td class="border-b border-black p-1.5 text-center" style="width: 6%;">:</td>
                        <td class="border-b border-black p-1.5 whitespace-nowrap">{{ $meta['header_doc_no'] }}</td>
                    </tr>
                    <tr>
                        <td class="border-b border-black p-1.5 font-semibold whitespace-nowrap">Tanggal Efektif</td>
                        <td class="border-b border-black p-1.5 text-center">:</td>
                        <td class="border-b border-black p-1.5 whitespace-nowrap">{{ $meta['header_effective_date'] }}
                        </td>
                    </tr>
                    <tr>
                        <td class="border-b border-black p-1.5 font-semibold whitespace-nowrap">Revisi</td>
                        <td class="border-b border-black p-1.5 text-center">:</td>
                        <td class="border-b border-black p-1.5 whitespace-nowrap">{{ $meta['header_revision'] }}</td>
                    </tr>
                    <tr>
                        <td class="p-1.5 font-semibold whitespace-nowrap">Halaman</td>
                        <td class="p-1.5 text-center">:</td>
                        <td class="p-1.5 whitespace-nowrap">{{ $meta['header_page'] ?? '1 dari 1' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="border border-black text-center font-bold leading-tight py-5 whitespace-nowrap"
                style="font-size: 30px;">DAFTAR
                INDUK DOKUMEN</td>
        </tr>
    </table>

    <div class="mt-4 mb-5 leading-tight" style="font-size: 16px;">
        <div class="flex items-center gap-2 mb-2">
            <span class="font-semibold w-52">PROJECT</span>
            <span>:</span>
            <span>{{ $meta['project'] }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="font-semibold w-52">TANGGAL UPDATE</span>
            <span>:</span>
            <span>{{ $meta['update_date_display'] ?? $meta['update_date'] }}</span>
        </div>
    </div>

    <table class="w-full border border-black text-sm">
        <tr class="bg-[#4f7f34] text-white text-center font-semibold">
            <td class="border border-black p-2" rowspan="2">NO</td>
            <td class="border border-black p-2" rowspan="2">DEPT</td>
            <td class="border border-black p-2" rowspan="2">JENIS</td>
            <td class="border border-black p-2" rowspan="2">NOMOR DOKUMEN</td>
            <td class="border border-black p-2" rowspan="2">JUDUL DOKUMEN</td>
            <td class="border border-black p-2" rowspan="2">DEPT TERKAIT</td>
            <td class="border border-black p-2" rowspan="2">ISSUED DATE</td>
            <td class="border border-black p-2" colspan="5">TANGGAL STATUS REVISI</td>
            <td class="border border-black p-2" rowspan="2">LOKASI PENYIMPANAN</td>
            <td class="border border-black p-2" rowspan="2">REMARKS</td>
        </tr>
        <tr class="bg-[#4f7f34] text-white text-center font-semibold">
            <td class="border border-black p-2">Revisi 1</td>
            <td class="border border-black p-2">Revisi 2</td>
            <td class="border border-black p-2">Revisi 3</td>
            <td class="border border-black p-2">Revisi 4</td>
            <td class="border border-black p-2">Revisi 5</td>
        </tr>
        @forelse ($rows as $row)
            <tr>
                <td class="border border-black p-2 text-center">{{ $row['no'] }}</td>
                <td class="border border-black p-2">{{ $row['dept'] }}</td>
                <td class="border border-black p-2">{{ $row['jenis'] }}</td>
                <td class="border border-black p-2">{{ $row['nomor_dokumen'] }}</td>
                <td class="border border-black p-2">{{ $row['judul_dokumen'] }}</td>
                <td class="border border-black p-2">{{ $row['departemen_terkait'] }}</td>
                <td class="border border-black p-2 text-center">{{ $row['issued_date'] }}</td>
                <td class="border border-black p-2 text-center">{{ $row['revisi_1'] }}</td>
                <td class="border border-black p-2 text-center">{{ $row['revisi_2'] }}</td>
                <td class="border border-black p-2 text-center">{{ $row['revisi_3'] }}</td>
                <td class="border border-black p-2 text-center">{{ $row['revisi_4'] }}</td>
                <td class="border border-black p-2 text-center">{{ $row['revisi_5'] }}</td>
                <td class="border border-black p-2">{{ $row['lokasi_penyimpanan'] }}</td>
                <td class="border border-black p-2">{{ $row['remarks'] }}</td>
            </tr>
        @empty
            @for ($i = 0; $i < 3; $i++)
                <tr>
                    <td class="border border-black p-3"></td>
                    <td class="border border-black p-3"></td>
                    <td class="border border-black p-3"></td>
                    <td class="border border-black p-3"></td>
                    <td class="border border-black p-3"></td>
                    <td class="border border-black p-3"></td>
                    <td class="border border-black p-3"></td>
                    <td class="border border-black p-3"></td>
                    <td class="border border-black p-3"></td>
                    <td class="border border-black p-3"></td>
                    <td class="border border-black p-3"></td>
                    <td class="border border-black p-3"></td>
                    <td class="border border-black p-3"></td>
                    <td class="border border-black p-3"></td>
                </tr>
            @endfor
        @endforelse
    </table>
</div>
