<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Category;
use App\Models\Tenant\User;
use App\Notifications\Tenant\CategoryImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateCategoryExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public ?int $userId, public string $fileName) {}
    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null) { return; }
        Auth::shouldUse('tenant'); Auth::guard('tenant')->setUser($user);
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) { return; }
        fputcsv($handle, Category::IMPORT_EXPORT_COLUMNS);
        foreach (Category::query()->orderBy('name')->get() as $category) {
            fputcsv($handle, [$category->name, $category->code, $category->email ?? '', $category->status === 1 ? 'Active' : 'Inactive', $category->companies->pluck('name')->join(', '), $category->locations->pluck('name')->join(', ')]);
        }
        rewind($handle); $contents = stream_get_contents($handle); fclose($handle);
        if ($contents === false || ! Storage::disk('local')->put('category-exports/' . $this->fileName, $contents)) { return; }
        $user->notify(new CategoryImportExportCompleted('export', 'Category export is ready for download.', '/employee-structure/categories/export/download/' . rawurlencode($this->fileName)));
    }
}
