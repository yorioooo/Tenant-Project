<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\RevenueTarget;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function index()
    {
        // Ambil total revenue tiap tenant dari transaksi yang sudah sync
        $tenantRevenues = Transaction::select('tenant_name')
            ->selectRaw('SUM(total_amount_net) as total_revenue')
            ->where('status_sync', 'Y')
            ->groupBy('tenant_name')
            ->get();

        $totalRevenueAll = $tenantRevenues->sum('total_revenue');

        // Hitung contribution % dan revenue share berdasarkan rumus: 
        // revenue_share = total_revenue * (contribution% / 100)
        // contribution% = (tenant_total_revenue / total_revenue_all) * 100

        $data = $tenantRevenues->map(function ($item) use ($totalRevenueAll) {
            $contributionPercentage = $totalRevenueAll > 0
                ? ($item->total_revenue / $totalRevenueAll) * 100
                : 0;

            $revenueShare = $item->total_revenue * ($contributionPercentage / 100);

            return [
                'tenant_name' => $item->tenant_name,
                'total_revenue' => $item->total_revenue,
                'contribution_percentage' => round($contributionPercentage, 2),
                'revenue_share' => round($revenueShare, 2),
            ];
        });

        // Ambil revenue target dan flag dari tabel revenue_targets
        $targets = RevenueTarget::all()->keyBy('tenant_name');

        // Gabungkan data revenue dan target + flag
        $result = $data->map(function ($item) use ($targets) {
            $target = $targets->get($item['tenant_name']);
            return [
                'tenant_name' => $item['tenant_name'],
                'total_revenue' => $item['total_revenue'],
                'contribution_percentage' => $item['contribution_percentage'],
                'revenue_share' => $item['revenue_share'],
                'target_amount' => $target ? $target->target_amount : null,
                'flag' => $target ? $target->flag : null,
            ];
        });

        // Kirim data ke view atau JSON response
        return response()->json($result);
    }
}
