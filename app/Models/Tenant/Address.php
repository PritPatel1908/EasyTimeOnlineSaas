<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Address
 *
 * @property int $id
 * @property string|null $flat_building
 * @property string|null $house_no
 * @property string|null $flore
 * @property string|null $street
 * @property string|null $landmark
 * @property string|null $country
 * @property string|null $state
 * @property string|null $city
 * @property string|null $pincode
 * @property bool $is_current_address
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property PersonalInfo $personal_info
 */
class Address extends Model
{
    protected $table = 'addresses';

    protected $casts = [
        'is_current_address' => 'bool',
        'user_id' => 'int',
    ];

    protected $fillable = [
        'flat_building',
        'house_no',
        'flore',
        'street',
        'landmark',
        'country',
        'state',
        'city',
        'pincode',
        'is_current_address',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
