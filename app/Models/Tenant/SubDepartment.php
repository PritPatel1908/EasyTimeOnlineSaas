<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Traits\CUDby;
use App\Traits\HasRelatedRecords;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SubDepartment
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $email
 * @property int|null $location_id
 * @property int $department_id
 * @property int $status
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Department $department
 * @property Location|null $location
 * @property Collection|DataPolicy[] $data_policies
 * @property Collection|User[] $users
 */
class SubDepartment extends Model
{
    use CUDby;
    use HasRelatedRecords;
    use SoftDeletes;

    protected $table = 'sub_departments';

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);
    }

    protected $casts = [
        'location_id' => 'array',
        'department_id' => 'array',
        'created_by' => 'int',
        'status' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'name',
        'code',
        'email',
        'location_id',
        'department_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // public function user()
    // {
    // 	return $this->belongsTo(User::class);
    // }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function data_policies()
    {
        return $this->belongsToMany(DataPolicy::class)
            ->withPivot('id')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
