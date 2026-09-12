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
 * Class Location
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $email
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $deleted_at
 * @property int $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Collection|Category[] $categories
 * @property Collection|Company[] $companies
 * @property Collection|DataPolicy[] $data_policies
 * @property Collection|Department[] $departments
 * @property Collection|Designation[] $designations
 * @property Collection|LeaveGroup[] $leave_groups
 * @property Collection|Unit[] $units
 * @property Collection|Grade[] $grades
 * @property Collection|BusRoute[] $bus_routes
 * @property Collection|LeaveType[] $leave_types
 * @property Collection|Holiday[] $holidays
 * @property Collection|Machine[] $machines
 * @property Collection|Shift[] $shifts
 * @property Collection|SubCategory[] $sub_categories
 * @property Collection|SubDepartment[] $sub_departments
 * @property Collection|User[] $users
 * @property Collection|Area[] $areas
 */
class Location extends Model
{
    use CUDby;
    use HasRelatedRecords;
    use SoftDeletes;

    protected $table = 'locations';

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);
    }

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'name',
        'code',
        'email',
        'latitude',
        'longitude',
        'created_by',
        'updated_by',
        'deleted_by',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    public function data_policies()
    {
        return $this->belongsToMany(DataPolicy::class)
            ->withPivot('id')
            ->withTimestamps();
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function designations()
    {
        return $this->hasMany(Designation::class);
    }

    public function holidays()
    {
        return $this->belongsToMany(Holiday::class)
            ->withPivot('id')
            ->withTimestamps();
    }

    public function leave_groups()
    {
        return $this->hasMany(LeaveGroup::class);
    }

    public function leave_types()
    {
        return $this->hasMany(LeaveType::class);
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function sub_categories()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function sub_departments()
    {
        return $this->hasMany(SubDepartment::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function bus_routes()
    {
        return $this->hasMany(BusRoute::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }
}
