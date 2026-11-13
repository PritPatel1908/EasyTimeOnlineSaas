<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class SidebarPermissionVisibilityTest extends TestCase
{
    public function test_leave_and_settings_sections_are_hidden_without_permissions(): void
    {
        URL::defaults(['tenant' => 'sample']);
        view()->share('errors', new ViewErrorBag);
        Auth::guard('tenant')->logout();

        $html = view('partials.sidebar', ['companyImageUrls' => []])->render();

        $this->assertStringNotContainsString('LEAVE STRUCTURE', $html);
        $this->assertStringNotContainsString('SETTINGS STRUCTURE', $html);
    }
}
