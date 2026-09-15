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
 * Class SubDepartment
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $email
 * @property int|null $location_id
 * @property int $department_id
 * @property int $status
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Department $department
 * @property Location|null $location
 * @property Collection|DataPolicy[] $data_policies
 * @property Collection|User[] $users
 */
class SubDepartment extends Model
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
        'department',
        'location',
    ];

    protected $table = 'sub_departments';

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);

        static::created(function (SubDepartment $subDepartment): void {
            $subDepartment->dispatchAuditLog([], $subDepartment->auditSnapshot($subDepartment->getAttributes()), 'created');
        });

        static::updating(function (SubDepartment $subDepartment): void {
            $subDepartment->auditLogOldSnapshot = $subDepartment->auditSnapshot($subDepartment->getOriginal());
        });

        static::updated(function (SubDepartment $subDepartment): void {
            $subDepartment->dispatchAuditLog($subDepartment->auditLogOldSnapshot, $subDepartment->auditSnapshot($subDepartment->getAttributes()), 'updated');
        });

        static::deleting(function (SubDepartment $subDepartment): void {
            $subDepartment->auditLogOldSnapshot = $subDepartment->auditSnapshot($subDepartment->getAttributes());
        });

        static::deleted(function (SubDepartment $subDepartment): void {
            $subDepartment->dispatchAuditLog($subDepartment->auditLogOldSnapshot, $subDepartment->auditSnapshot($subDepartment->getAttributes()), 'deleted');
        });
    }

    protected $casts = [
        'location_id' => 'array',
        'department_id' => 'array',
        'created_by' => 'int',
        'status' => 'int',
        'updated_by' => 'int',
        'deleted_by' => 'int',
    ];

    protected $fillable = [
        'name',
        'code',
        'email',
        'location_id',
        'department_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function getDepartmentsAttribute(): Collection
    {
        return Department::query()
            ->whereIn('id', $this->idsFrom($this->getRawOriginal('department_id')))
            ->orderBy('name')
            ->get();
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function data_policies()
    {
        return $this->belongsToMany(DataPolicy::class, 'data_policy_sub_department')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function departments()
    {
        return $this->getDepartmentsAttribute();
    }

    public function locations()
    {
        return Location::query()
            ->whereIn('id', $this->idsFrom($this->getRawOriginal('location_id')))
            ->orderBy('name')
            ->get();
    }

    public static function getImportUniqueFields(): array
    {
        return ['code', 'name'];
    }

    private function dispatchAuditLog(array $old, array $new, string $event): void
    {
        ActivityLog::dispatch(Auth::guard('tenant')->user(), $this, $old, $new, $event)
            ->onConnection('database_tenant')
            ->onQueue('processing');
    }

    private function auditSnapshot(array $attributes): array
    {
        return array_merge($attributes, [
            'relations' => [
                'departments' => $this->relatedRecords(Department::class, $this->idsFrom($attributes['department_id'] ?? null)),
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

        return array_values(array_filter((array) $value, static fn ($id): bool => is_int($id) || ctype_digit((string) $id)));
    }

    private function relationIds(string $relation): array
    {
        return $this->relationLoaded($relation)
            ? $this->{$relation}->pluck('id')->all()
            : $this->{$relation}()->withoutGlobalScopes()->pluck('data_policies.id')->all();
    }
}
