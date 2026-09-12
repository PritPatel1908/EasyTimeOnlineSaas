<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Machine
 *
 * @property int $id
 * @property string $name
 * @property string $machine_ip
 * @property string $machine_serial_number
 * @property int|null $dms_device_id
 * @property int|null $dms_area_id
 * @property int $sync_dms
 * @property string $network_status
 * @property string|null $access_direction
 * @property int|null $location_id
 * @property int|null $area_id
 * @property int $status
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Location|null $location
 */
class Machine extends Model
{
    use CUDby;
    use SoftDeletes;

    protected $table = 'machines';

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);
    }

    protected $casts = [
        'sync_dms' => 'int',
        'location_id' => 'int',
        'company_id' => 'int',
        'area_id' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'name',
        'machine_ip',
        'machine_serial_number',
        'dms_device_id',
        'dms_area_id',
        'sync_dms',
        'network_status',
        'access_direction',
        'location_id',
        'company_id',
        'area_id',
        'status',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
