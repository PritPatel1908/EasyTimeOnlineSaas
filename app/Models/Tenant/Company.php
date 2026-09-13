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
 * Class Company
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $status
 * @property string|null $email
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $logo_path
 * @property User|null $user
 * @property Collection|Location[] $locations
 * @property Collection|DataPolicy[] $data_policies
 * @property Collection|Company[] $Companies
 * @property Collection|Shift[] $shifts
 * @property Collection|User[] $users
 */
class Company extends Model
{
    use CUDby;
    use HasRelatedRecords;
    use SoftDeletes;

    protected array $auditLogOldSnapshot = [];

    public const IMPORT_EXPORT_COLUMNS = [
        'name',
        'code',
        'email',
        'status',
        'location',
    ];

    protected $table = 'companies';

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);

        static::created(function (Company $company): void {
            $company->dispatchAuditLog([], $company->auditSnapshot($company->getAttributes()), 'created');
        });

        static::updating(function (Company $company): void {
            $company->auditLogOldSnapshot = $company->auditSnapshot($company->getOriginal());
        });

        static::updated(function (Company $company): void {
            $company->dispatchAuditLog($company->auditLogOldSnapshot, $company->auditSnapshot($company->getAttributes()), 'updated');
        });

        static::deleting(function (Company $company): void {
            $company->auditLogOldSnapshot = $company->auditSnapshot($company->getAttributes());
        });

        static::deleted(function (Company $company): void {
            $company->dispatchAuditLog($company->auditLogOldSnapshot, $company->auditSnapshot($company->getAttributes()), 'deleted');
        });
    }

    protected $casts = [
        'location_id' => 'array',
        'created_by' => 'int',
        'updated_by' => 'int',
        'status' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'name',
        'code',
        'email',
        'status',
        'location_id',
        'logo_path',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function getLocationsAttribute(): Collection
    {
        $locationIds = $this->getRawOriginal('location_id');

        if (is_string($locationIds)) {
            $decodedLocationIds = json_decode($locationIds, true);
            $locationIds = json_last_error() === JSON_ERROR_NONE ? $decodedLocationIds : [$locationIds];
        }

        if (! is_array($locationIds)) {
            $locationIds = $locationIds === null ? [] : [$locationIds];
        }

        $locationIds = array_values(array_filter($locationIds, static fn($locationId): bool => is_int($locationId) || ctype_digit((string) $locationId)));

        return Location::query()
            ->whereIn('id', $locationIds)
            ->orderBy('name')
            ->get();
    }


    public function data_policies()
    {
        return $this->belongsToMany(DataPolicy::class, 'data_policy_company')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function Companies()
    {
        return $this->hasMany(Company::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public static function getImportUniqueFields(): array
    {
        return ['code', 'name'];
    }

    private function dispatchAuditLog(array $old, array $new, string $event): void
    {
        ActivityLog::dispatch(Auth::user(), $this, $old, $new, $event)->onQueue('processing');
    }

    private function auditSnapshot(array $attributes): array
    {
        return array_merge($attributes, [
            'relations' => [
                'locations' => $this->relatedRecords(Location::class, $this->locationIdsFrom($attributes['location_id'] ?? null)),
                'data_policies' => $this->relatedRecords(DataPolicy::class, $this->relationIds('data_policies')),
                'created_by' => $this->actorRecord($attributes['created_by'] ?? null),
                'updated_by' => $this->actorRecord($attributes['updated_by'] ?? null),
                'deleted_by' => $this->actorRecord($attributes['deleted_by'] ?? null),
            ],
        ]);
    }

    private function locationIdsFrom(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
        }

        return array_values(array_filter((array) $value, static fn($id): bool => is_int($id) || ctype_digit((string) $id)));
    }

    private function relationIds(string $relation): array
    {
        $relationQuery = $this->{$relation}();
        $relatedModel = $relationQuery->getRelated();

        return $relationQuery->pluck($relatedModel->qualifyColumn($relatedModel->getKeyName()))->all();
    }

    private function relatedRecords(string $model, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return $model::query()
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'code'])
            ->map(static fn(Model $record): array => $record->only(['id', 'name', 'code']))
            ->values()
            ->all();
    }

    private function actorRecord(mixed $id): ?array
    {
        if ($id === null || $id === '') {
            return null;
        }

        $actor = User::query()->find($id);

        return $actor?->only(['id', 'name', 'email']);
    }
}
