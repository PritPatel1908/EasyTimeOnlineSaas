<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class AdminSectionController extends Controller
{
    private const UNAVAILABLE = 'Not available';

    public function tenants(): View
    {
        $rows = DB::table('tenants')
            ->select('id', 'data', 'created_at')
            ->latest()
            ->get()
            ->map(function (object $tenant): array {
                $data = is_string($tenant->data) ? json_decode($tenant->data, true) : $tenant->data;

                return [
                    'name' => is_array($data) && isset($data['name']) ? $data['name'] : $tenant->id,
                    'identifier' => $tenant->id,
                    'created' => $tenant->created_at ?? self::UNAVAILABLE,
                    'status' => 'Active',
                ];
            })
            ->all();

        return $this->section('Tenants', 'Manage all tenant workspaces registered in EasyTime Online SaaS.', ['Tenant', 'Identifier', 'Created', 'Status'], $rows);
    }

    public function domains(): View
    {
        $rows = DB::table('domains')
            ->join('tenants', 'tenants.id', '=', 'domains.tenant_id')
            ->select('domains.domain', 'domains.tenant_id', 'domains.created_at')
            ->latest('domains.created_at')
            ->get()
            ->map(fn (object $domain): array => [
                'domain' => $domain->domain,
                'tenant' => $domain->tenant_id,
                'created' => $domain->created_at ?? self::UNAVAILABLE,
                'status' => 'Active',
            ])
            ->all();

        return $this->section('Domains', 'Review the domains connected to tenant workspaces.', ['Domain', 'Tenant', 'Created', 'Status'], $rows);
    }

    public function users(): View
    {
        $rows = DB::table('users')
            ->select('name', 'email', 'created_at')
            ->latest()
            ->get()
            ->map(fn (object $user): array => [
                'name' => $user->name,
                'email' => $user->email,
                'created' => $user->created_at ?? self::UNAVAILABLE,
                'status' => 'Registered',
            ])
            ->all();

        return $this->section('Users', 'Review platform users registered in the central administration.', ['Name', 'Email', 'Created', 'Status'], $rows);
    }

    public function reports(): View
    {
        return $this->section('Reports', 'Monitor tenant, domain, and platform activity from one place.', [], []);
    }

    public function settings(): View
    {
        return $this->section('Settings', 'Central administration settings for EasyTime Online SaaS.', [], []);
    }

    private function section(string $title, string $description, array $headers, array $rows): View
    {
        return view('admin.section', compact('title', 'description', 'headers', 'rows'));
    }
}
