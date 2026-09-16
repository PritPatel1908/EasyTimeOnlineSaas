<?php

namespace Tests\Unit;

use App\Http\Controllers\Tenant\NotificationController;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    public function test_it_reads_preview_for_canteen_facility_export(): void
    {
        Storage::fake('local');

        $fileName = 'canteen_facilities_20240601_120000.csv';
        $path = 'canteen-facility-exports/' . $fileName;
        Storage::disk('local')->put($path, "id,name\n1,Alpha\n2,Beta\n");

        $controller = new NotificationController();
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke($controller, '/storage/' . $path);

        $this->assertNull($error);
        $this->assertSame(['id', 'name'], $preview['headers']);
        $this->assertCount(2, $preview['rows']);
    }
}
