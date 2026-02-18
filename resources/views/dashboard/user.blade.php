<x-app-layout>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-10">

        {{-- HEADER --}}
        <div class="p-6 sm:p-8 rounded-2xl bg-gradient-to-r from-[#0AA03A] to-[#16A34A] shadow-lg text-white">
            <h1 class="text-2xl sm:text-3xl font-bold tracking-wide">
                Dashboard — {{ auth()->user()->department->name }}
            </h1>
            <p class="text-white/90 text-sm">Ringkasan aktivitas dokumen Anda</p>
        </div>


        {{-- ===================================================== --}}
        {{-- STAT CARDS --}}
        {{-- ===================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            {{-- Total Dokumen --}}
            <div class="p-6 rounded-xl shadow-md border border-[#0AA03A]/30 bg-[#E8FCEB] flex items-center gap-4">
                <div class="w-12 h-12 bg-[#0AA03A]/20 rounded-full flex items-center justify-center">
                    <span class="text-[#0AA03A] text-2xl">📄</span>
                </div>

                <div>
                    <p class="text-xs text-green-800 font-medium">Total Dokumen</p>
                    <p class="text-3xl font-bold text-[#087C2D]">{{ $totalDocuments }}</p>
                </div>
            </div>

            {{-- Per kategori --}}
            @foreach ($categoryCount as $kat => $total)
                <div class="p-6 rounded-xl shadow-md border border-[#0AA03A]/20 bg-white flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#0AA03A]/15 rounded-full flex items-center justify-center">
                        <span class="text-[#0AA03A] text-xl">📁</span>
                    </div>

                    <div>
                        <p class="text-xs text-gray-600 font-medium">{{ strtoupper($kat) }}</p>
                        <p class="text-3xl font-bold text-[#0AA03A]">{{ $total }}</p>
                    </div>
                </div>
            @endforeach

        </div>



        {{-- ===================================================== --}}
        {{-- CHART + LAST UPDATES --}}
        {{-- ===================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

            {{-- BAR CHART --}}
            <div class="bg-white rounded-xl shadow-md border border-gray-200">
                <div class="bg-[#0AA03A] text-white px-4 py-3 rounded-t-xl font-semibold">
                    Dokumen per Kategori
                </div>

                <div class="p-6">
                    <canvas id="categoryBarChart" height="300"></canvas>
                </div>
            </div>


            {{-- LAST CREATED DOCUMENTS --}}
            <div class="bg-white rounded-xl shadow-md border border-gray-200">
                <div class="bg-[#0AA03A] text-white px-4 py-3 rounded-t-xl font-semibold">
                    Dokumen Terbaru
                </div>

                <div class="p-6 space-y-4">

                    @forelse ($latestCreated as $doc)
                        <div
                            class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2 p-3 bg-gray-50 rounded-lg border hover:bg-gray-100">

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
                                    <p class="font-semibold text-gray-800">{{ $doc->document_number }}</p>
                                    <p class="text-xs text-gray-500 w-40 sm:w-52 truncate">{{ $doc->title }}</p>
                                </div>
                            </div>

                            <span class="text-xs text-gray-500 sm:whitespace-nowrap">
                                {{ $doc->created_at->diffForHumans() }}
                            </span>

                        </div>
                        @empty
                            <p class="text-gray-500 text-sm">Belum ada dokumen.</p>
                        @endforelse

                    </div>
                </div>

            </div>

        </div>


        {{-- CHART SCRIPT --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            const labels = @json($categoryCount->keys());
            const values = @json($categoryCount->values());

            new Chart(document.getElementById('categoryBarChart'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        data: values,
                        backgroundColor: ['#000000', '#0AA03A', '#EA580C', '#2563EB'],
                        borderRadius: 8,
                    }]
                },
                options: {
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
