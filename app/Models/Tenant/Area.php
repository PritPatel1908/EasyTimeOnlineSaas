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
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Area
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $location_id
 * @property int|null $time_limit_in_min
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Location $location
 * @property Collection|GuestAccess[] $guest_accesses
 * @property Collection|Machine[] $machines
 */
class Area extends Model
{
    use CUDby;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'areas';

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);
    }

    // public function scopePhysical($query)
    // {
    //     return $query->where('id', '!=', 1);
    // }

    protected $casts = [
        'location_id' => 'array',
        'time_limit_in_min' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'code',
        'name',
        'dms_area_id',
        'time_limit_in_min',
        'location_id',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // public function guest_accesses()
    // {
    // 	return $this->belongsToMany(GuestAccess::class, 'guest_access_area')
    // 		->withPivot('id');
    // }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }
}
