<x-app-layout>

    <div class="max-w-7xl mx-auto px-6 py-10">

        <h1 class="text-3xl font-bold text-gray-800 mb-8">
            📊 Dashboard — {{ auth()->user()->department->name }} (User)
        </h1>

        {{-- ========================= --}}
        {{-- 1. STATISTIC CARDS --}}
        {{-- ========================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <div class="p-5 bg-white rounded-xl shadow-lg border">
                <h3 class="text-sm text-gray-500">Total Dokumen</h3>
                <p class="text-3xl font-bold text-green-600 mt-2">
                    {{ $totalDocuments }}
                </p>
            </div>

            @foreach ($categoryCount as $kat => $total)
                <div class="p-5 bg-white rounded-xl shadow-lg border">
                    <h3 class="text-sm text-gray-500">{{ $kat }}</h3>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $total }}</p>
                </div>
            @endforeach

        </div>



        {{-- ========================= --}}
        {{-- 2. CHART + COMPACT LAST UPDATE --}}
        {{-- ========================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-10">

            {{-- GRAFIK PER KATEGORI --}}
            <div class="bg-white p-6 rounded-xl shadow-lg border">
                <h2 class="text-lg font-semibold mb-4">Dokumen per Kategori</h2>
                <canvas id="categoryBarChart" height="140"></canvas>
            </div>


            {{-- COMPACT LAST UPDATE --}}
            <div class="bg-white p-6 rounded-xl shadow-lg border">
                <h2 class="text-lg font-semibold mb-4">Last Updated</h2>

                @foreach ($documents->take(5) as $doc)
                    <div class="flex justify-between items-start border-b py-3">

                        <div class="flex items-center gap-3">

                            {{-- Icon --}}
                            <div class="text-2xl">
                                @switch($doc->kategori)
                                    @case('SOP')
                                        📄
                                    @break

                                    @case('FORM')
                                        📘
                                    @break

                                    @case('IK')
                                        📝
                                    @break

                                    @case('STD')
                                        📚
                                    @break

                                    @default
                                        📄
                                @endswitch
                            </div>

                            <div>
                                <p class="font-semibold">{{ $doc->document_number }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $doc->title }}
                                </p>
                            </div>

                        </div>

                        <div class="text-xs text-gray-500 whitespace-nowrap">
                            {{ $doc->updated_at->diffForHumans() }}
                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    </div>



    {{-- ========================= --}}
    {{-- CHART SCRIPT --}}
    {{-- ========================= --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const labels = @json($categoryCount->keys());
        const values = @json($categoryCount->values());

        new Chart(document.getElementById('categoryBarChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: "Jumlah Dokumen",
                    data: values,
                    backgroundColor: ['#16A34A', '#2563EB', '#FB923C', '#000000'],
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

</x-app-layout>
