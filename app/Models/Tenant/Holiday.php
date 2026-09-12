<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Holiday
 *
 * @property int $id
 * @property string $name
 * @property Carbon $date
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection|HolidayCategory[] $holiday_categories
 * @property Collection|Location[] $locations
 * @property Collection|Shift[] $shifts
 * @property Collection|Category[] $categories
 */
class Holiday extends Model
{
    use SoftDeletes;

    protected $table = 'holidays';

    protected $casts = [
        'date' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'date',
    ];

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'holiday_locations')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'holiday_categories')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'holiday_shifts')
            ->withPivot('id')
            ->withTimestamps();
    }
}
