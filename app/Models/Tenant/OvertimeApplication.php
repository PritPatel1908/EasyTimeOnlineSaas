<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OvertimeApplication extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'overtime_applications';

    protected $casts = [
        'user_id' => 'int',
        'date' => 'date',
        'overtime_minutes' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'user_id',
        'date',
        'overtime_minutes',
        'reason',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public const IMPORT_EXPORT_COLUMNS = [
        'employee_codes',
        'date',
        'overtime_minutes',
        'reason',
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
