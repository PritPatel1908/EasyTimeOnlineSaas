<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Traits\CUDby;
use App\Traits\HasRelatedRecords;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
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
    use CUDby;
    use HasRelatedRecords;
    use SoftDeletes;

    public const IMPORT_EXPORT_COLUMNS = [
        'code',
        'name',
        'start_date',
        'end_date',
        'location',
        'company',
        'is_started',
        'is_closed',
        'status',
    ];

    protected $table = 'financial_years';

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'location_id' => 'array',
        'company_id' => 'array',
        'is_started' => 'bool',
        'is_closed' => 'bool',
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

    public static function getImportUniqueFields(): array
    {
        return ['code'];
    }

    public function getLocationRecordsAttribute(): Collection
    {
        return Location::query()
            ->whereIn('id', $this->normalizedIds($this->location_id))
            ->orderBy('name')
            ->get();
    }

    public function getCompanyRecordsAttribute(): Collection
    {
        return Company::query()
            ->whereIn('id', $this->normalizedIds($this->company_id))
            ->orderBy('name')
            ->get();
    }

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

    /**
     * @return array<int, int>
     */
    private function normalizedIds(mixed $ids): array
    {
        if (is_string($ids)) {
            $decoded = json_decode($ids, true);
            $ids = json_last_error() === JSON_ERROR_NONE ? $decoded : [$ids];
        }

        return array_values(array_filter(
            (array) $ids,
            static fn (mixed $id): bool => is_int($id) || (is_string($id) && ctype_digit($id)),
        ));
    }
}
