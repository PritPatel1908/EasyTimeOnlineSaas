<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\User;
use App\Notifications\Tenant\EmployeeImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateEmployeeExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId, public string $fileName)
    {
        $this->onConnection('database_tenant')->onQueue('export');
    }

    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null) return;
        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) return;
        fputcsv($handle, User::IMPORT_EXPORT_COLUMNS);
        foreach (User::query()->where('user_type', 'employee')->orderBy('name')->get() as $employee) {
            $values = [
                'code' => $employee->code,
                'fname' => $employee->fname,
                'mname' => $employee->mname,
                'lname' => $employee->lname,
                'name' => $employee->name,
                'email' => $employee->email,
                'number' => $employee->number,
                'card' => $employee->card,
                'gender' => $employee->gender,
                'dob' => $employee->dob?->format('Y-m-d'),
                'blood_group_id' => $employee->blood_group_id,
                'join_date' => $employee->join_date?->format('Y-m-d'),
                'status' => $employee->status ? 'Active' : 'Inactive',
                'user_type' => $employee->user_type,
                'company' => $this->names($employee->company_id, 'Company'),
                'location' => $this->names($employee->location_id, 'Location'),
                'department' => $this->names($employee->department_id, 'Department'),
                'sub_department' => $this->names($employee->sub_department_id, 'SubDepartment'),
                'category' => $this->names($employee->category_id, 'Category'),
                'sub_category' => $this->names($employee->sub_category_id, 'SubCategory'),
                'designation' => $this->names($employee->designation_id, 'Designation'),
                'grade' => $this->names($employee->grade_id, 'Grade'),
                'unit' => $this->names($employee->unit_id, 'Unit'),
                'bus_route' => $this->names($employee->bus_route_id, 'BusRoute'),
                'data_policy' => $employee->data_policy?->name ?? '',
                'role' => $employee->roles->pluck('name')->join(', '),
                'is_locked' => $employee->is_locked ? 'Yes' : 'No',
                'allow_mobile_login' => $employee->allow_mobile_login ? 'Yes' : 'No',
                'allow_mobile_punch' => $employee->allow_mobile_punch ? 'Yes' : 'No',
            ];
            fputcsv($handle, $this->orderByExportColumns($values));
        }
        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        if ($contents === false || ! Storage::disk('local')->put('employee-exports/' . $this->fileName, $contents)) return;
        $user->notify(new EmployeeImportExportCompleted('export', 'Employee export is ready for download.', '/employee-structure/employees/export/download/' . rawurlencode($this->fileName)));
    }

    private function names(mixed $value, string $model): string
    {
        $ids = is_array($value) ? $value : (json_decode((string) $value, true) ?: [$value]);
        $ids = (array) $ids;
        $class = 'App\\Models\\Tenant\\' . $model;
        return $class::query()->whereIn('id', array_filter($ids))->pluck('name')->join(', ');
    }

    /**
     * @param array<string, mixed> $values
     * @return array<int, mixed>
     */
    private function orderByExportColumns(array $values): array
    {
        return array_map(
            static fn (string $column): mixed => $values[$column] ?? '',
            User::IMPORT_EXPORT_COLUMNS,
        );
    }
}
