<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Jobs\Central\CreateTenantAndDomains;
use App\Jobs\Central\DispatchTenantReadyNotification;
use App\Jobs\Central\MigrateTenantDatabase;
use App\Jobs\Central\SeedTenantData;
use App\Models\Central\Company;
use App\Models\Central\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Throwable;

class AdminTenantController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.tenants.index', ['tenants' => $this->paginate($request)]);
    }

    public function pagination(Request $request): JsonResponse
    {
        return response()->json([
            'html' => view('admin.tenants.partials.pagination', ['tenants' => $this->paginate($request)])->render(),
        ]);
    }

    private function paginate(Request $request): mixed
    {
        $perPage = $request->validate(['per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])]])['per_page'] ?? 10;
        $tenants = DB::table('tenants')
            ->leftJoin('domains', 'domains.tenant_id', '=', 'tenants.id')
            ->select('tenants.id', 'tenants.data', 'tenants.created_at', DB::raw('COUNT(domains.id) as domains_count'))
            ->groupBy('tenants.id', 'tenants.data', 'tenants.created_at')
            ->latest('tenants.created_at')
            ->paginate($perPage, ['*'], 'page', $request->integer('page', 1))
            ->withQueryString();
        $tenants->getCollection()->transform(fn(object $tenant): object => $this->present($tenant));

        return $tenants;
    }

    public function create(): View
    {
        return view('admin.tenants.form', [
            'tenant' => null,
            'companies' => Company::query()->where('status', 'Active')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'slug' => [
                'required',
                'string',
                'lowercase',
                'max:63',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:tenants,id',
            ],
            'db_connection' => ['required', Rule::in(['mysql', 'pgsql', 'sqlsrv', 'sqlite'])],
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'integer', 'between:1,65535'],
            'db_database' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9_$-]+$/'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ]);
        $company = Company::query()->findOrFail($validated['company_id']);

        Bus::chain([
            new CreateTenantAndDomains(
                $validated['slug'],
                $company->name,
                $company->id,
                $this->tenantDomains($validated['slug']),
                [
                    'connection' => $validated['db_connection'],
                    'host' => $validated['db_host'],
                    'port' => (string) $validated['db_port'],
                    'database' => $validated['db_database'],
                    'username' => $validated['db_username'],
                    'password' => $validated['db_password'] ?? '',
                ],
            ),
            new MigrateTenantDatabase($validated['slug']),
            new SeedTenantData($validated['slug']),
        ])->onConnection('sync')->dispatch();

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant created successfully.');
    }

    public function edit(string $tenant): View
    {
        $tenantRecord = DB::table('tenants')->where('id', $tenant)->firstOrFail();
        $data = is_string($tenantRecord->data) ? json_decode($tenantRecord->data, true) : $tenantRecord->data;
        $tenantRecord->name = is_array($data) && isset($data['name']) ? $data['name'] : $tenantRecord->id;
        $tenantRecord->data = is_array($data) ? $data : [];

        return view('admin.tenants.form', [
            'tenant' => $tenantRecord,
            'companies' => Company::query()->where(function ($query) use ($tenantRecord): void {
                $query->where('status', 'Active')->orWhere('id', $tenantRecord->company_id);
            })->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, string $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'db_connection' => ['required', Rule::in(['mysql', 'pgsql', 'sqlsrv', 'sqlite'])],
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'integer', 'between:1,65535'],
            'db_database' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9_$-]+$/'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ]);
        $company = Company::query()->findOrFail($validated['company_id']);
        $tenantRecord = DB::table('tenants')->where('id', $tenant)->firstOrFail();
        $data = is_string($tenantRecord->data) ? json_decode($tenantRecord->data, true) : $tenantRecord->data;
        $data = is_array($data) ? $data : [];
        $data['name'] = $company->name;
        $data['tenancy_db_connection'] = $validated['db_connection'];
        $data['tenancy_db_host'] = $validated['db_host'];
        $data['tenancy_db_port'] = (string) $validated['db_port'];
        $data['tenancy_db_name'] = $validated['db_database'];
        $data['tenancy_db_username'] = $validated['db_username'];

        if ($validated['db_password'] !== null && $validated['db_password'] !== '') {
            $data['tenancy_db_password'] = $validated['db_password'];
        }

        DB::table('tenants')->where('id', $tenant)->update([
            'company_id' => $company->id,
            'data' => json_encode($data, JSON_THROW_ON_ERROR),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant updated successfully.');
    }

    public function destroy(string $tenant): RedirectResponse
    {
        DB::table('tenants')->where('id', $tenant)->delete();

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant deleted successfully.');
    }

    public function databaseAction(string $tenant): RedirectResponse
    {
        $status = $this->databaseStatus($tenant);
        $message = null;

        if ($status === 'ready') {
            $message = ['success', 'Tenant database is already ready.'];
        } elseif ($status === 'unavailable') {
            $message = ['error', 'Database connection is unavailable. Check the tenant database settings.'];
        } else {
            $tenantRecord = Tenant::query()->where('id', $tenant)->firstOrFail();
            $adminEmail = Auth::user()?->email
                ?? config('mail.from.address');

            Bus::chain([
                new MigrateTenantDatabase($tenant),
                new DispatchTenantReadyNotification($tenant, (string) $adminEmail),
            ])->onConnection('database')->dispatch();

            $message = ['success', 'Tenant database provisioning has started. You will receive a notification when it is ready.'];
        }

        return redirect()->to(url('/tenants'))->with($message[0], $message[1]);
    }

    public function migrate(string $tenant): RedirectResponse
    {
        Tenant::query()->where('id', $tenant)->firstOrFail();

        MigrateTenantDatabase::dispatch($tenant)->onConnection('sync');

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant database migration completed.');
    }

    public function seed(string $tenant): RedirectResponse
    {
        Tenant::query()->where('id', $tenant)->firstOrFail();

        SeedTenantData::dispatch($tenant)->onConnection('sync');

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant database seeding completed.');
    }

    private function present(object $tenant): object
    {
        $data = is_string($tenant->data) ? json_decode($tenant->data, true) : $tenant->data;
        $tenant->name = is_array($data) && isset($data['name']) ? $data['name'] : $tenant->id;
        $tenant->database_status = $this->databaseStatus($tenant->id);
        $tenant->database_status_label = match ($tenant->database_status) {
            'ready' => 'Database ready and migrated',
            'database_pending' => 'Database is not created yet',
            'migrations_pending' => 'Database exists but tenant migrations are pending',
            default => 'Database connection unavailable',
        };
        $tenant->database_status_icon = match ($tenant->database_status) {
            'ready' => 'icon-check text-success',
            'database_pending' => 'icon-clock text-warning',
            'migrations_pending' => 'icon-refresh text-info',
            default => 'icon-exclamation text-danger',
        };

        return $tenant;
    }

    private function databaseStatus(string $tenantId): string
    {
        $connectionName = 'tenant_status_' . $tenantId;

        try {
            $tenantRecord = Tenant::query()->where('id', $tenantId)->firstOrFail();
            $databaseName = $tenantRecord->database()->getName();
            $connectionConfig = $tenantRecord->database()->connection();
            $driver = $connectionConfig['driver'];

            if (! $this->databaseExists($connectionName, $connectionConfig, $databaseName, $driver)) {
                return 'database_pending';
            }

            config(['database.connections.' . $connectionName => $connectionConfig]);
            DB::purge($connectionName);
            $hasMigrationsTable = Schema::connection($connectionName)->hasTable(
                (string) config('database.migrations.table', 'migrations'),
            );

            return $hasMigrationsTable ? 'ready' : 'migrations_pending';
        } catch (Throwable) {
            return 'unavailable';
        } finally {
            DB::purge($connectionName);
        }
    }

    /**
     * Check existence with the credentials entered for this tenant.
     *
     * @param  array<string, mixed>  $connectionConfig
     */
    private function databaseExists(string $connectionName, array $connectionConfig, string $databaseName, string $driver): bool
    {
        $exists = false;

        if ($driver === 'sqlsrv') {
            $connectionConfig['database'] = 'master';
            config(['database.connections.' . $connectionName => $connectionConfig]);
            DB::purge($connectionName);

            $exists = DB::connection($connectionName)->selectOne(
                'SELECT name FROM sys.databases WHERE name = ?',
                [$databaseName],
            ) !== null;
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            $connectionConfig['database'] = null;
            config(['database.connections.' . $connectionName => $connectionConfig]);
            DB::purge($connectionName);

            $exists = DB::connection($connectionName)->selectOne(
                'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
                [$databaseName],
            ) !== null;
        } elseif ($driver === 'pgsql') {
            $connectionConfig['database'] = 'postgres';
            config(['database.connections.' . $connectionName => $connectionConfig]);
            DB::purge($connectionName);

            $exists = DB::connection($connectionName)->selectOne(
                'SELECT datname FROM pg_database WHERE datname = ?',
                [$databaseName],
            ) !== null;
        } else {
            $exists = file_exists(database_path($databaseName));
        }

        return $exists;
    }

    private function tenantDomain(string $slug, string $environmentKey): string
    {
        $environment = $environmentKey === 'TENANT_BASE_DOMAIN_LOCAL' ? 'local' : 'prod';

        return $slug . '.' . trim((string) config('tenancy.base_domains.' . $environment), '.');
    }

    /**
     * @return array<int, string>
     */
    private function tenantDomains(string $slug): array
    {
        return [
            $this->tenantDomain($slug, 'TENANT_BASE_DOMAIN_LOCAL'),
            $this->tenantDomain($slug, 'TENANT_BASE_DOMAIN_PROD'),
        ];
    }
}
