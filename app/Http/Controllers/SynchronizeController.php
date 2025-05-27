<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class SynchronizeController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Ambil daftar tenant unik dari tabel transactions
            $tenants = Transaction::select('tenant_name')
                ->distinct()
                ->orderBy('tenant_name')
                ->get();

            // Ambil tenant yang dipilih dari request
            $selectedTenant = $request->input('tenant_name');

            // Inisialisasi transaksi kosong
            $transactions = new LengthAwarePaginator([], 0, 10);

            // Jika tenant dipilih, ambil transaksi yang belum disinkronkan (status_sync = 'N')
            if ($selectedTenant) {
                $transactions = Transaction::where('tenant_name', $selectedTenant)
                    ->where('status_sync', 'N')
                    ->select('id', 'tenant_name', 'transaction_id', 'date_time_transaction', 'total_amount_net', 'status_sync')
                    ->orderByDesc('date_time_transaction')
                    ->paginate(10);
                Log::info('Unsynchronized transactions for tenant ' . $selectedTenant . ': ', $transactions->toArray());
            }

            return view('syncronize', compact('tenants', 'transactions', 'selectedTenant'));

        } catch (\Exception $e) {
            Log::error('Synchronize Index Error: ' . $e->getMessage());
            return view('syncronize', [
                'tenants' => [],
                'transactions' => new LengthAwarePaginator([], 0, 10),
                'selectedTenant' => null
            ])->with('error', 'Failed to load synchronize page. Please try again later.');
        }
    }

    public function update($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            // Pastikan transaksi belum disinkronkan
            if ($transaction->status_sync === 'N') {
                $transaction->status_sync = 'Y';
                $transaction->save();

                Log::info('Transaction synchronized: ', $transaction->toArray());
                return redirect()->route('syncronize')->with('success', 'Transaction synchronized successfully.');
            }

            return redirect()->route('syncronize')->with('info', 'Transaction already synchronized.');

        } catch (\Exception $e) {
            Log::error('Synchronize Update Error: ' . $e->getMessage());
            return redirect()->route('syncronize')->with('error', 'Failed to synchronize transaction. Please try again later.');
        }
    }
}