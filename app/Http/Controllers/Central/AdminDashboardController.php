<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $tenants = DB::table('tenants')
            ->leftJoin('domains', 'domains.tenant_id', '=', 'tenants.id')
            ->select('tenants.id', 'tenants.data', 'tenants.created_at', DB::raw('COUNT(domains.id) as domains_count'))
            ->groupBy('tenants.id', 'tenants.data', 'tenants.created_at')
            ->latest('tenants.created_at')
            ->limit(5)
            ->get()
            ->map(function (object $tenant): object {
                $data = is_string($tenant->data) ? json_decode($tenant->data, true) : $tenant->data;
                $tenant->name = is_array($data) && isset($data['name']) ? $data['name'] : $tenant->id;
                $tenant->created_at = $tenant->created_at ? Carbon::parse($tenant->created_at) : null;

                return $tenant;
            });

        return view('admin.dashboard', [
            'tenantCount' => DB::table('tenants')->count(),
            'domainCount' => DB::table('domains')->count(),
            'userCount' => DB::table('users')->count(),
            'newTenantCount' => DB::table('tenants')->whereBetween('created_at', [$monthStart, $monthEnd])->count(),
            'tenants' => $tenants,
        ]);
    }
}
