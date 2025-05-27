<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\RevenueTarget;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class TransactionDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        try {
            // Ambil start_date dan end_date dari request, default ke tanggal awal dan akhir bulan saat ini jika tidak ada
            $startDate = $request->input('start_date', now()->startOfMonth()->format('d/m/Y'));
            $endDate = $request->input('end_date', now()->endOfMonth()->format('d/m/Y'));

            // Konversi ke format yang bisa digunakan oleh Carbon
            $start = Carbon::createFromFormat('d/m/Y', $startDate)->startOfDay();
            $end = Carbon::createFromFormat('d/m/Y', $endDate)->endOfDay();
            Log::info('Filter Date Range: Start - ' . $start->toDateTimeString() . ' | End - ' . $end->toDateTimeString());

            // Log semua transaksi untuk memverifikasi data di database
            $allTransactions = Transaction::all();
            Log::info('All Transactions in Database: ', $allTransactions->toArray());

            // Query transaksi berdasarkan rentang tanggal dan status_sync
            $transactionsQuery = Transaction::whereRaw('UPPER(status_sync) = ?', ['Y'])
                ->whereBetween('date_time_transaction', [$start, $end]);
            $transactionCount = $transactionsQuery->count();
            Log::info('Transaction Count with status_sync=Y in date range: ' . $transactionCount);

            $transactions = (clone $transactionsQuery)
                ->select('id', 'tenant_name', 'address', 'date_transaction', 'interval', 'date_time_transaction', 'transaction_id', 'total_amount_net', 'total_amount_gross', 'discount', 'tax', 'service', 'status_sync', 'cashier')
                ->orderByDesc('date_time_transaction')
                ->paginate(10);
            Log::info('Paginated Transactions: ', $transactions->toArray());

            // Hitung Total Revenue Sharing berdasarkan rentang tanggal
            $totalRevenueSharing = (clone $transactionsQuery)
                ->sum('total_amount_net');
            Log::info('Total Revenue Sharing: ' . $totalRevenueSharing);

            $rawShares = (clone $transactionsQuery)
                ->select('tenant_name')
                ->selectRaw('SUM(total_amount_net) as total_revenue')
                ->groupBy('tenant_name')
                ->orderByDesc('total_revenue')
                ->get();
            Log::info('Raw Shares Data: ', $rawShares->toArray());

            $totalRevenueAll = $rawShares->sum('total_revenue');
            Log::info('Total Revenue All: ' . $totalRevenueAll);

            // Hitung Total Target Revenue berdasarkan rentang tanggal
            $totalTargetRevenue = RevenueTarget::where('flag', 'Y')
                ->where(function ($query) use ($start, $end) {
                    $query->where('periode', $start->year)
                          ->whereBetween('bulan', [$start->month, $end->month]);
                })
                ->sum('revenue_target');
            Log::info('Total Target Revenue: ' . $totalTargetRevenue);

            // Proses Revenue Shares dan ubah menjadi paginasi
            $revenueSharesCollection = $rawShares->map(function ($share) use ($totalRevenueAll, $totalTargetRevenue) {
                $contributionPercentage = $totalRevenueAll != 0
                    ? ($share->total_revenue / $totalRevenueAll) * 100
                    : 0;

                $revenueShare = $contributionPercentage != 0 && $totalTargetRevenue != 0
                    ? ($contributionPercentage / 100) * $totalTargetRevenue
                    : 0;

                return [
                    'tenant_name' => $share->tenant_name,
                    'total_revenue' => $share->total_revenue,
                    'contribution_percentage' => round($contributionPercentage, 2),
                    'revenue_share' => round($revenueShare, 2),
                ];
            });

            // Ubah koleksi menjadi LengthAwarePaginator
            $perPage = 10;
            $currentPage = LengthAwarePaginator::resolveCurrentPage('revenue_shares_page');
            $currentItems = $revenueSharesCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $revenueShares = new LengthAwarePaginator($currentItems, $revenueSharesCollection->count(), $perPage, $currentPage, [
                'path' => route('dashboard'),
                'pageName' => 'revenue_shares_page',
            ]);
            Log::info('Processed Revenue Shares: ', $revenueShares->toArray());

            return view('dashboard', compact(
                'transactions',
                'revenueShares',
                'totalRevenueSharing',
                'totalTargetRevenue',
                'startDate',
                'endDate'
            ));

        } catch (\Exception $e) {
            Log::error('Dashboard Error: ' . $e->getMessage());
            Log::error('Stack Trace: ' . $e->getTraceAsString());

            return view('dashboard', [
                'transactions' => new LengthAwarePaginator([], 0, 10),
                'revenueShares' => new LengthAwarePaginator([], 0, 10),
                'totalRevenueSharing' => 0,
                'totalTargetRevenue' => 0,
                'startDate' => now()->startOfMonth()->format('d/m/Y'),
                'endDate' => now()->endOfMonth()->format('d/m/Y'),
            ])->with('error', 'Failed to load dashboard. Please try again later.');
        }
    }

    public function getRanking()
    {
        try {
            $rankings = Transaction::whereRaw('UPPER(status_sync) = ?', ['Y'])
                ->select('tenant_name')
                ->selectRaw('SUM(total_amount_net) as total_revenue')
                ->groupBy('tenant_name')
                ->orderByDesc('total_revenue')
                ->take(5)
                ->get();

            return response()->json([
                'labels' => $rankings->pluck('tenant_name'),
                'data' => $rankings->pluck('total_revenue')
            ]);

        } catch (\Exception $e) {
            Log::error('getRanking Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load ranking data'], 500);
        }
    }

    public function detailTransaction(Request $request)
    {
        try {
            // Ambil tenant dari query parameter (dari sync.blade.php)
            $selectedTenant = $request->query('tenant');

            // Inisialisasi transaksi kosong dan alamat default
            $transactions = new LengthAwarePaginator([], 0, 10);
            $address = 'Address not available';

            // Jika tenant ada, ambil transaksi dan alamat untuk tenant tersebut
            if ($selectedTenant) {
                $transactions = Transaction::where('tenant_name', $selectedTenant)
                    ->select('id', 'date_time_transaction', 'transaction_id', 'total_amount_gross', 'total_amount_net', 'discount', 'tax', 'service', 'status_sync', 'cashier')
                    ->orderByDesc('date_time_transaction')
                    ->paginate(10);
                Log::info('Transactions for tenant ' . $selectedTenant . ': ', $transactions->toArray());

                // Ambil alamat dari transaksi pertama yang sesuai dengan tenant_name
                $firstTransaction = Transaction::where('tenant_name', $selectedTenant)
                    ->select('address', 'date_transaction', 'interval')
                    ->orderByDesc('date_time_transaction') // Ambil yang terbaru
                    ->first();
                $address = $firstTransaction ? $firstTransaction->address : 'Address not available';
                $transactionDate = $firstTransaction && $firstTransaction->date_transaction ? $firstTransaction->date_transaction->format('d M Y') : 'N/A';
                $interval = $firstTransaction && $firstTransaction->interval ? $firstTransaction->interval : 'N/A';
            } else {
                $transactionDate = 'N/A';
                $interval = 'N/A';
            }

            return view('detail-transaction', compact('transactions', 'selectedTenant', 'address', 'transactionDate', 'interval'));

        } catch (\Exception $e) {
            Log::error('Detail Transaction Error: ' . $e->getMessage());
            return view('detail-transaction', [
                'transactions' => new LengthAwarePaginator([], 0, 10),
                'selectedTenant' => null,
                'address' => 'Address not available',
                'transactionDate' => 'N/A',
                'interval' => 'N/A'
            ])->with('error', 'Failed to load detail transaction. Please try again later.');
        }
    }

    public function index()
    {
        try {
            $transactions = Transaction::whereRaw('UPPER(status_sync) = ?', ['Y'])
                ->select('id', 'tenant_name', 'address', 'date_transaction', 'interval', 'date_time_transaction', 'transaction_id', 'total_amount_net', 'total_amount_gross', 'discount', 'tax', 'service', 'status_sync', 'cashier')
                ->orderByDesc('date_time_transaction')
                ->paginate(10);

            return view('transaction_list', compact('transactions'));

        } catch (\Exception $e) {
            Log::error('Index Error: ' . $e->getMessage());
            return redirect()->route('transactions')->with('error', 'Failed to load transactions.');
        }
    }

    public function show($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            return view('transaction_detail', compact('transaction'));

        } catch (\Exception $e) {
            Log::error('Show Error: ' . $e->getMessage());
            return redirect()->route('transactions')->with('error', 'Transaction not found.');
        }
    }

    public function delete($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            $transaction->delete();
            return redirect()->back()->with('success', 'Transaction deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Delete Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete transaction.');
        }
    }

    public function sync(Request $request, $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            $response = Http::get('https://api.example.com/sync', [
                'transaction_id' => $transaction->transaction_id,
            ]);

            if ($response->successful()) {
                $transaction->update(['status_sync' => 'Y']);
                return response()->json(['success' => 'Transaction synced successfully.']);
            }

            return response()->json(['error' => 'Failed to sync transaction.'], 500);
        } catch (\Exception $e) {
            Log::error('Sync Error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred during sync.'], 500);
        }
    }

    public function reconcile(Request $request, $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            // Logika untuk reconcile, misalnya mengubah status_sync atau tindakan lain
            $transaction->update(['status_sync' => 'Y']);
            return response()->json(['success' => 'Transaction reconciled successfully.']);
        } catch (\Exception $e) {
            Log::error('Reconcile Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to reconcile transaction.'], 500);
        }
    }

    public function syncMonitoring(Request $request)
    {
        try {
            // Ambil start_date dan end_date dari request, default ke null jika tidak ada
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Konversi ke format Carbon jika ada
            $start = $startDate ? Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay() : null;
            $end = $endDate ? Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay() : null;
            Log::info('Filter Date Range: Start - ' . ($start ? $start->toDateTimeString() : 'N/A') . ' | End - ' . ($end ? $end->toDateTimeString() : 'N/A'));

            // Query untuk mengambil semua transaksi
            $transactionsQuery = Transaction::select('id', 'tenant_name', 'interval', 'date_transaction', 'date_time_transaction', 'status_sync', 'transaction_id')
                ->orderBy('date_time_transaction', 'asc');

            // Terapkan filter tanggal jika ada
            if ($start && $end) {
                $transactionsQuery->whereBetween('date_transaction', [$start, $end]);
            } elseif ($start) {
                $transactionsQuery->where('date_transaction', '>=', $start);
            } elseif ($end) {
                $transactionsQuery->where('date_transaction', '<=', $end);
            }

            $transactions = $transactionsQuery->get();

            // Hitung total_data untuk setiap transaksi berdasarkan jumlah transaksi hingga waktu tersebut
            $syncDataCollection = $transactions->map(function ($transaction) {
                // Hitung jumlah transaksi hingga waktu transaksi ini (termasuk transaksi ini)
                $totalData = Transaction::where('date_time_transaction', '<=', $transaction->date_time_transaction)
                    ->count();

                return [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id,
                    'tenant_name' => $transaction->tenant_name,
                    'interval' => $transaction->interval,
                    'date' => $transaction->date_transaction ? $transaction->date_transaction->format('d M Y') : 'N/A',
                    'time' => $transaction->date_time_transaction ? $transaction->date_time_transaction->format('H:i') : 'N/A',
                    'status' => strtoupper($transaction->status_sync) == 'Y' ? 'done' : 'fail',
                    'total_data' => $totalData,
                    'date_time_transaction' => $transaction->date_time_transaction,
                ];
            });

            // Urutkan kembali dari terbaru ke terlama untuk ditampilkan
            $syncDataCollection = $syncDataCollection->sortByDesc('date_time_transaction')->values();

            // Konversi ke paginasi dengan 10 item per halaman
            $perPage = 10;
            $currentPage = LengthAwarePaginator::resolveCurrentPage('sync_page');
            $currentItems = $syncDataCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $syncData = new LengthAwarePaginator($currentItems, $syncDataCollection->count(), $perPage, $currentPage, [
                'path' => route('sync'),
                'pageName' => 'sync_page',
            ]);
            Log::info('Paginated Sync Data: ', $syncData->toArray());

            return view('sync', compact('syncData', 'startDate', 'endDate'));
        } catch (\Exception $e) {
            Log::error('Sync Monitoring Error: ' . $e->getMessage());
            return view('sync', [
                'syncData' => new LengthAwarePaginator([], 0, 10),
                'startDate' => null,
                'endDate' => null
            ])->with('error', 'Failed to load sync data.');
        }
    }
}