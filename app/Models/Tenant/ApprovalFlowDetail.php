<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

// TODO:revome comment after add audit
// use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class ApprovalFlowDetail
 *
 * @property int $id
 * @property int $approval_flow_id
 * @property int $data_policy_id
 * @property bool $notify_only
 * @property int $order
 * @property ApprovalFlow $approval_flow
 * @property DataPolicy $data_policy
 */
// TODO:revome comment after add audit
class ApprovalFlowDetail extends Model // implements Auditable
{
    // TODO:revome comment after add audit
    // use \OwenIt\Auditing\Auditable;
    protected $table = 'approval_flow_details';

    public $timestamps = false;

    protected $casts = [
        'level' => 'int',
        'approval_flow_id' => 'int',
    ];

    protected $fillable = [
        'level',
        'approval_flow_id',
    ];

    public function approval_flow()
    {
        return $this->belongsTo(ApprovalFlow::class);
    }

    public function data_policy()
    {
        return $this->belongsTo(DataPolicy::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'approval_flow_detail_user')
            ->withPivot('id')
            ->withTimestamps();
    }
}
