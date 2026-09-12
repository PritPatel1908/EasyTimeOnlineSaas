<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Company
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $status
 * @property string|null $email
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $logo_path
 * @property User|null $user
 * @property Collection|Location[] $location
 * @property Collection|DataPolicy[] $data_policies
 * @property Collection|Company[] $Companies
 * @property Collection|Shift[] $shifts
 * @property Collection|User[] $users
 */
class Company extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'companies';

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);
    }

    protected $casts = [
        'location_id' => 'array',
        'created_by' => 'int',
        'updated_by' => 'int',
        'status' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'name',
        'code',
        'email',
        'status',
        'location_id',
        'logo_path',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // public function user()
    // {
    // 	return $this->belongsTo(User::class);
    // }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function data_policies()
    {
        return $this->belongsToMany(DataPolicy::class, 'data_policy_company')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function Companies()
    {
        return $this->hasMany(Company::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
