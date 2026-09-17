<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('employee-structure.categories.index', compact('categories'));
    }
}
