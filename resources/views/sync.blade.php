<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Monitoring API Sync - Tenant Transaction System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}?v=2">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
        .date-time-box { background-color: #ffffff; padding: 0.75rem 1rem; border-radius: 0.375rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); display: flex; align-items: center; gap: 0.5rem; }
        .date-time-text { color: #4B5563; font-size: 0.9rem; font-weight: 500; }
        .live-clock { font-weight: 600; }
        .filter-container { display: flex; align-items: center; gap: 1rem; }
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
            <a href="{{ route('sync') }}" class="sidebar-item active flex items-center px-4 py-2 text-blue-100 rounded-md hover-scale">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.001 0 01-15.357-2m15.357 2H15" />
                </svg>
                Sync
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 content-area p-6 overflow-auto">
        <!-- Header and Filter -->
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Monitoring API Sync</h1>
            <div class="filter-container">
                <form method="GET" action="{{ route('sync') }}" class="flex items-center gap-2">
                    <div>
                        <label for="start_date" class="date-time-text">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $startDate ?? '' }}" class="border rounded-md p-1">
                    </div>
                    <div>
                        <label for="end_date" class="date-time-text">End Date:</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $endDate ?? '' }}" class="border rounded-md p-1">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                        Filter
                    </button>
                    <a href="{{ route('sync') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition duration-200">
                        Reset
                    </a>
                </form>
            </div>
        </div>

        <!-- Date and Time Display -->
        <div class="flex justify-end mb-4">
            <div class="date-time-box">
                <span class="date-time-text">{{ now()->format('d M Y') }}</span>
                <span class="date-time-text live-clock" id="liveClock"></span>
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

        <!-- Sync Table Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2">Tenant Name</th>
                            <th class="px-4 py-2">Interval</th>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Time</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Total Data</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody id="syncTableBody">
                        @forelse ($syncData as $index => $data)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} border-b">
                                <td class="px-4 py-2">{{ $data['tenant_name'] }}</td>
                                <td class="px-4 py-2">{{ $data['interval'] }}</td>
                                <td class="px-4 py-2">{{ $data['date'] }}</td>
                                <td class="px-4 py-2">{{ $data['time'] }}</td>
                                <td class="px-4 py-2">
                                    <span class="{{ $data['status'] == 'done' ? 'text-green-600' : ($data['status'] == 'on progress' ? 'text-yellow-600' : ($data['status'] == 'fail' ? 'text-red-600' : 'text-gray-600')) }}">
                                        {{ ucfirst($data['status']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <span id="totalData-{{ $data['id'] }}">
                                        {{ $data['total_data'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('detail-transaction', ['tenant' => $data['tenant_name']]) }}" class="detail-btn text-blue-600 hover:text-blue-800 focus:outline-none mr-2 hover-scale">
                                        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View Details
                                    </a>
                                    <button data-id="{{ $data['id'] }}" class="report-btn text-green-600 hover:text-green-800 focus:outline-none hover-scale">
                                        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01" />
                                        </svg>
                                        Report
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-2 text-center text-sm text-gray-600">
                                    No sync data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info and Links -->
            <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
                <span>Showing {{ $syncData->firstItem() ?? 0 }} - {{ $syncData->lastItem() ?? 0 }} of {{ $syncData->total() ?? 0 }} data</span>
                <div class="flex space-x-2">
                    <a href="{{ $syncData->previousPageUrl() }}"
                       class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200 {{ $syncData->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }}">
                        Previous
                    </a>
                    <a href="{{ $syncData->nextPageUrl() }}"
                       class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200 {{ $syncData->hasMorePages() ? '' : 'opacity-50 cursor-not-allowed' }}">
                        Next
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Section -->
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

        // Event listener untuk tombol Report
        document.querySelectorAll('.report-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                generateReport(id);
            });
        });

        // Fungsi untuk menghasilkan report (placeholder)
        function generateReport(id) {
            alert('Generating report for sync ID: ' + id);
        }
    </script>
</body>
</html>