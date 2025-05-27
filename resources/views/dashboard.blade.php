<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - Tenant Transaction System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}?v=2">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        @keyframes fadeIn { 0% { opacity: 0; transform: translateY(20px); } 100% { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { 0% { transform: translateX(-100%); opacity: 0; } 100% { transform: translateX(0); opacity: 1; } }
        .animate-fade-in { animation: fadeIn 0.8s ease-in-out forwards; }
        .animate-slide-in { animation: slideIn 0.6s ease-in-out forwards; }
        .hover-scale { transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out; }
        .hover-scale:hover { transform: scale(1.02); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .sidebar { background: #3B82F6; min-height: 100vh; }
        .sidebar-item { transition: background-color 0.3s ease, color 0.3s ease; }
        .sidebar-item:hover { background-color: rgba(255, 255, 255, 0.1); color: #FFFFFF; }
        .content-area { background: linear-gradient(135deg, #F9FAFB, #E5E7EB); flex-grow: 1; }
        html, body { margin: 0; padding: 0; height: 100vh; overflow: hidden; }
        .chart-container { height: 300px; width: 100%; }
        .date-time-box { background-color: #ffffff; padding: 0.75rem 1rem; border-radius: 0.375rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); display: flex; align-items: center; gap: 0.5rem; }
        .date-time-text { color: #4B5563; font-size: 0.9rem; font-weight: 500; }
        .live-clock { font-weight: 600; }
    </style>
</head>
<body class="flex h-screen">
    <!-- Sidebar -->
    <div class="sidebar w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out animate-slide-in">
        <nav>
            <a href="{{ route('dashboard') }}" class="sidebar-item flex items-center px-4 py-2 text-blue-100 rounded-md hover-scale">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Dashboard
            </a>
            <a href="{{ route('sync') }}" class="sidebar-item flex items-center px-4 py-2 text-blue-100 rounded-md hover-scale">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.001 0 01-15.357-2m15.357 2H15" />
                </svg>
                Sync
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 content-area p-8 overflow-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
            <div class="flex items-center space-x-4">
                <div class="date-time-box">
                    <span class="date-time-text">{{ now()->format('d M Y') }}</span>
                    <span class="date-time-text live-clock" id="liveClock"></span>
                </div>
            </div>
        </div>

        <!-- Messages -->
        @if (session('success'))
            <div class="mb-8 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-base animate-fade-in">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-8 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-base animate-fade-in">
                {{ session('error') }}
            </div>
        @endif
        @if (session('info'))
            <div class="mb-8 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg text-base animate-fade-in">
                {{ session('info') }}
            </div>
        @endif

        <!-- Date Range Filter -->
        <div class="mb-6">
            <form method="GET" action="{{ route('dashboard') }}" class="flex space-x-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="text" name="start_date" id="start_date" value="{{ $startDate }}" class="mt-1 p-2 border rounded-md" placeholder="dd/mm/yyyy">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="text" name="end_date" id="end_date" value="{{ $endDate }}" class="mt-1 p-2 border rounded-md" placeholder="dd/mm/yyyy">
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">Filter</button>
            </form>
        </div>

        <!-- Summary Cards: Total Target Revenue and Total Revenue Sharing -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Total Target Revenue -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-2">Total Target Revenue</h2>
                <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($totalTargetRevenue, 0, ',', '.') }}</p>
            </div>
            <!-- Total Revenue Sharing -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-2">Total Revenue Sharing</h2>
                <p class="text-2xl font-bold flex items-center {{ $totalRevenueSharing < $totalTargetRevenue ? 'text-red-600' : 'text-green-600' }}">
                    Rp {{ number_format($totalRevenueSharing, 0, ',', '.') }}
                    @if ($totalRevenueSharing < $totalTargetRevenue)
                        <svg class="h-6 w-6 ml-2 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-8l-7 7-7-7" />
                        </svg>
                    @else
                        <svg class="h-6 w-6 ml-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7m-7-7v18" />
                        </svg>
                    @endif
                </p>
            </div>
        </div>

        <!-- Tenant Ranking Charts -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Bar Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Bar Chart</h2>
                <div class="chart-container">
                    <canvas id="rankingBarChart"></canvas>
                </div>
            </div>

            <!-- Pie Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Pie Chart</h2>
                <div class="chart-container">
                    <canvas id="rankingPieChart"></canvas>
                </div>
            </div>

            <!-- Line Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Line Chart</h2>
                <div class="chart-container">
                    <canvas id="rankingHistogramChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Revenue Sharing Table -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Ranking Revenue Sharing</h2>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2">Tenant Name</th>
                        <th class="px-4 py-2">Sum(Total Revenue)</th>
                        <th class="px-4 py-2">%Revenue</th>
                        <th class="px-4 py-2">Revenue Sharing</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($revenueShares as $index => $share)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} border-b">
                            <td class="px-4 py-2">{{ $share['tenant_name'] ?? 'N/A' }}</td>
                            <td class="px-4 py-2">Rp {{ number_format($share['total_revenue'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-2">{{ number_format($share['contribution_percentage'] ?? 0, 2) }}%</td>
                            <td class="px-4 py-2">Rp {{ number_format($share['revenue_share'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-center text-sm text-gray-600">
                                No revenue sharing data available or data not processed correctly.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Transaction Table -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Recent Transactions</h2>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2">Tenant Name</th>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Total Amount Net</th>
                        <th class="px-4 py-2">Status Sync</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr class="{{ $loop->index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} border-b">
                            <td class="px-4 py-2">{{ $transaction->tenant_name }}</td>
                            <td class="px-4 py-2">{{ $transaction->date_time_transaction->format('d M Y H:i') }}</td>
                            <td class="px-4 py-2">Rp {{ number_format($transaction->total_amount_net, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 {{ strtoupper($transaction->status_sync) == 'Y' ? 'text-green-600' : 'text-red-600' }}">{{ strtoupper($transaction->status_sync) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-center text-sm text-gray-600">
                                No transactions available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Fungsi untuk memperbarui live clock setiap detik
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timeString = `${hours}:${minutes}:${seconds} WIB`;
            document.getElementById('liveClock').textContent = timeString;
        }

        // Perbarui jam setiap detik
        setInterval(updateClock, 1000);
        updateClock(); // Panggil sekali saat halaman dimuat

        // AJAX call for ranking data
        function loadCharts() {
            fetch('{{ route("dashboard.ranking") }}')
                .then(response => response.json())
                .then(data => {
                    // Bar Chart
                    const barCtx = document.getElementById('rankingBarChart').getContext('2d');
                    new Chart(barCtx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Total Revenue (Bar)',
                                data: data.data,
                                backgroundColor: 'rgba(59, 130, 246, 0.6)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });

                    // Pie Chart
                    const pieCtx = document.getElementById('rankingPieChart').getContext('2d');
                    new Chart(pieCtx, {
                        type: 'pie',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.data,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.6)',
                                    'rgba(54, 162, 235, 0.6)',
                                    'rgba(255, 206, 86, 0.6)',
                                    'rgba(75, 192, 192, 0.6)',
                                    'rgba(153, 102, 255, 0.6)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });

                    // Line Chart
                    const histCtx = document.getElementById('rankingHistogramChart').getContext('2d');
                    new Chart(histCtx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Total Revenue (Line)',
                                data: data.data,
                                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                                borderColor: 'rgba(255, 159, 64, 1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: 'rgba(255, 159, 64, 1)',
                                pointBorderColor: '#fff',
                                pointHoverRadius: 7,
                                pointRadius: 5
                            }]
                        },
                        options: {
                            scales: {
                                x: { title: { display: true, text: 'Tenants' } },
                                y: { beginAtZero: true, title: { display: true, text: 'Revenue' } }
                            },
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                })
                .catch(error => console.error('Error loading charts:', error));
        }

        // Load charts on page load
        window.onload = function() {
            loadCharts();
            updateClock();
        };
    </script>
</body>
</html>