<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Category;
use App\Models\Tenant\User;
use App\Notifications\Tenant\CategoryImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateCategoryExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public ?int $userId, public string $fileName)
    {
        $this->onConnection('database_tenant')->onQueue('export');
    }
    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null) {
            return;
        }
        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            return;
        }
        fputcsv($handle, Category::IMPORT_EXPORT_COLUMNS);
        foreach (Category::query()->orderBy('name')->get() as $category) {
            fputcsv($handle, [
                $category->name,
                $category->code,
                $category->email ?? '',
                $category->status === 1 ? 'Active' : 'Inactive',
                $category->companies->pluck('name')->join(', '),
                $category->locations->pluck('name')->join(', '),
                $category->canteen_break_limit ?? '',
                $category->need_approval_for_overtime === true ? 'Yes' : ($category->need_approval_for_overtime === false ? 'No' : ''),
                $category->regular_ot_on_wo === true ? 'Yes' : ($category->regular_ot_on_wo === false ? 'No' : ''),
                $category->bypass_timing_rule === true ? 'Yes' : ($category->bypass_timing_rule === false ? 'No' : ''),
                $category->ignore_before_after_shift_punch === true ? 'Yes' : ($category->ignore_before_after_shift_punch === false ? 'No' : ''),
                $category->fix_work_hours === true ? 'Yes' : ($category->fix_work_hours === false ? 'No' : ''),
                $category->fix_work_hours_as_per_shift === true ? 'Yes' : ($category->fix_work_hours_as_per_shift === false ? 'No' : ''),
                $category->fix_work_hours_value ?? '',
                $category->ignore_break_in_attendance === true ? 'Yes' : ($category->ignore_break_in_attendance === false ? 'No' : ''),
                $category->reset_halfday_rule_cycle === true ? 'Yes' : ($category->reset_halfday_rule_cycle === false ? 'No' : ''),
                $category->give_double_ot_in_public_holiday === true ? 'Yes' : ($category->give_double_ot_in_public_holiday === false ? 'No' : ''),
                $category->give_double_coff_in_public_holiday === true ? 'Yes' : ($category->give_double_coff_in_public_holiday === false ? 'No' : ''),
                $category->is_week_off_paid === true ? 'Yes' : ($category->is_week_off_paid === false ? 'No' : ''),
                $category->is_holiday_paid === true ? 'Yes' : ($category->is_holiday_paid === false ? 'No' : ''),
                $category->single_punch_allowed_present === true ? 'Yes' : ($category->single_punch_allowed_present === false ? 'No' : ''),
                $category->single_punch_allowed_half_day === true ? 'Yes' : ($category->single_punch_allowed_half_day === false ? 'No' : ''),
                $category->max_short_leave_minutes_per_month ?? '',
                $category->max_short_leave_minutes_per_application ?? '',
                $category->max_occurance_of_short_leave_in_month ?? '',
                $category->advance_short_leave_application ?? '',
                $category->is_eligible_for_c_off === true ? 'Yes' : ($category->is_eligible_for_c_off === false ? 'No' : ''),
                $category->c_off_lapse_in_days ?? '',
                $category->allow_halfday_c_off === true ? 'Yes' : ($category->allow_halfday_c_off === false ? 'No' : ''),
                $category->allow_backdated_leave === true ? 'Yes' : ($category->allow_backdated_leave === false ? 'No' : ''),
                $category->backdated_day_limit ?? '',
                $category->advance_day_limit ?? '',
                $category->maximum_accumulation ?? '',
                $category->maximum_request_in_a_month ?? '',
                $category->maximum_request_in_a_year ?? '',
                $category->leave_type_id ? (is_array($category->leave_type_id) ? implode(',', array_map('strval', $category->leave_type_id)) : (string) $category->leave_type_id) : '',
                $category->min_avail ?? '',
                $category->max_avail ?? '',
                $category->skip_overtime ?? '',
            ]);
        }
        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        if ($contents === false || ! Storage::disk('local')->put('category-exports/' . $this->fileName, $contents)) {
            return;
        }
        $user->notify(new CategoryImportExportCompleted('export', 'Category export is ready for download.', '/employee-structure/categories/export/download/' . rawurlencode($this->fileName)));
    }
}
