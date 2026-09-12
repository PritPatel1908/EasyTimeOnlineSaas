<?php

namespace App\Models\Tenant;

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
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

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
