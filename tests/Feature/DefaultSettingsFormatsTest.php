<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\SettingsController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DefaultSettingsFormatsTest extends TestCase
{
    public function test_default_settings_create_missing_values_and_update_existing_ones(): void
    {
        $this->createGeneralConfigurationsTable();

        DB::table('general_configurations')->insert([
            ['key' => 'datetime_format', 'value' => 'old-format'],
            ['key' => 'punch_direction_consider_same', 'value' => '2'],
            ['key' => 'attendance_schedule_time', 'value' => '05:00:00'],
            ['key' => 'monthly_leave_check_date', 'value' => null],
            ['key' => 'yearly_leave_check_date', 'value' => null],
            ['key' => 'password_attempts_count', 'value' => '5'],
        ]);

        $settingsView = app(SettingsController::class)->show('tenant', 'default-settings');
        $defaultSettings = $settingsView->getData()['defaultSettings'];
        $this->assertSame('2', $defaultSettings['punch_direction_consider_same']);
        $this->assertSame('05:00:00', $defaultSettings['attendance_schedule_time']);
        $this->assertNull($defaultSettings['monthly_leave_check_date']);
        $this->assertNull($defaultSettings['yearly_leave_check_date']);
        $this->assertSame('5', $defaultSettings['password_attempts_count']);

        $request = Request::create('/default-settings', 'PUT', [
            'datetime_format' => 'd/m/Y H:i',
            'date_format' => 'custom',
            'custom_date_format' => 'jS F Y',
            'time_format' => 'h:i A',
            'punch_direction_consider_same' => '3',
            'attendance_schedule_time' => '06:30:00',
            'monthly_leave_check_date' => '15',
            'yearly_leave_check_date' => '31',
            'password_attempts_count' => '6',
            'allowed_files' => ['jpg', 'png'],
            'max_file_size' => '5000',
        ]);

        $response = app(SettingsController::class)->updateDefaultSettings($request);
        $this->assertSame(url('default-settings'), $response->getTargetUrl());

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
        $this->assertDatabaseHas('general_configurations', [
            'key' => 'punch_direction_consider_same',
            'value' => '3',
        ]);
        $this->assertDatabaseHas('general_configurations', [
            'key' => 'attendance_schedule_time',
            'value' => '06:30:00',
        ]);
        $this->assertDatabaseHas('general_configurations', [
            'key' => 'monthly_leave_check_date',
            'value' => '15',
        ]);
        $this->assertDatabaseHas('general_configurations', [
            'key' => 'yearly_leave_check_date',
            'value' => '31',
        ]);
        $this->assertDatabaseHas('general_configurations', [
            'key' => 'password_attempts_count',
            'value' => '6',
        ]);
        $this->assertDatabaseHas('general_configurations', [
            'key' => 'allowed_files',
            'value' => '["jpg","png"]',
        ]);
        $this->assertDatabaseHas('general_configurations', [
            'key' => 'max_file_size',
            'value' => '5000',
        ]);

        $savedSettingsView = app(SettingsController::class)->show('tenant', 'default-settings');
        $this->assertSame(
            ['jpg', 'png'],
            $savedSettingsView->getData()['selectedAllowedFiles']
        );
        $this->assertSame('5000', $savedSettingsView->getData()['defaultSettings']['max_file_size']);
    }

    public function test_leave_credit_dates_must_be_between_one_and_thirty_one(): void
    {
        $this->createGeneralConfigurationsTable();

        $request = Request::create('/default-settings', 'PUT', [
            'datetime_format' => 'd/m/Y H:i',
            'date_format' => 'd/m/Y',
            'time_format' => 'h:i A',
            'punch_direction_consider_same' => '2',
            'attendance_schedule_time' => '05:00:00',
            'monthly_leave_check_date' => '32',
            'yearly_leave_check_date' => '0',
            'password_attempts_count' => '5',
        ]);

        $this->expectException(ValidationException::class);
        app(SettingsController::class)->updateDefaultSettings($request);
    }

    public function test_company_basic_information_is_saved_and_loaded_from_general_configurations(): void
    {
        $this->createGeneralConfigurationsTable();
        Storage::fake('public');

        $imageKeys = [
            'company_white_logo',
            'company_dark_logo',
            'company_white_mini_logo',
            'company_dark_mini_logo',
            'company_favicon',
            'company_apple_icon',
        ];
        $files = [];
        foreach ($imageKeys as $key) {
            $files[$key] = UploadedFile::fake()->image($key.'.png', 32, 32);
        }

        $request = Request::create('/company-settings', 'PUT', [
            'company_name' => 'SmartHR',
            'company_email' => 'admin@example.com',
            'company_phone' => '+91 1234567890',
            'company_web' => 'https://example.com',
        ], [], $files);

        $response = app(SettingsController::class)->updateCompanySettings($request);

        $this->assertSame(url('default-settings'), $response->getTargetUrl());
        $this->assertDatabaseHas('general_configurations', [
            'key' => 'company_name',
            'value' => 'SmartHR',
        ]);
        $this->assertDatabaseHas('general_configurations', [
            'key' => 'company_email',
            'value' => 'admin@example.com',
        ]);
        $this->assertDatabaseHas('general_configurations', [
            'key' => 'company_phone',
            'value' => '+91 1234567890',
        ]);
        $this->assertDatabaseHas('general_configurations', [
            'key' => 'company_web',
            'value' => 'https://example.com',
        ]);
        foreach ($imageKeys as $key) {
            $storedPath = DB::table('general_configurations')->where('key', $key)->value('value');
            $this->assertIsString($storedPath);
            Storage::disk('public')->assertExists($storedPath);
        }

        $settingsView = app(SettingsController::class)->show('tenant', 'default-settings');
        $defaultSettings = $settingsView->getData()['defaultSettings'];
        $this->assertSame('SmartHR', $defaultSettings['company_name']);
        $this->assertSame('admin@example.com', $defaultSettings['company_email']);
        $this->assertSame('+91 1234567890', $defaultSettings['company_phone']);
        $this->assertSame('https://example.com', $defaultSettings['company_web']);
        foreach ($imageKeys as $key) {
            $this->assertStringContainsString(
                '/company-images/'.$key,
                $settingsView->getData()['companyImageUrls'][$key]
            );
        }
    }

    private function createGeneralConfigurationsTable(): void
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
    }
}
