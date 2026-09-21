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
            fputcsv($handle, [
                $employee->code,
                $employee->fname,
                $employee->mname,
                $employee->lname,
                $employee->name,
                $employee->email,
                $employee->number,
                $employee->card,
                $employee->gender,
                $employee->dob?->format('Y-m-d'),
                $employee->join_date?->format('Y-m-d'),
                $employee->status ? 'Active' : 'Inactive',
                $employee->user_type,
                $this->names($employee->company_id, 'Company'),
                $this->names($employee->location_id, 'Location'),
                $this->names($employee->department_id, 'Department'),
                $this->names($employee->sub_department_id, 'SubDepartment'),
                $this->names($employee->category_id, 'Category'),
                $this->names($employee->sub_category_id, 'SubCategory'),
                $this->names($employee->designation_id, 'Designation'),
                $this->names($employee->grade_id, 'Grade'),
                $this->names($employee->unit_id, 'Unit'),
                $this->names($employee->bus_route_id, 'BusRoute'),
                $employee->data_policy?->name ?? '',
                $employee->roles->pluck('name')->join(', '),
                $employee->is_locked ? 'Yes' : 'No',
                $employee->allow_mobile_login ? 'Yes' : 'No',
                $employee->allow_mobile_punch ? 'Yes' : 'No',
            ]);
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
        $class = 'App\\Models\\Tenant\\' . $model;
        return $class::query()->whereIn('id', array_filter($ids))->pluck('name')->join(', ');
    }
}
