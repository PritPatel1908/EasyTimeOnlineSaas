<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Jobs\Tenant\ActivityLog;
use App\Traits\CUDby;
use App\Traits\HasRelatedRecords;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Class Designation
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int|null $location_id
 * @property int|null $company_id
 * @property int|null $category_id
 * @property int $status
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Location|null $location
 * @property Company|null $company
 * @property Category|null $category
 * @property Collection|DataPolicy[] $data_policies
 */
class Designation extends Model
{
    use CUDby;
    use HasRelatedRecords;
    use SoftDeletes;

    protected array $auditLogOldSnapshot = [];

    public const IMPORT_EXPORT_COLUMNS = [
        'name',
        'code',
        'status',
        'company',
        'location',
        'category',
    ];

    protected $table = 'designations';

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);
        static::created(fn(Designation $designation) => $designation->dispatchAuditLog([], $designation->getAttributes(), 'created'));
        static::updating(function (Designation $designation): void {
            $designation->auditLogOldSnapshot = $designation->getOriginal();
        });
        static::updated(fn(Designation $designation) => $designation->dispatchAuditLog($designation->auditLogOldSnapshot, $designation->getAttributes(), 'updated'));
        static::deleting(function (Designation $designation): void {
            $designation->auditLogOldSnapshot = $designation->getAttributes();
        });
        static::deleted(fn(Designation $designation) => $designation->dispatchAuditLog($designation->auditLogOldSnapshot, $designation->getAttributes(), 'deleted'));
    }

    protected $casts = [
        'location_id' => 'array',
        'company_id' => 'array',
        'category_id' => 'array',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'name',
        'code',
        'location_id',
        'company_id',
        'category_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function data_policies()
    {
        return $this->belongsToMany(DataPolicy::class, 'data_policy_designation')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function companies(): Collection
    {
        return Company::query()->whereIn('id', $this->idsFrom($this->getRawOriginal('company_id')))->orderBy('name')->get();
    }

    public function locations(): Collection
    {
        return Location::query()->whereIn('id', $this->idsFrom($this->getRawOriginal('location_id')))->orderBy('name')->get();
    }

    public function categories(): Collection
    {
        return Category::query()->whereIn('id', $this->idsFrom($this->getRawOriginal('category_id')))->orderBy('name')->get();
    }

    public static function getImportUniqueFields(): array
    {
        return ['code', 'name'];
    }

    private function dispatchAuditLog(array $old, array $new, string $event): void
    {
        ActivityLog::dispatch(Auth::guard('tenant')->user(), $this, $old, $new, $event)->onQueue('processing');
    }

    private function idsFrom(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
        }

        return array_values(array_filter((array) $value, static fn($id): bool => is_int($id) || ctype_digit((string) $id)));
    }
}
