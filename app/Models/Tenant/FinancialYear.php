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
 * Class FinancialYear
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property bool $is_started
 * @property bool $is_closed
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property int $status
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 */
class FinancialYear extends Model
{
    use SoftDeletes;

    // use CUDby;
    protected $table = 'financial_years';

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'location_id' => 'array',
        'company_id' => 'array',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'location_id',
        'company_id',
        'code',
        'name',
        'start_date',
        'end_date',
        'is_started',
        'is_closed',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function grade_wise_leaves()
    {
        return $this->hasMany(LeaveGroup::class, 'financial_year_id');
    }
}
