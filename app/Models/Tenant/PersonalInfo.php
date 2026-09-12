<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Traits\CUDby;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PersonalInfo
 *
 * @property int $id
 * @property float|null $height
 * @property float|null $weight
 * @property string|null $languages
 * @property string|null $hobbies
 * @property string|null $emergency_name
 * @property string|null $emergency_number
 * @property string|null $emergency_address
 * @property int|null $user_id
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 */
class PersonalInfo extends Model
{
    use SoftDeletes;

    // use CUDby;
    protected $table = 'personal_infos';

    protected $casts = [
        'height' => 'float',
        'weight' => 'float',
        'user_id' => 'int',
        'languages' => 'json',
        'hobbies' => 'json',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'height',
        'weight',
        'languages',
        'hobbies',
        'emergency_name',
        'emergency_number',
        'emergency_address',
        'user_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
