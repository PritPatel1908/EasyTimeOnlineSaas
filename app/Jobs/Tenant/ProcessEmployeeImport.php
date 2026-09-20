<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\BusRoute;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\DataPolicy;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\Grade;
use App\Models\Tenant\Location;
use App\Models\Tenant\Role;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Unit;
use App\Models\Tenant\User;
use App\Notifications\Tenant\EmployeeImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProcessEmployeeImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId, public string $filePath, public bool $updateDuplicateRecords = false)
    {
        $this->onConnection('database_tenant')->onQueue('import');
    }

    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null || ! Storage::disk('local')->exists($this->filePath)) return;
        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $handle = fopen(Storage::disk('local')->path($this->filePath), 'r');
        if ($handle === false) return;
        $headers = array_map(fn($header) => strtolower(trim((string) $header)), fgetcsv($handle) ?: []);
        $created = 0;
        $updated = 0;
        $errors = [];
        while (($values = fgetcsv($handle)) !== false) {
            $row = array_combine($headers, array_pad(array_slice($values, 0, count($headers)), count($headers), ''));
            $code = trim((string) ($row['code'] ?? ''));
            if ($code === '') continue;
            $employee = User::query()->where('code', $code)->first();
            if ($employee && ! $this->updateDuplicateRecords) {
                $errors[] = 'Duplicate code ' . $code;
                continue;
            }
            $payload = $this->payload($row, $code);
            if ($payload['name'] === '') {
                $errors[] = 'Missing name for code ' . $code;
                continue;
            }
            $roleName = trim((string) ($row['role'] ?? ''));
            if ($employee) {
                $employee->update($payload);
                $employee->syncRoles($this->resolveRole($roleName));
                $updated++;
            } else {
                $employee = User::create($payload);
                $employee->syncRoles($this->resolveRole($roleName));
                $created++;
            }
        }
        fclose($handle);
        Storage::disk('local')->delete($this->filePath);
        $message = $errors === [] ? "Imported {$created} new employee record(s) and updated {$updated} existing record(s)." : 'Import completed with errors: ' . implode('; ', array_slice($errors, 0, 5));
        $user->notify(new EmployeeImportExportCompleted('import', $message));
    }

    private function payload(array $row, string $code): array
    {
        return [
            'code' => $code,
            'fname' => trim((string) ($row['fname'] ?? '')),
            'mname' => trim((string) ($row['mname'] ?? '')) ?: null,
            'lname' => trim((string) ($row['lname'] ?? '')) ?: null,
            'name' => trim((string) ($row['name'] ?? '')),
            'email' => trim((string) ($row['email'] ?? '')) ?: null,
            'number' => trim((string) ($row['number'] ?? '')) ?: null,
            'card' => trim((string) ($row['card'] ?? '')) ?: null,
            'gender' => trim((string) ($row['gender'] ?? '')) ?: null,
            'dob' => trim((string) ($row['dob'] ?? '')) ?: null,
            'join_date' => trim((string) ($row['join_date'] ?? '')) ?: null,
            'status' => strtolower(trim((string) ($row['status'] ?? 'active'))) === 'inactive' ? 0 : 1,
            'user_type' => trim((string) ($row['user_type'] ?? 'employee')) ?: 'employee',
            'is_locked' => in_array(strtolower(trim((string) ($row['is_locked'] ?? ''))), ['1', 'yes', 'true'], true),
            'company_id' => $this->resolveIds(Company::class, $row['company'] ?? ''),
            'location_id' => $this->resolveIds(Location::class, $row['location'] ?? ''),
            'department_id' => $this->resolveIds(Department::class, $row['department'] ?? ''),
            'sub_department_id' => $this->resolveIds(SubDepartment::class, $row['sub_department'] ?? ''),
            'category_id' => $this->resolveIds(Category::class, $row['category'] ?? ''),
            'sub_category_id' => $this->resolveIds(SubCategory::class, $row['sub_category'] ?? ''),
            'designation_id' => $this->resolveIds(Designation::class, $row['designation'] ?? ''),
            'grade_id' => $this->resolveIds(Grade::class, $row['grade'] ?? ''),
            'unit_id' => $this->resolveIds(Unit::class, $row['unit'] ?? ''),
            'bus_route_id' => $this->resolveIds(BusRoute::class, $row['bus_route'] ?? ''),
            'data_policy_id' => $this->resolveOne(DataPolicy::class, $row['data_policy'] ?? ''),
        ];
    }

    private function resolveRole(string $value): array
    {
        $role = $value === '' ? null : Role::query()->where('guard_name', 'tenant')->where('name', $value)->first();

        return $role ? [$role] : [];
    }

    private function resolveIds(string $model, mixed $value): array
    {
        return array_values(array_filter(array_map(fn(string $item) => $this->resolveOne($model, $item), array_filter(array_map('trim', explode(',', (string) $value))))));
    }

    private function resolveOne(string $model, mixed $value): ?int
    {
        $value = trim((string) $value);
        if ($value === '') return null;
        $record = ctype_digit($value) ? $model::query()->find((int) $value) : ($model::query()->where('name', $value)->first() ?? $model::query()->where('code', $value)->first());

        return $record?->getKey();
    }
}
