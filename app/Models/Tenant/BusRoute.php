<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Traits\CUDby;
use App\Traits\HasRelatedRecords;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class BusRoute
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $route
 * @property string $number
 * @property string $driver
 * @property int|null $location_id
 * @property int $status
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Location|null $location
 * @property Collection|DataPolicy[] $data_policies
 */
class BusRoute extends Model
{
    use CUDby;
    use HasRelatedRecords;
    use SoftDeletes;

    protected $table = 'bus_routes';

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);
    }

    protected $casts = [
        'location_id' => 'array',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'code',
        'name',
        'route',
        'number',
        'driver',
        'location_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function user()
    {
        return $this->hasMany(User::class);
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
}
