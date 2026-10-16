<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\ManualLeaveBalanceController;
use App\Jobs\Tenant\GenerateManualLeaveBalanceExport;
use App\Jobs\Tenant\ProcessManualLeaveBalanceImport;
use App\Models\Tenant\User;
use App\Support\CsvImportTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantManualLeaveBalanceImportExportTest extends TestCase
{
    public function test_import_sample_uses_manual_leave_balance_columns(): void
    {
        $response = app(ManualLeaveBalanceController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame([
            'employee_codes',
            'location',
            'company',
            'leave_type_code',
            'balance',
            'date',
            'reason',
        ], $rows[0]);
        $this->assertTrue(CsvImportTemplate::isHintRow($rows[1]));
        $this->assertCount(7, $rows[1]);
        $this->assertStringContainsString('names or codes', $rows[1][1]);
        $this->assertStringContainsString('names or codes', $rows[1][2]);
        $this->assertStringContainsString('YYYY-MM-DD HH:MM', $rows[1][5]);
        $this->assertSame('location', CsvImportTemplate::normalizeHeader('location_codes'));
        $this->assertSame('company', CsvImportTemplate::normalizeHeader('company_codes'));
    }

    public function test_export_dispatches_manual_leave_balance_queue_job(): void
    {
        Bus::fake();
        $this->mockTenantUser(17);

        $response = app(ManualLeaveBalanceController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('Manual leave balance export has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(GenerateManualLeaveBalanceExport::class);
        $job = Bus::dispatched(GenerateManualLeaveBalanceExport::class)->first();
        $this->assertSame(17, $job->userId);
        $this->assertSame('database_tenant', $job->connection);
        $this->assertSame('export', $job->queue);
        $this->assertMatchesRegularExpression('/\Amanual_leave_balances_\d{8}_\d{6}\.csv\z/', $job->fileName);
    }

    public function test_import_dispatches_manual_leave_balance_queue_job(): void
    {
        Bus::fake();
        Storage::fake('local');
        $this->mockTenantUser(1);
        $file = UploadedFile::fake()->createWithContent(
            'manual_leave_balances_import_sample.csv',
            "employee_codes,location,company,leave_type_code,balance,date,reason\nE001,,,CL,1.5,2026-01-01 09:45,Correction\n",
        );
        $request = Request::create(
            '/employee-structure/manual-leave-balances/import',
            'POST',
            [],
            [],
            ['file' => $file],
        );

        $response = app(ManualLeaveBalanceController::class)->import($request);

        $this->assertStringContainsString('Manual leave balance import has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(ProcessManualLeaveBalanceImport::class, function (ProcessManualLeaveBalanceImport $job): bool {
            return $job->userId === 1
                && $job->updateDuplicateRecords === false
                && str_contains($job->filePath, 'manual-leave-balance-imports/');
        });
    }

    private function mockTenantUser(int $id): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAuthIdentifier')->andReturn($id);
        $user->shouldReceive('getAttribute')->with('id')->andReturn($id);
        $user->shouldReceive('getKey')->andReturn($id);
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn($id);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);
    }
}
