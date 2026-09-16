<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Jobs\Tenant\ActivityLog;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Traits\CUDby;
use App\Traits\HasRelatedRecords;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CanteenFacility extends \Illuminate\Database\Eloquent\Model
{
    use CUDby, HasRelatedRecords, SoftDeletes;

    public const IMPORT_EXPORT_COLUMNS = [
        'name',
        'code',
        'total_cfa',
        'status',
        'location',
        'total_absent_days',
        'company_contribution_in_percentage_wise',
        'company_allowance_contribution_in_fixed',
        'company_allowance_contribution_in_percentage',
    ];

    protected $table = 'canteen_facilities';

    protected $fillable = ['name', 'code', 'total_cfa', 'status', 'location_id', 'created_by', 'updated_by', 'deleted_by'];

    protected $casts = ['total_cfa' => 'decimal:2', 'status' => 'integer', 'location_id' => 'integer'];

    protected static function booted(): void
    {
        static::addGlobalScope(new DataPolicyFilter);
        static::created(fn(self $record) => $record->audit('created', [], $record->getAttributes()));
        static::updating(fn(self $record) => $record->auditLogOldSnapshot = $record->getOriginal());
        static::updated(fn(self $record) => $record->audit('updated', $record->auditLogOldSnapshot, $record->getAttributes()));
        static::deleting(fn(self $record) => $record->auditLogOldSnapshot = $record->getAttributes());
        static::deleted(fn(self $record) => $record->audit('deleted', $record->auditLogOldSnapshot, $record->getAttributes()));
    }

    protected array $auditLogOldSnapshot = [];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(CanteenFacilityRule::class);
    }

    public static function getImportUniqueFields(): array
    {
        return ['code', 'name'];
    }

    private function audit(string $event, array $old, array $new): void
    {
        ActivityLog::dispatch(Auth::guard('tenant')->user(), $this, $old, $new, $event)
            ->afterCommit()->onConnection('database_tenant')->onQueue('processing');
    }
}
