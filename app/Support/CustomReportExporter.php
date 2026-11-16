<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\ReportFormat;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;
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

        fputcsv($handle, $headers);
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
        $export = new class(array_merge([$headers], $rows)) implements FromArray
        {
            /**
             * @param array<int, array<int, string>> $table
             */
            public function __construct(private array $table) {}

            public function array(): array
            {
                return $this->table;
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
