<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoffApplication extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'coff_applications';

    protected $fillable = [
        'user_id',
        'coff_against_date',
        'coff_from_date',
        'coff_to_date',
        'is_half_day',
        'is_first_half',
        'is_second_half',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'user_id' => 'int',
        'coff_against_date' => 'date',
        'coff_from_date' => 'date',
        'coff_to_date' => 'date',
        'is_half_day' => 'bool',
        'is_first_half' => 'bool',
        'is_second_half' => 'bool',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    public const IMPORT_EXPORT_COLUMNS = [
        'employee_code',
        'coff_against_date',
        'coff_from_date',
        'coff_to_date',
        'is_half_day',
        'is_first_half',
        'is_second_half',
        'approval_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approval_status()
    {
        return $this->morphOne(ApprovalStatus::class, 'approvable');
    }

    public function approval_statuses()
    {
        return $this->morphMany(ApprovalStatus::class, 'approvable');
    }

    public function halfDayDescription(): string
    {
        if ($this->is_half_day) {
            return $this->is_first_half ? 'First half' : 'Second half';
        }

        $parts = [];
        if ($this->is_second_half) {
            $parts[] = 'Start date: second half';
        }
        if ($this->is_first_half) {
            $parts[] = 'End date: first half';
        }

        return $parts === [] ? 'Full day(s)' : implode(', ', $parts);
    }

    public function overallApprovalStatus(): string
    {
        $statuses = $this->relationLoaded('approval_statuses')
            ? $this->approval_statuses
            : $this->approval_statuses()->get();

        if ($statuses->isEmpty()) {
            return 'unknown';
        }

        if ($statuses->contains('approval_status', 'rejected')) {
            return 'rejected';
        }

        if ($statuses->contains(static fn (ApprovalStatus $status): bool => in_array(
            $status->approval_status,
            ['draft', 'pending'],
            true,
        ))) {
            return 'pending';
        }

        return $statuses->every(
            static fn (ApprovalStatus $status): bool => $status->approval_status === 'approved',
        ) ? 'approved' : 'unknown';
    }
}
