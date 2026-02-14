<x-app-layout>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-10">

        {{-- HEADER CARD --}}
        <div class="p-6 sm:p-8 rounded-2xl bg-gradient-to-r from-[#0AA03A] to-[#16A34A] shadow-lg text-white">
            <h1 class="text-2xl sm:text-3xl font-bold tracking-wide">📊 Dashboard</h1>
            <p class="text-white/90 text-sm">Ringkasan aktivitas dokumen perusahaan</p>
        </div>


        {{-- ========================================= --}}
        {{-- STAT CARDS --}}
        {{-- ========================================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            {{-- TOTAL --}}
            <div class="p-6 rounded-xl shadow-md border border-[#0AA03A]/30 bg-[#E8FCEB]">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#0AA03A]/20 rounded-full flex items-center justify-center">
                        <span class="text-[#0AA03A] text-xl">📄</span>
                    </div>
                    <div>
                        <p class="text-xs text-green-800 font-medium">Total Dokumen</p>
                        <p class="text-3xl font-bold text-[#087C2D]">
                            {{ array_sum($categoryCount->toArray()) }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- PER KATEGORI --}}
            @foreach ($categoryCount as $kat => $total)
                <a href="{{ route('documents.index', ['kategori' => $kat]) }}"
                    class="p-6 rounded-xl shadow-md border border-[#0AA03A]/20 bg-white hover:bg-[#F3FAF6]">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-[#0AA03A]/15 rounded-full flex items-center justify-center">
                            <span class="text-[#0AA03A] text-xl">📁</span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 font-medium">{{ strtoupper($kat) }}</p>
                            <p class="text-3xl font-bold text-[#0AA03A]">{{ $total }}</p>
                        </div>
                    </div>
                </a>
            @endforeach

        </div>



        {{-- ========================================= --}}
        {{-- TOP LIST PANELS --}}
        {{-- ========================================= --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- TOP DEPARTEMEN --}}
            <div class="rounded-xl shadow-md border border-gray-200 overflow-hidden bg-white">
                <div class="bg-[#0AA03A] text-white px-4 py-3 text-sm font-semibold">
                    🏭 Departemen Paling Aktif
                </div>

                <div class="p-5 space-y-3 max-h-[320px] overflow-y-auto pr-2">
                    @foreach ($topDepartments as $dept)
                        <a href="{{ route('documents.index', ['department_id' => $dept->department_id]) }}"
                            class="flex justify-between items-center p-2 rounded-lg bg-[#E8FCEB] border border-[#B7EFC2] hover:bg-[#D9F7E1]">
                            <span class="text-gray-800 text-sm">{{ $dept->department->name }}</span>
                            <span class="text-[#087C2D] font-semibold">{{ $dept->total }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- TOP KATEGORI --}}
            <div class="rounded-xl shadow-md border border-gray-200 overflow-hidden bg-white">
                <div class="bg-[#0AA03A] text-white px-4 py-3 text-sm font-semibold">
                    📁 Kategori Terbanyak Update
                </div>

                <div class="p-5 space-y-3">
                    @foreach ($topKategori as $kat)
                        <a href="{{ route('documents.index', ['kategori' => $kat->kategori]) }}"
                            class="flex justify-between items-center p-2 rounded-lg bg-[#E8FCEB] border border-[#B7EFC2] hover:bg-[#D9F7E1]">
                            <span class="text-gray-800 text-sm">{{ $kat->kategori }}</span>
                            <span class="text-[#087C2D] font-semibold">{{ $kat->total }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- LAST UPDATED --}}
            <div class="rounded-xl shadow-md border border-gray-200 overflow-hidden bg-white">
                <div class="bg-[#0AA03A] text-white px-4 py-3 text-sm font-semibold">
                    ⏱ Update Terakhir
                </div>

                <div class="p-5 space-y-3">
                    @forelse ($lastUpdated as $doc)
                        <a href="{{ route('documents.show', $doc->id) }}"
                            class="flex justify-between items-center p-2 rounded-lg bg-gray-50 border border-gray-200 hover:bg-gray-100">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $doc->document_number }}</p>
                                <p class="text-xs text-gray-500">{{ $doc->department->name }}</p>
                            </div>
                            <span class="text-gray-400 text-xs">{{ $doc->updated_at->diffForHumans() }}</span>
                        </a>
                    @empty
                        <p class="text-gray-500 text-sm">Belum ada update.</p>
                    @endforelse
                </div>
            </div>

        </div>



        {{-- ========================================= --}}
        {{-- MAIN CHARTS (RAPIH & KECIL) --}}
        {{-- ========================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Pie Chart --}}
            <div class="bg-white rounded-xl shadow-md border border-gray-200">
                <div class="bg-[#0AA03A] text-white px-4 py-3 rounded-t-xl font-semibold">
                    Kategori Dokumen
                </div>
                <div class="p-4 flex justify-center items-center h-64">
                    <canvas id="kategoriChart" height="80"></canvas>
                </div>
            </div>

            {{-- Line Chart --}}
            <div class="bg-white rounded-xl shadow-md border border-gray-200">
                <div class="bg-[#0AA03A] text-white px-4 py-3 rounded-t-xl font-semibold">
                    Upload per Bulan
                </div>
                <div class="p-4 flex justify-center items-center h-64">
                    <canvas id="uploadChart" height="80"></canvas>
                </div>
            </div>

        </div>



        {{-- ========================================= --}}
        {{-- HORIZONTAL BAR — TOTAL PER DEPARTEMEN --}}
        {{-- ========================================= --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200">
            <div class="bg-[#0AA03A] text-white px-4 py-3 rounded-t-xl font-semibold">
                Perbandingan Total Dokumen per Departemen
            </div>
            <div class="p-4 flex justify-center items-center h-72">
                <canvas id="deptTotalChart"></canvas>
            </div>
        </div>



        {{-- ========================================= --}}
        {{-- PER-DEPARTEMEN CATEGORY CHARTS (3/COL) --}}
        {{-- ========================================= --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            @foreach ($deptCategoryData as $deptName => $values)
                <div class="bg-white rounded-xl shadow-md border border-gray-200">

                    <div class="bg-[#0AA03A] text-white px-3 py-2 rounded-t-xl text-xs font-semibold text-center">
                        {{ $deptName }} — Kategori
                    </div>

                    <div class="p-4 flex justify-center items-center h-40">
                        <canvas id="chart-{{ Str::slug($deptName) }}"></canvas>
                    </div>

                </div>
            @endforeach

        </div>

    </div>


    {{-- ========================================= --}}
    {{-- SCRIPTS --}}
    {{-- ========================================= --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>

    <script>
        // PIE CHART
        new Chart(document.getElementById('kategoriChart'), {
            type: 'pie',
            data: {
                labels: @json($categoryCount->keys()),
                datasets: [{
                    data: @json($categoryCount->values()),
                    backgroundColor: ['#000000', '#EA580C', '#2563EB', '0AA03A']
                }]
            },
            options: {
                maintainAspectRatio: false
            }
        });

        // LINE CHART
        new Chart(document.getElementById('uploadChart'), {
            type: 'line',
            data: {
                labels: @json($uploadPerMonth->pluck('month')),
                datasets: [{
                    label: 'Upload Bulanan',
                    data: @json($uploadPerMonth->pluck('total')),
                    borderColor: '#0AA03A',
                    backgroundColor: 'rgba(10,160,58,0.20)',
                    borderWidth: 3,
                    tension: 0.35
                }]
            },
            options: {
                maintainAspectRatio: false
            }
        });

        // TOTAL PER DEPARTEMEN (HORIZONTAL)
        new Chart(document.getElementById('deptTotalChart'), {
            type: 'bar',
            data: {
                labels: @json($departmentTotals->pluck('department.name')),
                datasets: [{
                    label: 'Total Dokumen',
                    data: @json($departmentTotals->pluck('total')),
                    backgroundColor: '#0AA03A',
                }]
            },
            options: {
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });


        // MANY SMALL CHARTS PER DEPARTMENT
        @foreach ($deptCategoryData as $deptName => $data)
            new Chart(document.getElementById('chart-{{ Str::slug($deptName) }}'), {
                type: 'bar',
                data: {
                    labels: ['SOP', 'IK', 'FORM', 'STD'],
                    datasets: [{
                        data: [
                            {{ $data['SOP'] }},
                            {{ $data['IK'] }},
                            {{ $data['FORM'] }},
                            {{ $data['STD'] }},
                        ],
                        backgroundColor: ['#EA580C', '#0AA03A', '#000000', '#2563EB'],
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endforeach
    </script>

</x-app-layout>


