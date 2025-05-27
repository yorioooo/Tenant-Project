<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Transaction - Tenant Transaction System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}?v=2">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @keyframes fadeIn { 0% { opacity: 0; transform: translateY(20px); } 100% { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { 0% { transform: translateX(-100%); opacity: 0; } 100% { transform: translateX(0); opacity: 1; } }
        @keyframes modalPop { 0% { transform: scale(0.8); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        .animate-fade-in { animation: fadeIn 0.8s ease-in-out forwards; }
        .animate-slide-in { animation: slideIn 0.6s ease-in-out forwards; }
        .hover-scale { transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out; }
        .hover-scale:hover { transform: scale(1.02); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .sidebar { background: #3B82F6; min-height: 100vh; }
        .sidebar-item { transition: background-color 0.3s ease, color 0.3s ease; }
        .sidebar-item:hover { background-color: rgba(255, 255, 255, 0.1); color: #FFFFFF; }
        .content-area { background: linear-gradient(135deg, #F9FAFB, #E5E7EB); flex-grow: 1; }
        html, body { margin: 0; padding: 0; height: 100vh; overflow: hidden; }
        .date-time-box { 
            background-color: #ffffff; 
            padding: 0.75rem 1rem; 
            border-radius: 0.375rem; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
        }
        .date-time-text { 
            color: #4B5563; 
            font-size: 0.9rem; 
            font-weight: 500; 
        }
        .live-clock { font-weight: 600; }
        .header-box {
            background-color: #ffffff;
            padding: 1.5rem 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 50%;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 1.5rem;
        }
        .date-interval-box {
            background-color: #ffffff;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-width: 30%;
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 50;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
        }
        .modal-overlay.active {
            visibility: visible;
            opacity: 1;
        }
        .modal-content {
            background: linear-gradient(135deg, #ffffff, #f3f4f6);
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            max-width: 90%;
            width: 700px;
            transform: scale(0.8);
            transition: transform 0.4s ease-in-out, opacity 0.4s ease-in-out;
            border: 1px solid #e5e7eb;
            position: relative;
            overflow-y: auto;
            max-height: 80vh;
        }
        .modal-overlay.active .modal-content {
            transform: scale(1);
            animation: modalPop 0.4s ease-out forwards;
        }
        .modal-content h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1d4ed8;
            margin-bottom: 1.5rem;
            position: relative;
        }
        .modal-content h2::after {
            content: '';
            position: absolute;
            bottom: -0.5rem;
            left: 0;
            width: 50px;
            height: 3px;
            background: #3b82f6;
            border-radius: 2px;
        }
        .modal-content p {
            font-size: 1rem;
            color: #374151;
            line-height: 1.6;
            margin-bottom: 0.75rem;
        }
        .modal-content strong {
            color: #1f2937;
            font-weight: 600;
        }
        .modal-content p.gross-value,
        .modal-content p.discount-value,
        .modal-content p.tax-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: #374151;
        }
        .modal-content p.net-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #374151;
        }
        .modal-content .buttons {
            margin-top: 2rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
        .modal-content button {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .modal-content button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .modal-content .transaction-layout {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            gap: 2rem;
            margin-bottom: 1.5rem;
        }
        .modal-content .left-column {
            flex: 1;
            text-align: left;
            padding: 0.5rem;
        }
        .modal-content .right-column {
            flex: 1;
            text-align: right;
            padding: 0.5rem;
        }
        .confirmation-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 60;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        .confirmation-overlay.active {
            visibility: visible;
            opacity: 1;
        }
        .confirmation-content {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            max-width: 90%;
            width: 400px;
            transform: scale(0.9);
            transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .confirmation-overlay.active .confirmation-content {
            transform: scale(1);
        }
        .confirmation-content p {
            font-size: 1.1rem;
            color: #1f2937;
            margin-bottom: 1.5rem;
        }
        .confirmation-content .buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        .confirmation-content button {
            padding: 0.5rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .confirmation-content button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="flex h-screen">
    <!-- Sidebar Section -->
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

    <!-- Main Content Section -->
    <div class="flex-1 content-area p-6 overflow-auto">
        <!-- Header and Clock -->
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Detail Transaction</h1>
            <div class="flex items-center space-x-4">
                <div class="date-time-box">
                    <span class="date-time-text">{{ now()->format('d M Y') }}</span>
                    <span class="date-time-text live-clock" id="liveClock"></span>
                </div>
            </div>
        </div>

        <!-- Header Box for Tenant Name and Address -->
        @if ($selectedTenant)
            <div class="header-box mb-6">
                <h2 class="text-3xl font-bold text-gray-600">Transactions for {{ $selectedTenant }}</h2>
                <p class="text-lg text-gray-500 mt-2">Address: {{ $address }}</p>
            </div>
        @endif

        <!-- Messages Section -->
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-base animate-fade-in">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-base animate-fade-in">
                {{ session('error') }}
            </div>
        @endif
        @if (session('info'))
            <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg text-base animate-fade-in">
                {{ session('info') }}
            </div>
        @endif

        <!-- Date and Interval Box -->
        @if ($selectedTenant)
            <div class="date-interval-box mb-6">
                <span class="date-time-text">Date: {{ $transactionDate }}</span>
                <span class="date-time-text">Interval: {{ $interval }}</span>
            </div>
        @endif

        <!-- Transaction Table Section -->
        @if ($selectedTenant)
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2">ID Transaction</th>
                                <th class="px-4 py-2">Time</th>
                                <th class="px-4 py-2">Gross</th>
                                <th class="px-4 py-2">Discount</th>
                                <th class="px-4 py-2">Tax</th>
                                <th class="px-4 py-2">Service</th>
                                <th class="px-4 py-2">Net</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2">Cashier</th>
                                <th class="px-4 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $index => $transaction)
                                <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} border-b">
                                    <td class="px-4 py-2">{{ $transaction->transaction_id }}</td>
                                    <td class="px-4 py-2">
                                        {{ $transaction->date_time_transaction ? $transaction->date_time_transaction->format('H:i') : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2 {{ $transaction->total_amount_gross >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format(abs($transaction->total_amount_gross), 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2">Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2">Rp {{ number_format($transaction->tax, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2">Rp {{ number_format($transaction->service, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 {{ $transaction->total_amount_net >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format(abs($transaction->total_amount_net), 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        @if ($transaction->status_sync === 'Y')
                                            <span class="text-green-600">Valid</span>
                                        @elseif ($transaction->status_sync === 'N')
                                            <span class="text-red-600">Invalid</span>
                                        @else
                                            <span class="text-gray-600">Unknown</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">{{ $transaction->cashier ?? 'N/A' }}</td>
                                    <td class="px-4 py-2">
                                        <button onclick="showModal('transactionModal-{{ $transaction->id }}')" class="text-blue-600 hover:text-blue-800 focus:outline-none">
                                            <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View Detail
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal for Transaction Details -->
                                <div id="transactionModal-{{ $transaction->id }}" class="modal-overlay">
                                    <div class="modal-content">
                                        <h2 class="text-xl font-bold text-gray-800 mb-4">Transaction Details</h2>
                                        <div class="transaction-layout">
                                            <div class="left-column">
                                                <p><strong>Tenant Name:</strong> {{ $transaction->tenant_name }}</p>
                                                <p><strong>Address:</strong> {{ $address }}</p>
                                                <p class="gross-value"><strong>Gross:</strong> Rp {{ number_format(abs($transaction->total_amount_gross), 0, ',', '.') }}</p>
                                                <p class="discount-value"><strong>Discount:</strong> Rp {{ number_format($transaction->discount, 0, ',', '.') }}</p>
                                                <p class="tax-value"><strong>Tax:</strong> Rp {{ number_format($transaction->tax, 0, ',', '.') }}</p>
                                            </div>
                                            <div class="right-column">
                                                <p><strong>Date:</strong> {{ $transactionDate }}</p>
                                                <p><strong>Interval:</strong> {{ $interval }}</p>
                                                <p class="net-value"><strong>Net:</strong> Rp {{ number_format(abs($transaction->total_amount_net), 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                        <div class="buttons">
                                            <button onclick="hideModal('transactionModal-{{ $transaction->id }}')" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition duration-200">
                                                Back
                                            </button>
                                            <button onclick="reconcileTransaction('{{ $transaction->id }}')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                                                Reconcile
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Confirmation Modal for Reconcile -->
                                <div id="confirmationModal-{{ $transaction->id }}" class="confirmation-overlay">
                                    <div class="confirmation-content">
                                        <p>Do you want to update this data?</p>
                                        <div class="buttons">
                                            <button onclick="hideModal('confirmationModal-{{ $transaction->id }}')" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                                                No
                                            </button>
                                            <button onclick="confirmReconcile('{{ $transaction->id }}')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                                Yes
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-2 text-center text-sm text-gray-600">
                                        No transactions found for this tenant.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
                        <span>Showing {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() ?? 0 }} transactions</span>
                        <div class="flex space-x-2">
                            <a href="{{ $transactions->appends(['tenant_name' => $selectedTenant])->previousPageUrl() }}"
                               class="px-3 py-1 bg-white border rounded-md hover:bg-gray-100 {{ $transactions->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }}">
                                Previous
                            </a>
                            <a href="{{ $transactions->appends(['tenant_name' => $selectedTenant])->nextPageUrl() }}"
                               class="px-3 py-1 bg-white border rounded-md hover:bg-gray-100 {{ $transactions->hasMorePages() ? '' : 'opacity-50 cursor-not-allowed' }}">
                                Next
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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

        // Fungsi untuk menampilkan modal
        function showModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        // Fungsi untuk menyembunyikan modal
        function hideModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Fungsi untuk menangani tombol Reconcile (menampilkan modal konfirmasi)
        function reconcileTransaction(transactionId) {
            showModal('confirmationModal-' + transactionId);
        }

        // Fungsi untuk konfirmasi reconcile
        function confirmReconcile(transactionId) {
            alert('Reconcile action for transaction ID ' + transactionId + ' will be implemented later.');
            hideModal('confirmationModal-' + transactionId);
            hideModal('transactionModal-' + transactionId);
        }
    </script>
</body>
</html>