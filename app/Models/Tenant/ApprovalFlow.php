<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// TODO:revome comment after add audit
// use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class ApprovalFlow
 *
 * @property int $id
 * @property string $approval_flow_code
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Collection|ApprovalFlowDetail[] $approval_flow_details
 */
// TODO:revome comment after add audit
class ApprovalFlow extends Model // implements Auditable
{
    use CUDby;
    use SoftDeletes;

    // TODO:revome comment after add audit
    // use \OwenIt\Auditing\Auditable;
    protected $table = 'approval_flows';

    protected $casts = [
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'approval_flow_code',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'approval_flow_id', 'id');
    }

    public function shift_change_approval_flow()
    {
        return $this->belongsTo(User::class, 'shift_change_approval_flow_id', 'id');
    }

    public function approval_statuses()
    {
        return $this->hasMany(ApprovalStatus::class, 'approval_flow_id', 'id');
    }

    public function approval_flow_details()
    {
        return $this->hasMany(ApprovalFlowDetail::class);
    }
}
