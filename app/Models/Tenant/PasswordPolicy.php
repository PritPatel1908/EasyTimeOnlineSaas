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
 * Class PasswordPolicy
 *
 * @property int $id
 * @property string $policy_name
 * @property int $password_expiry_days
 * @property int $notify_expiry_days
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Collection|User[] $users
 */
class PasswordPolicy extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'password_policies';

    protected $casts = [
        'password_expiry_days' => 'int',
        'notify_expiry_days' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'policy_name',
        'password_expiry_days',
        'notify_expiry_days',
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
}
