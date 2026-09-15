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
 * Class Unit
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int|null $location_id
 * @property int $status
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Location|null $location
 * @property Collection|DataPolicy[] $data_policies
 */
class Unit extends Model
{
    use CUDby;
    use HasRelatedRecords;
    use SoftDeletes;

    protected array $auditLogOldSnapshot = [];

    public const IMPORT_EXPORT_COLUMNS = [
        'name',
        'code',
        'status',
        'location',
    ];

    protected $table = 'units';

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);

        static::created(function (Unit $unit): void {
            $unit->dispatchAuditLog([], $unit->auditSnapshot($unit->getAttributes()), 'created');
        });

        static::updating(function (Unit $unit): void {
            $unit->auditLogOldSnapshot = $unit->auditSnapshot($unit->getOriginal());
        });

        static::updated(function (Unit $unit): void {
            $unit->dispatchAuditLog($unit->auditLogOldSnapshot, $unit->auditSnapshot($unit->getAttributes()), 'updated');
        });

        static::deleting(function (Unit $unit): void {
            $unit->auditLogOldSnapshot = $unit->auditSnapshot($unit->getAttributes());
        });

        static::deleted(function (Unit $unit): void {
            $unit->dispatchAuditLog($unit->auditLogOldSnapshot, $unit->auditSnapshot($unit->getAttributes()), 'deleted');
        });
    }

    protected $casts = [
        'location_id' => 'array',
        'status' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'name',
        'code',
        'location_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function users()
    {
        return $this->user();
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function getLocationsAttribute(): Collection
    {
        return Location::query()
            ->whereIn('id', $this->idsFrom($this->getRawOriginal('location_id')))
            ->orderBy('name')
            ->get();
    }

    public function locations()
    {
        return $this->getLocationsAttribute();
    }

    public function data_policies()
    {
        return $this->belongsToMany(DataPolicy::class, 'data_policy_unit')
            ->withPivot('id')
            ->withTimestamps();
    }

    public static function getImportUniqueFields(): array
    {
        return ['code', 'name'];
    }

    private function dispatchAuditLog(array $old, array $new, string $event): void
    {
        ActivityLog::dispatch(Auth::guard('tenant')->user(), $this, $old, $new, $event)->onQueue('processing');
    }

    private function auditSnapshot(array $attributes): array
    {
        return array_merge($attributes, [
            'relations' => [
                'locations' => $this->relatedRecords(Location::class, $this->idsFrom($attributes['location_id'] ?? null)),
                'data_policies' => $this->relatedRecords(DataPolicy::class, $this->relationIds('data_policies')),
                'created_by' => $this->actorRecord($attributes['created_by'] ?? null),
                'updated_by' => $this->actorRecord($attributes['updated_by'] ?? null),
                'deleted_by' => $this->actorRecord($attributes['deleted_by'] ?? null),
            ],
        ]);
    }

    private function idsFrom(mixed $value): array
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

        return User::query()->find($id)?->only(['id', 'name', 'email']);
    }
}
