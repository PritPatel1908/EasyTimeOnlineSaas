<?php

namespace App\Models\Tenant;

use App\Jobs\Tenant\ReverseLeaveApplicationAccountBalance;
use App\Traits\CUDby;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// TODO:revome comment after add audit
// use OwenIt\Auditing\Contracts\Auditable;
class ApprovalStatus extends Model // implements Auditable
{
    use CUDby;
    use HasFactory;
    // TODO:revome comment after add audit
    // use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'approvable_id',
        'approvable_type',
        'approval_status',
        'approval_flow_id',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::updated(static function (self $approvalStatus): void {
            if (! $approvalStatus->wasChanged('approval_status')
                || $approvalStatus->approval_status !== 'rejected'
                || $approvalStatus->approvable_type !== LeaveApplication::class) {
                return;
            }

            $application = LeaveApplication::withTrashed()->findOrFail($approvalStatus->approvable_id);
            $userIds = $application->users()
                ->pluck('users.id')
                ->map(static fn (mixed $userId): int => (int) $userId)
                ->all();

            ReverseLeaveApplicationAccountBalance::dispatch(
                (int) $application->id,
                $userIds,
                $approvalStatus->updated_by === null ? null : (int) $approvalStatus->updated_by,
            )->onConnection('database_tenant')->onQueue('processing')->afterCommit();
        });
    }

    public function approvable()
    {
        return $this->morphTo();
    }

    public function approval_flow()
    {
        return $this->belongsTo(ApprovalFlow::class);
    }

    public function approval_status_details()
    {
        return $this->hasMany(ApprovalStatusDetails::class);
    }
}
