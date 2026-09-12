<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Experience
 *
 * @property int $id
 * @property string|null $previous_employeer
 * @property string|null $designation
 * @property Carbon|null $join_date
 * @property Carbon|null $left_date
 * @property float|null $ctc
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 */
class Experience extends Model
{
    protected $table = 'experiences';

    protected $casts = [
        'join_date' => 'datetime',
        'left_date' => 'datetime',
        'ctc' => 'float',
        'user_id' => 'int',
    ];

    protected $fillable = [
        'previous_employeer',
        'designation',
        'join_date',
        'left_date',
        'ctc',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
