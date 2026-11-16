<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Builder;

final class CustomReportEmployeeQuery
{
    /**
     * @param array<string, mixed> $filters
     * @return Builder<User>
     */
    public function build(array $filters): Builder
    {
        $query = User::query()->where('user_type', 'employee');

        foreach (CustomReportFilterOptions::definitions() as $key => $definition) {
            $ids = $this->ids($filters[$key] ?? []);
            if ($ids === []) {
                continue;
            }

            if ($definition['json']) {
                $query->where(static function (Builder $query) use ($definition, $ids): void {
                    foreach ($ids as $id) {
                        $query->orWhereJsonContains($definition['column'], $id);
                    }
                });
            } else {
                $query->whereIn($definition['column'], $ids);
            }
        }

        $employeeIds = $this->ids($filters['employee_ids'] ?? []);
        if ($employeeIds !== []) {
            $query->whereIn('id', $employeeIds);
        }

        return $query->orderBy('name');
    }

    /**
     * @return array<int, int>
     */
    private function ids(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map(static fn (mixed $id): int => is_numeric($id) ? (int) $id : 0, $value),
            static fn (int $id): bool => $id > 0,
        )));
    }
}
