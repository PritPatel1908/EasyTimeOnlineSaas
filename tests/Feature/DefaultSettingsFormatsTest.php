<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\SettingsController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DefaultSettingsFormatsTest extends TestCase
{
    public function test_default_formats_create_missing_settings_and_update_existing_ones(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('general_configurations', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('general_configurations')->insert([
            'key' => 'datetime_format',
            'value' => 'old-format',
        ]);

        $request = Request::create('/default-settings', 'PUT', [
            'datetime_format' => 'd/m/Y H:i',
            'date_format' => 'custom',
            'custom_date_format' => 'jS F Y',
            'time_format' => 'h:i A',
        ]);

        app(SettingsController::class)->updateDefaultFormats($request);

        $this->assertDatabaseHas('general_configurations', [
            'key' => 'datetime_format',
            'value' => 'd/m/Y H:i',
        ]);
        $this->assertDatabaseHas('general_configurations', [
            'key' => 'date_format',
            'value' => 'jS F Y',
        ]);
        $this->assertDatabaseHas('general_configurations', [
            'key' => 'time_format',
            'value' => 'h:i A',
        ]);
    }
}
