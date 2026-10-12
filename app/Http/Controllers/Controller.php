<?php

namespace App\Http\Controllers;

use App\Support\CsvImportTemplate;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

abstract class Controller
{
    protected function validateImportCsv(UploadedFile $file, array $expectedColumns, string $expectedFilename): void
    {
        if ($file->getClientOriginalName() !== $expectedFilename) {
            throw ValidationException::withMessages([
                'file' => ['Select the original ' . $expectedFilename . ' template for this module.'],
            ]);
        }

        $errors = CsvImportTemplate::validateHeaders($file->getRealPath(), $expectedColumns);

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'file' => ['The selected CSV file does not match the required import template. Use the correct module CSV template with columns: ' . implode(', ', $expectedColumns) . '.'],
            ]);
        }
    }
}
