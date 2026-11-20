<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\ReportFormat;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

class CustomReportExporter
{
    /**
     * @param array<int, string> $headers
     * @param array<int, array<int, string>> $rows
     */
    public function store(
        ReportFormat $format,
        array $headers,
        string $reportName,
        string $orientation,
        string $path,
        array $rows = [],
    ): void {
        match ($format) {
            ReportFormat::csv => $this->storeCsv($headers, $rows, $path),
            ReportFormat::xlsx => $this->storeXlsx($headers, $rows, $path),
            ReportFormat::pdf => $this->storePdf($headers, $rows, $reportName, $orientation, $path),
        };
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, array<int, string>> $rows
     */
    private function storeCsv(array $headers, array $rows, string $path): void
    {
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            throw new RuntimeException('Unable to create the custom report CSV.');
        }

        if ($headers !== []) {
            fputcsv($handle, $headers);
        }
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            throw new RuntimeException('Unable to save the custom report CSV.');
        }
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, array<int, string>> $rows
     */
    private function storeXlsx(array $headers, array $rows, string $path): void
    {
        $table = $headers === [] ? $rows : array_merge([$headers], $rows);
        $table = array_map(static fn (array $row): array => $row === [] ? [' '] : $row, $table);
        $export = new class($table) implements FromArray, WithStyles
        {
            /**
             * @param array<int, array<int, string>> $table
             */
            public function __construct(private array $table) {}

            public function array(): array
            {
                return $this->table;
            }

            public function styles(Worksheet $sheet): ?array
            {
                if ($this->table === []) {
                    return [];
                }

                $sectionStart = null;
                $sectionEnd = null;
                $sectionColumnCount = 0;
                $applySectionOutline = static function (int $startRow, int $endRow, int $columnCount) use ($sheet): void {
                    $range = 'A'.$startRow.':'.Coordinate::stringFromColumnIndex($columnCount).$endRow;
                    $sheet->getStyle($range)->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => ['argb' => 'FF404040'],
                            ],
                        ],
                    ]);
                };

                foreach ($this->table as $index => $row) {
                    $hasContent = array_filter(
                        $row,
                        static fn (mixed $value): bool => $value !== null && trim((string) $value) !== '',
                    ) !== [];

                    if (! $hasContent) {
                        $nextRowFirstValue = trim((string) ($this->table[$index + 1][0] ?? ''));
                        $isEmployeeSeparator = ! in_array($nextRowFirstValue, ['Date', 'Summary'], true);
                        if ($isEmployeeSeparator && $sectionStart !== null) {
                            $applySectionOutline($sectionStart, $sectionEnd, $sectionColumnCount);
                            $sectionStart = null;
                            $sectionEnd = null;
                            $sectionColumnCount = 0;
                        }
                        $sheet->getCell('A'.($index + 1))->setValue(null);
                        $sheet->getRowDimension($index + 1)->setRowHeight(12);

                        continue;
                    }

                    $sectionStart ??= $index + 1;
                    $sectionEnd = $index + 1;
                    $sectionColumnCount = max($sectionColumnCount, count($row));
                    $rowRange = 'A'.($index + 1).':'.Coordinate::stringFromColumnIndex(count($row)).($index + 1);
                    $sheet->getStyle($rowRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FFD9D9D9'],
                            ],
                        ],
                    ]);
                }

                if ($sectionStart !== null) {
                    $applySectionOutline($sectionStart, $sectionEnd, $sectionColumnCount);
                }

                return [];
            }
        };

        if (! Excel::store($export, $path, 'local', \Maatwebsite\Excel\Excel::XLSX)) {
            throw new RuntimeException('Unable to save the custom report Excel file.');
        }
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, array<int, string>> $rows
     */
    private function storePdf(array $headers, array $rows, string $reportName, string $orientation, string $path): void
    {
        if (! class_exists(\Dompdf\Dompdf::class)) {
            throw new RuntimeException('PDF export is unavailable because the Dompdf package is not installed.');
        }

        $pdf = new \Dompdf\Dompdf;
        $pdf->loadHtml(View::make('employee-structure.custom-reports.export', [
            'headers' => $headers,
            'rows' => $rows,
            'reportName' => $reportName,
        ])->render());
        $pdf->setPaper('a4', $orientation === 'horizontal' ? 'landscape' : 'portrait');
        $pdf->render();
        $contents = $pdf->output();

        if (! Storage::disk('local')->put($path, $contents)) {
            throw new RuntimeException('Unable to save the custom report PDF.');
        }
    }
}
