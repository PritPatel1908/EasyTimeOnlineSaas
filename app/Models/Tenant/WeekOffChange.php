<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Class WeekOffChange
 *
 * @property int $id
 * @property string|null $reason
 * @property Carbon|null $from_date
 * @property Carbon|null $to_date
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Collection|Users[] $users
 * @property Collection|WeekOffChangeDetail[] $week_off_change_details
 * @property Collection|WeekOffMuster[] $WeekOffMuster
 * @property ApprovalStatus|null $approval_status
 */
class WeekOffChange extends Model
{
    use CUDby;
    use HasFactory, SoftDeletes;

    protected $table = 'week_off_changes';

    protected $casts = [
        'from_date' => 'datetime',
        'is_forever' => 'bool',
        'to_date' => 'datetime',
        'location_id' => 'array',
        'company_id' => 'array',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'reason',
        'from_date',
        'is_forever',
        'location_id',
        'company_id',
        'to_date',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);

        // When a ShiftChange is retrieved, make sure we also apply the data policy filter to the related users
        static::retrieved(function (WeekOffChange $weekOffChange) {
            if ($weekOffChange->relationLoaded('Users')) {
                // Filter the users collection based on data policy
                $weekOffChange->setRelation('Users', $weekOffChange->Users->filter(function ($user) {
                    // Apply the same logic as in DataPolicyFilter for users
                    return $user->id === Auth::id() || (Auth::user() && Auth::user()->data_policy);
                }));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function Users()
    {
        return $this->belongsToMany(User::class, 'week_off_change_users')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function WeekOffChangeDetails()
    {
        return $this->hasMany(WeekOffChangeDetail::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'week_off_change_department')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function sub_departments()
    {
        return $this->belongsToMany(SubDepartment::class, 'week_off_change_sub_department')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'week_off_change_category')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function sub_categories()
    {
        return $this->belongsToMany(SubCategory::class, 'week_off_change_sub_category')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function designations()
    {
        return $this->belongsToMany(Designation::class, 'week_off_change_designation')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'week_off_change_area')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function WeekOffMuster(): MorphMany
    {
        return $this->morphMany(WeekOffMuster::class, 'weekoffable');
    }

    public function approval_status()
    {
        return $this->morphOne(ApprovalStatus::class, 'approvable');
    }
}
