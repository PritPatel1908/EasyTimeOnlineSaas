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

/**
 * Class OvertimeRule
 *
 * @property int $id
 * @property string $code
 * @property string $description
 * @property bool $ignore_early_come
 * @property bool $ignore_late_come
 * @property int $status
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $ignore_early_come_minutes
 * @property Carbon|null $ignore_late_come_minutes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Collection|User[] $users
 */
class OvertimeRule extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'overtime_rules';

    protected $casts = [
        'ignore_early_come' => 'bool',
        'ignore_early_come_minutes' => 'datetime',
        'ignore_late_come' => 'bool',
        'ignore_late_come_minutes' => 'datetime',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'code',
        'description',
        'ignore_early_come',
        'ignore_late_come',
        'ignore_early_come_minutes',
        'ignore_late_come_minutes',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // public function user()
    // {
    // 	return $this->belongsTo(User::class);
    // }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function overtime_slabs()
    {
        return $this->hasMany(OvertimeSlab::class);
    }
}
