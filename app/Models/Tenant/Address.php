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
 * @property string|null $current_flat_building
 * @property string|null $current_house_no
 * @property string|null $current_flore
 * @property string|null $current_street
 * @property string|null $current_landmark
 * @property string|null $current_country
 * @property string|null $current_state
 * @property string|null $current_city
 * @property string|null $current_pincode
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
        'current_flat_building',
        'current_house_no',
        'current_flore',
        'current_street',
        'current_landmark',
        'current_country',
        'current_state',
        'current_city',
        'current_pincode',
        'is_current_address',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
