<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Education
 *
 * @property int $id
 * @property string|null $education
 * @property int $year
 * @property string|null $university_name
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 */
class Education extends Model
{
    protected $table = 'educations';

    protected $casts = [
        'year' => 'int',
        'user_id' => 'int',
    ];

    protected $fillable = [
        'education',
        'year',
        'university_name',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
