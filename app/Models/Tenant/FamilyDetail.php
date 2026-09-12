<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FamilyDetail
 *
 * @property int $id
 * @property string|null $relation
 * @property string|null $name
 * @property Carbon|null $dob
 * @property bool $is_nominee
 * @property string|null $occupation
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 */
class FamilyDetail extends Model
{
    protected $table = 'family_details';

    protected $casts = [
        'dob' => 'datetime',
        'is_nominee' => 'bool',
        'user_id' => 'int',
    ];

    protected $fillable = [
        'relation',
        'name',
        'dob',
        'is_nominee',
        'occupation',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
