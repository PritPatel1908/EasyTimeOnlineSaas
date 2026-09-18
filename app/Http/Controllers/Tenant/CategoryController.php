<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreCategoryRequest;
use App\Http\Requests\Tenant\UpdateCategoryRequest;
use App\Jobs\Tenant\GenerateCategoryExport;
use App\Jobs\Tenant\ProcessCategoryImport;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoryController extends Controller
{
    private const INDEX_URL = 'employee-structure/categories';

    public function index(): View
    {
        return view('employee-structure.categories.index', ['categories' => Category::query()->latest('id')->paginate(15)->withQueryString()]);
    }

    public function show(Request $request): View
    {
        return view('employee-structure.categories.show', ['category' => Category::query()->findOrFail($request->route('category'))]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate(['status' => ['nullable', 'in:all,1,0']]);
        $query = Category::query()->latest('id');
        if (($validated['status'] ?? 'all') !== 'all') {
            $query->where('status', (int) $validated['status']);
        }
        $categories = $query->get();
        return response()->json(['html' => view('employee-structure.categories.partials.rows', compact('categories'))->render(), 'count' => $categories->count()]);
    }

    public function create(): View
    {
        return view('employee-structure.categories.add', [
            'category' => null,
            'companies' => Company::query()->where('status', 1)->orderBy('name')->get(),
            'locations' => Location::query()->where('status', 1)->orderBy('name')->get(),
            'leaveTypes' => LeaveType::query()->where('status', 1)->orderBy('description')->get(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $validated = $request->validated();
            $slabs = $validated['c_off_against_wo_hl_slabs'] ?? [];
            $otSlabs = $validated['c_off_against_ot_slabs'] ?? [];
            unset($validated['c_off_against_wo_hl_slabs']);
            unset($validated['c_off_against_ot_slabs']);
            $category = Category::query()->create($validated);
            $this->syncCoffSlabs($category, $slabs);
            $this->syncCoffOtSlabs($category, $otSlabs);
        });
        return redirect(url(self::INDEX_URL))->with('success', 'Category created successfully.');
    }

    public function edit(Request $request): View
    {
        return view('employee-structure.categories.edit', [
            'category' => Category::query()->findOrFail($request->route('category')),
            'companies' => Company::query()->where('status', 1)->orderBy('name')->get(),
            'locations' => Location::query()->where('status', 1)->orderBy('name')->get(),
            'leaveTypes' => LeaveType::query()->where('status', 1)->orderBy('description')->get(),
        ]);
    }

    public function update(UpdateCategoryRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $validated = $request->validated();
            $slabs = $validated['c_off_against_wo_hl_slabs'] ?? [];
            $otSlabs = $validated['c_off_against_ot_slabs'] ?? [];
            unset($validated['c_off_against_wo_hl_slabs']);
            unset($validated['c_off_against_ot_slabs']);
            $category = Category::query()->findOrFail($request->route('category'));
            $category->update($validated);
            $this->syncCoffSlabs($category, $slabs);
            $this->syncCoffOtSlabs($category, $otSlabs);
        });
        return redirect(url(self::INDEX_URL))->with('success', 'Category updated successfully.');
    }

    private function syncCoffSlabs(Category $category, array $slabs): void
    {
        $category->c_off_against_wo_hl_slabs()->delete();
        foreach ($slabs as $slab) {
            if (($slab['from_time'] ?? '') === '' && ($slab['to_time'] ?? '') === '' && ($slab['credit_days'] ?? '') === '') {
                continue;
            }
            $category->c_off_against_wo_hl_slabs()->create($slab);
        }
    }

    private function syncCoffOtSlabs(Category $category, array $slabs): void
    {
        $category->c_off_against_ot_slabs()->delete();
        foreach ($slabs as $slab) {
            if (($slab['from_hours'] ?? '') === '' && ($slab['to_hours'] ?? '') === '' && ($slab['credit_days'] ?? '') === '') {
                continue;
            }
            foreach (['from_hours', 'to_hours'] as $field) {
                if (($slab[$field] ?? '') !== '') {
                    [$hours, $minutes] = array_map('intval', explode(':', $slab[$field]));
                    $slab[$field] = $hours + ($minutes / 60);
                }
            }
            $category->c_off_against_ot_slabs()->create($slab);
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        $category = Category::query()->findOrFail($request->route('category'));
        if ($category->hasRelatedRecords()) {
            return redirect(url(self::INDEX_URL))->with('error', $category->getRelatedRecordsMessage('Category'));
        }
        $category->delete();
        return redirect(url(self::INDEX_URL))->with('success', 'Category deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'categories_' . now()->format('Ymd_His') . '.csv';
        GenerateCategoryExport::dispatch(Auth::guard('tenant')->id(), $fileName)->onConnection('database_tenant')->onQueue('export');
        return redirect(url(self::INDEX_URL))->with('success', 'Category export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle !== false) {
                fputcsv($handle, Category::IMPORT_EXPORT_COLUMNS);
                fclose($handle);
            }
        }, 'categories_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);
        $validated = $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'], 'update_duplicate_records' => ['nullable', 'boolean']]);
        /** @var UploadedFile $file */
        $file = $request->file('file');
        $storedPath = $file->storeAs('category-imports', 'category_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension());
        ProcessCategoryImport::dispatch(Auth::guard('tenant')->id(), $storedPath, (bool) ($validated['update_duplicate_records'] ?? false))->onConnection('database_tenant')->onQueue('import');
        return redirect(url(self::INDEX_URL))->with('success', 'Category import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Acategories_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'category-exports/' . $fileName;
        if (! Storage::disk('local')->exists($path)) {
            (new GenerateCategoryExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);
        return response()->download(Storage::disk('local')->path($path), 'categories.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
