<x-app-layout>

    <div class="max-w-7xl mx-auto px-6 py-8 space-y-6">

        {{-- HEADER --}}
        <div class="p-5 rounded-xl bg-gradient-to-r from-green-600 to-green-400 shadow-lg text-white">
            <h1 class="text-2xl font-bold">📊 Dashboard</h1>
            <p class="text-white/90">Ringkasan aktivitas dokumen</p>
        </div>

        {{-- STATISTIC CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-5 bg-white rounded-xl shadow border">
                <p class="text-xs text-gray-500">Total Dokumen</p>
                <p class="text-3xl font-bold text-green-700">
                    {{ array_sum($categoryCount->toArray()) }}
                </p>
            </div>

            @foreach ($categoryCount as $kat => $total)
                <div class="p-5 bg-white rounded-xl shadow border">
                    <p class="text-xs text-gray-500">{{ $kat }}</p>
                    <p class="text-3xl font-bold text-green-700">{{ $total }}</p>
                </div>
            @endforeach
        </div>

        {{-- MINI CARDS ROW (3 kolom) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- TOP DEPT --}}
            <div class="bg-white p-5 rounded-xl shadow border">
                <h2 class="font-semibold mb-2 text-sm">🏭 Dept Paling Aktif</h2>
                @foreach ($topDepartments as $dept)
                    <div class="flex justify-between text-sm py-1 border-b last:border-none">
                        <span>{{ $dept->department->name }}</span>
                        <span class="font-semibold">{{ $dept->total }}</span>
                    </div>
                @endforeach
            </div>

            {{-- TOP KATEGORI --}}
            <div class="bg-white p-5 rounded-xl shadow border">
                <h2 class="font-semibold mb-2 text-sm">📁 Kategori Terbanyak Update</h2>
                @foreach ($topKategori as $kat)
                    <div class="flex justify-between text-sm py-1 border-b last:border-none">
                        <span>{{ $kat->kategori }}</span>
                        <span class="font-semibold">{{ $kat->total }}</span>
                    </div>
                @endforeach
            </div>

            {{-- LAST UPDATED --}}
            <div class="bg-white p-5 rounded-xl shadow border">
                <h2 class="font-semibold mb-2 text-sm">⏱ Last Updated</h2>

                @forelse($lastUpdated as $doc)
                    <div class="flex justify-between items-center text-sm py-1 border-b last:border-none">

                        <div>
                            <p class="font-semibold">{{ $doc->document_number }}</p>
                            <p class="text-gray-500 text-xs">{{ $doc->department->name }}</p>
                        </div>

                        <p class="text-xs text-gray-500 whitespace-nowrap">
                            {{ $doc->updated_at->diffForHumans() }}
                        </p>
                    </div>
                @empty
                    <p class="text-gray-500">Belum ada update.</p>
                @endforelse
            </div>

        </div>

        {{-- MAIN CHARTS --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="bg-white p-6 rounded-xl shadow-lg border">
                <h2 class="text-lg font-semibold mb-3">Kategori Dokumen</h2>
                <canvas id="kategoriChart" height="230"></canvas>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg border">
                <h2 class="text-lg font-semibold mb-3">Upload per Bulan</h2>
                <canvas id="uploadChart" height="230"></canvas>
            </div>

        </div>
    </div>

    {{-- CHART JS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>

    <script>
        // PIE CHART
        const kategoriLabels = @json($categoryCount->keys());
        const kategoriValues = @json($categoryCount->values());

        new Chart(document.getElementById('kategoriChart'), {
            type: 'pie',
            data: {
                labels: kategoriLabels,
                datasets: [{
                    data: kategoriValues,
                    backgroundColor: ['#16A34A', '#2563EB', '#FB923C', '#000000'],
                }]
            }
        });

        // LINE CHART
        const uploadLabels = @json($uploadPerMonth->pluck('month'));
        const uploadValues = @json($uploadPerMonth->pluck('total'));

        new Chart(document.getElementById('uploadChart'), {
            type: 'line',
            data: {
                labels: uploadLabels,
                datasets: [{
                    label: 'Upload Bulanan',
                    data: uploadValues,
                    borderColor: '#16A34A',
                    borderWidth: 3,
                    tension: 0.4,
                }]
            }
        });
    </script>

</x-app-layout>
