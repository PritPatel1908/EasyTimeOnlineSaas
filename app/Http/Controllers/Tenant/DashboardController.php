<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tenantId = (string) tenant('id');
        $user = Auth::guard('tenant')->user();
        $userName = $user?->name ?: trim(implode(' ', array_filter([
            $user?->fname,
            $user?->mname,
            $user?->lname,
        ])));

        return view('index', [
            'tenantId' => $tenantId,
            'domain' => request()->getHost(),
            'currentUserName' => $userName !== '' ? $userName : 'User',
            'currentUserProfilePic' => $user?->profile_pic,
            'companyCount' => Company::query()->count(),
            'activeCompanyCount' => Company::query()->where('status', 1)->count(),
            'userCount' => User::query()->count(),
            'locationCount' => Location::query()->count(),
            'recentUsers' => User::query()->latest('id')->limit(5)->get(),
        ]);
    }
}
