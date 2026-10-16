<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class CsvImportTemplate
{
    private const HINT_ROW_PREFIX = 'HINT (ignored during import): ';

    /**
     * @param resource $handle
     * @param array<int, string> $columns
     * @param array<string, string> $hints
     */
    public static function write($handle, array $columns, array $hints): void
    {
        $missingHints = array_diff($columns, array_keys($hints));

        if ($missingHints !== []) {
            throw new InvalidArgumentException('Every CSV template column must have an import hint.');
        }

        fputcsv($handle, $columns);

        $hintRow = [];

        foreach ($columns as $index => $column) {
            $hint = $hints[$column];
            $hintRow[] = $index === 0 ? self::HINT_ROW_PREFIX . $hint : $hint;
        }

        fputcsv($handle, $hintRow);
    }

    /**
     * @param array<int, mixed> $row
     */
    public static function isHintRow(array $row): bool
    {
        return isset($row[0]) && str_starts_with((string) $row[0], self::HINT_ROW_PREFIX);
    }

    /**
     * @param array<int, string> $expectedColumns
     * @return array<int, string>
     */
    public static function validateHeaders(string $path, array $expectedColumns, bool $allowAdditionalColumns = false): array
    {
        if (! is_readable($path)) {
            return ['CSV file is not readable.'];
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            return ['CSV file could not be opened.'];
        }

        $headerRow = fgetcsv($handle);
        fclose($handle);

        if ($headerRow === false || $headerRow === []) {
            return ['CSV file is empty or missing a header row.'];
        }

        $normalizedHeaders = array_map(static fn(string $header): string => self::normalizeHeader($header), $headerRow);
        $expected = array_map(static fn(string $column): string => self::normalizeHeader($column), $expectedColumns);

        $missing = array_values(array_diff($expected, $normalizedHeaders));
        $unexpected = $allowAdditionalColumns ? [] : array_values(array_diff($normalizedHeaders, $expected));

        if ($missing !== [] || $unexpected !== []) {
            $messages = [];

            if ($missing !== []) {
                $messages[] = 'Missing columns: ' . implode(', ', $missing) . '.';
            }

            if ($unexpected !== []) {
                $messages[] = 'Unexpected columns: ' . implode(', ', $unexpected) . '.';
            }

            return $messages;
        }

        return [];
    }

    public static function normalizeHeader(string $header): string
    {
        $normalized = preg_replace('/[^a-z0-9]+/i', '_', trim($header));
        $normalized = strtolower(trim((string) $normalized, '_'));

        $aliases = [
            'company_name' => 'name',
            'company_code' => 'code',
            'company_email' => 'email',
            'location_name' => 'name',
            'location_code' => 'code',
            'location_email' => 'email',
            'department_name' => 'name',
            'department_code' => 'code',
            'department_email' => 'email',
            'team_name' => 'name',
            'team_code' => 'code',
            'team_email' => 'email',
            'unit_name' => 'name',
            'unit_code' => 'code',
            'category_name' => 'name',
            'category_code' => 'code',
            'designation_name' => 'name',
            'designation_code' => 'code',
            'employee_name' => 'name',
            'employee_code' => 'code',
            'employee_email' => 'email',
            'location' => 'location',
            'location_codes' => 'location',
            'company' => 'company',
            'company_codes' => 'company',
            'department' => 'department',
        ];

        return $aliases[$normalized] ?? $normalized;
    }
}
