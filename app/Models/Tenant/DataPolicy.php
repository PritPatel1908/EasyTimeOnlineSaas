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
 * Class DataPolicy
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $status
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Collection|Category[] $categories
 * @property Collection|Company[] $companies
 * @property Collection|Department[] $departments
 * @property Collection|Location[] $locations
 * @property Collection|Designation[] $designations
 * @property Collection|Grade[] $grades
 * @property Collection|Unit[] $units
 * @property Collection|BusRoute[] $bus_routes
 * @property Collection|SubCategory[] $sub_categories
 * @property Collection|SubDepartment[] $sub_departments
 * @property Collection|User[] $users
 * @property Collection|Area[] $areas
 * @property Collection|Machines[] $machines
 */
class DataPolicy extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'data_policies';

    protected $casts = [
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
        'status' => 'int',
        'self_only' => 'boolean',
        'all_locations' => 'boolean',
        'all_companies' => 'boolean',
        'all_departments' => 'boolean',
        'all_sub_departments' => 'boolean',
        'all_categories' => 'boolean',
        'all_sub_categories' => 'boolean',
        'all_designations' => 'boolean',
        'all_grades' => 'boolean',
        'all_units' => 'boolean',
        'all_bus_routes' => 'boolean',
        'all_areas' => 'boolean',
        'all_machines' => 'boolean',
    ];

    protected $fillable = [
        'code',
        'name',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'self_only',
        'all_locations',
        'all_companies',
        'all_departments',
        'all_sub_departments',
        'all_categories',
        'all_sub_categories',
        'all_designations',
        'all_grades',
        'all_units',
        'all_bus_routes',
        'all_areas',
        'all_machines',
    ];

    // public function user()
    // {
    // 	return $this->belongsTo(User::class);
    // }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'data_policy_category')
            // ->withPivot('id')
            ->withTimestamps();
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'data_policy_company')
            // ->withPivot('id')
            ->withTimestamps();
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'data_policy_department')
            // ->withPivot('id')
            ->withTimestamps();
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'data_policy_location')
            // ->withPivot('id')
            ->withTimestamps();
    }

    public function sub_categories()
    {
        return $this->belongsToMany(SubCategory::class, 'data_policy_sub_category')
            // ->withPivot('id')
            ->withTimestamps();
    }

    public function sub_departments()
    {
        return $this->belongsToMany(SubDepartment::class, 'data_policy_sub_department')
            // ->withPivot('id')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function designations()
    {
        return $this->belongsToMany(Designation::class, 'data_policy_designation')
            // ->withPivot('id')
            ->withTimestamps();
    }

    public function grades()
    {
        return $this->belongsToMany(Grade::class, 'data_policy_grade')
            // ->withPivot('id')
            ->withTimestamps();
    }

    public function units()
    {
        return $this->belongsToMany(Unit::class, 'data_policy_unit')
            // ->withPivot('id')
            ->withTimestamps();
    }

    public function bus_routes()
    {
        return $this->belongsToMany(BusRoute::class, 'data_policy_bus_route')
            // ->withPivot('id')
            ->withTimestamps();
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'data_policy_area')
            // ->withPivot('id')
            ->withTimestamps();
    }

    public function machines()
    {
        return $this->belongsToMany(Machine::class, 'data_policy_machine')
            // ->withPivot('id')
            ->withTimestamps();
    }
}
