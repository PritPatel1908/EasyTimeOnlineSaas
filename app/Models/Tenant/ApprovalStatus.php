<?php

namespace App\Models\Tenant;

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
    ];

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
