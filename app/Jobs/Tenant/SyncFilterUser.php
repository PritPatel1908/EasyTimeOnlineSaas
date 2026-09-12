<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffMuster;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncFilterUser implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uniqueFor = 3600;

    protected $record;

    protected $user_id;

    public function __construct($record, $user_id)
    {
        $this->record = $record;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::where(function (Builder $query) {
            // $query->where('id', '!=', $this->user_id);
            // $locationIds = $this->record?->location?->id ?? $this->record?->location_id ?? null;
            $locationIds = is_array($this->record?->location_id) ? $this->record?->location_id : $this->record?->location->id;
            if ($locationIds) {
                $locationIds = is_array($locationIds) ? $locationIds : [$locationIds];
                $query->whereIn('location_id', $locationIds);
            }
            // $companyIds = $this->record?->company?->id ?? $this->record?->company_id ?? null;
            $companyIds = is_array($this->record?->company_id) ? $this->record?->company_id : $this->record?->company->id;
            if ($companyIds) {
                $companyIds = is_array($companyIds) ? $companyIds : [$companyIds];
                $query->whereIn('company_id', $companyIds);
            }
            if ($this->record->departments()->get() != null && $this->record->departments()->get()->count() > 0) {
                $query->whereIn('department_id', $this->record->departments()->pluck('department_id'));
            }
            if ($this->record->sub_departments()->get() != null && $this->record->sub_departments()->get()->count() > 0) {
                $query->whereIn('sub_department_id', $this->record->sub_departments()->pluck('sub_department_id'));
            }
            if ($this->record->categories()->get() != null && $this->record->categories()->get()->count() > 0) {
                $query->whereIn('category_id', $this->record->categories()->pluck('category_id'));
            }
            if ($this->record->sub_departments()->get() != null && $this->record->sub_departments()->get()->count() > 0) {
                $query->whereIn('sub_category_id', $this->record->sub_categories()->pluck('sub_category_id'));
            }
            if ($this->record->designations()->get() != null && $this->record->designations()->get()->count() > 0) {
                $query->whereIn('designation_id', $this->record->designations()->pluck('designation_id'));
            }
            if ($this->record->areas()->get() != null && $this->record->designations()->get()->count() > 0) {
                $query->whereIn('users.area_id', $this->record->areas()->pluck('area_id'));
                $query->whereHas('areas', function (Builder $query) {
                    $query->where('areas.id', $this->record->areas()->pluck('area_id'));
                });
            }
            if ($this->record->Users()->get() != null && $this->record->Users()->get()->count() > 0) {
                $query->whereIn('id', $this->record->Users()->pluck('user_id'));
            }
            if (get_class($this->record) === 'App\Models\Tenant\WeekOffSwap' && $this->record->week_date != null) {
                $weekOffDate = $this->record->week_date;
                $weekOffMuster = WeekOffMuster::where('date', $weekOffDate)->where('is_locked', false)->whereIn('wo_type', [1, 2, 3])->pluck('user_id');
                $query->whereIn('users.id', $weekOffMuster);
            }
        })->get();
        $this->record->Users()->sync($users->pluck('id'));
    }
}
