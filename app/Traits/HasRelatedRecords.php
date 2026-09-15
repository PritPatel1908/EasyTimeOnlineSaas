<?php

declare(strict_types=1);

namespace App\Traits;

use App\Helpers\DatabaseReferenceChecker;
use Illuminate\Support\Str;

/**
 * Trait HasRelatedRecords
 *
 * Provides dynamic delete-protection for Eloquent models by checking whether the
 * model's primary key is referenced by any other table before allowing deletion.
 *
 * Two reference styles are supported automatically:
 *   - Direct FK:   a plain integer column (e.g. `company_id = 5`)
 *   - JSON FK:     a JSON-array column    (e.g. `company_id = [3, 5, 8]`)
 *
 * ── How to use ────────────────────────────────────────────────────────────────
 *
 * 1. Add the trait to your model.
 * 2. Override `$referenceColumns` if the FK column name(s) differ from the
 *    default (which is `singular(table)_id`, e.g. "companies" → "company_id").
 * 3. Optionally override `$referenceConnectionName` if the related tables live
 *    on a different database connection.
 *
 * Example — single default column:
 *
 *   class Department extends Model
 *   {
 *       use HasRelatedRecords;
 *       // referenceColumns defaults to ['department_id'] — no override needed
 *   }
 *
 * Example — multiple columns / custom names:
 *
 *   class Category extends Model
 *   {
 *       use HasRelatedRecords;
 *       protected array $referenceColumns = ['category_id'];
 *   }
 *
 * ── Controller usage ──────────────────────────────────────────────────────────
 *
 *   public function destroy(Request $request): RedirectResponse
 *   {
 *       $record = MyModel::findOrFail($request->route('id'));
 *
 *       if ($record->hasRelatedRecords()) {
 *           return back()->with('error', $record->getRelatedRecordsMessage('MyModel'));
 *       }
 *
 *       $record->delete();
 *       return back()->with('success', 'Deleted successfully.');
 *   }
 */
trait HasRelatedRecords
{
    /**
     * The FK column names to search for across all other tables.
     *
     * Leave empty to auto-derive from the model's table name
     * (e.g. table "departments" → ["department_id"]).
     *
     * Override in the model to specify one or more column names:
     *
     *   protected array $referenceColumns = ['category_id'];
     *
     * @var array<int, string>
     */
    protected array $referenceColumns = [];

    /**
     * The database connection name to use when scanning other tables.
     * Null means the model's own connection (most common case).
     */
    protected ?string $referenceConnectionName = null;

    /**
     * Returns true if any other table holds a reference to this record's PK.
     *
     * Checks both plain integer columns and JSON-array columns transparently.
     */
    public function hasRelatedRecords(): bool
    {
        return DatabaseReferenceChecker::hasReferences(
            referencedTable: $this->getTable(),
            referencedId: $this->getKey(),
            referenceColumns: $this->resolvedReferenceColumns(),
            connectionName: $this->referenceConnectionName,
        );
    }

    /**
     * Returns the first table+column pair that references this record, or null.
     *
     * Useful for building detailed error messages.
     *
     * @return array{table: string, column: string}|null
     */
    public function findRelatedRecord(): ?array
    {
        return DatabaseReferenceChecker::findReferences(
            referencedTable: $this->getTable(),
            referencedId: $this->getKey(),
            referenceColumns: $this->resolvedReferenceColumns(),
            connectionName: $this->referenceConnectionName,
        );
    }

    /**
     * Returns all table+column pairs that reference this record.
     *
     * @return array<int, array{table: string, column: string}>
     */
    public function findAllRelatedRecords(): array
    {
        return DatabaseReferenceChecker::findAllReferences(
            referencedTable: $this->getTable(),
            referencedId: $this->getKey(),
            referenceColumns: $this->resolvedReferenceColumns(),
            connectionName: $this->referenceConnectionName,
        );
    }

    /**
     * Returns a human-readable error message listing every table that still
     * references this record.
     *
     * @param  string|null  $label  Display name for the record type (e.g. "Company").
     *                              Falls back to the table name.
     */
    public function getRelatedRecordsMessage(?string $label = null): string
    {
        $reference = $this->findRelatedRecord();

        if ($reference === null) {
            return '';
        }

        $label = $label ?? ucfirst(str_replace('_', ' ', $this->getTable()));

        return "This {$label} cannot be deleted because it is still in use.";
    }

    /**
     * Resolves the effective list of reference column names.
     *
     * Uses the explicit $referenceColumns array when provided; otherwise derives
     * a single column name from the model's table name via Str::singular().
     *
     * @return array<int, string>
     */
    private function resolvedReferenceColumns(): array
    {
        if (! empty($this->referenceColumns)) {
            return $this->referenceColumns;
        }

        // Auto-derive: "departments" → "department_id"
        return [Str::singular($this->getTable()).'_id'];
    }
}
