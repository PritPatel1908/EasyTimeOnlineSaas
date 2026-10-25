<?php

namespace App\Models\Tenant;

use App\Jobs\Tenant\ReverseLeaveApplicationAccountBalance;
use App\Traits\CUDby;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// TODO:revome comment after add audit
// use OwenIt\Auditing\Contracts\Auditable;

class ApprovalStatusDetails extends Model // implements Auditable
{
    use CUDby;
    use HasFactory;
    // TODO:revome comment after add audit
    // use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'level',
        'approval_status_id',
        'approval_flow_detail_id',
        'user_id',
        'approval_status',
        'timestamp',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updated(static function (self $approvalStatusDetail): void {
            if (! $approvalStatusDetail->wasChanged('approval_status')
                || $approvalStatusDetail->approval_status !== 'rejected') {
                return;
            }

            $approvalStatus = $approvalStatusDetail->main_status;
            if ($approvalStatus?->approvable_type !== LeaveApplication::class) {
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
                $approvalStatusDetail->updated_by === null ? null : (int) $approvalStatusDetail->updated_by,
            )->onConnection('database_tenant')->onQueue('processing')->afterCommit();
        });
    }

    public function main_status()
    {
        return $this->belongsTo(ApprovalStatus::class, 'approval_status_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approval_flow_detail()
    {
        return $this->belongsTo(ApprovalFlowDetail::class);
    }
}
