<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\OvertimeRuleController;
use App\Jobs\Tenant\GenerateOvertimeRuleExport;
use App\Jobs\Tenant\ProcessOvertimeRuleImport;
use App\Models\Tenant\OvertimeRule;
use App\Models\Tenant\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantOvertimeRuleImportExportTest extends TestCase
{
    public function test_import_export_columns_match_rule_and_slab_fields(): void
    {
        $this->assertSame([
            'code',
            'description',
            'ignore_early_come_minutes',
            'ignore_late_come_minutes',
            'ignore_late_going',
            'ignore_overtime',
            'ignore_late_going_overtime',
            'status',
            'slab_value_from',
            'slab_value_to',
            'slab_head_value',
            'slab_decimal_value',
        ], OvertimeRule::IMPORT_EXPORT_COLUMNS);
    }

    public function test_configured_overtime_deductions_are_subtracted(): void
    {
        $rule = new OvertimeRule([
            'ignore_overtime' => 10.5,
            'ignore_late_going_overtime' => 15,
        ]);

        $this->assertSame(0.0, $rule->qualifyingOvertimeMinutes(10.5));
        $this->assertSame(9.0, $rule->qualifyingOvertimeMinutes(20.0));

        $rule->ignore_late_going = true;
        $this->assertSame(0.0, $rule->qualifyingOvertimeMinutes(20.0));
    }

    public function test_coming_time_thresholds_are_applied_before_overtime_is_calculated(): void
    {
        $rule = new OvertimeRule([
            'ignore_early_come_minutes' => '00:10',
            'ignore_late_come_minutes' => '00:05',
            'ignore_overtime' => 0,
            'ignore_late_going_overtime' => 0,
        ]);

        $this->assertSame(10, $rule->ignoreEarlyComingMinutesValue());
        $this->assertSame(5, $rule->ignoreLateComingMinutesValue());

        $this->assertSame([
            'early' => 15,
            'late' => 10,
        ], $rule->adjustedComingMinutes(25, 15));
    }

    public function test_overtime_rule_deductions_are_applied_before_slab_matching(): void
    {
        $rule = new OvertimeRule([
            'ignore_early_come_minutes' => '00:30',
            'ignore_late_come_minutes' => '00:30',
            'ignore_late_going' => true,
            'ignore_late_going_overtime' => 15,
            'ignore_overtime' => 10,
        ]);

        $this->assertSame(15, $rule->calculateOvertimeMinutes(
            earlyComingMinutes: 60,
            lateComingMinutes: 40,
            lateGoingMinutes: 25,
            earlyGoingMinutes: 5,
        ));
    }

    public function test_late_coming_within_the_allowance_does_not_reduce_overtime(): void
    {
        $rule = new OvertimeRule([
            'ignore_late_come_minutes' => '00:30',
            'ignore_overtime' => 0,
        ]);

        $this->assertSame(50, $rule->calculateOvertimeMinutes(
            earlyComingMinutes: 0,
            lateComingMinutes: 30,
            lateGoingMinutes: 50,
            earlyGoingMinutes: 0,
        ));
    }

    public function test_import_sample_matches_the_model_columns(): void
    {
        $response = app(OvertimeRuleController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString(
            'overtime_rules_import_sample.csv',
            $response->headers->get('Content-Disposition', ''),
        );

        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(OvertimeRule::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertCount(count(OvertimeRule::IMPORT_EXPORT_COLUMNS), $rows[1]);
    }

    public function test_export_dispatches_the_tenant_queue_job(): void
    {
        Bus::fake();
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $response = app(OvertimeRuleController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString(
            'Overtime rule export has started',
            $response->getSession()->get('success', ''),
        );
        Bus::assertDispatched(GenerateOvertimeRuleExport::class, function (GenerateOvertimeRuleExport $job): bool {
            return $job->userId === 17
                && $job->connection === 'database_tenant'
                && $job->queue === 'export'
                && preg_match('/\Aovertime_rules_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    public function test_import_dispatches_a_job_after_validating_csv_headers(): void
    {
        Bus::fake();
        Storage::fake('local');
        $user = Mockery::mock(User::class);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $headers = implode(',', OvertimeRule::IMPORT_EXPORT_COLUMNS);
        $file = UploadedFile::fake()->createWithContent(
            'overtime_rules_import.csv',
            $headers."\nDefault,Sample,00:15,00:10,Yes,5.5,10.25,Active,00:00,01:00,00:00,1.5\n",
        );
        $request = Request::create('/time-management/overtime-rules/import', 'POST', [], [], [
            'file' => $file,
        ]);

        $response = app(OvertimeRuleController::class)->import($request);

        $this->assertStringContainsString(
            'Overtime rule import has started',
            $response->getSession()->get('success', ''),
        );
        Bus::assertDispatched(ProcessOvertimeRuleImport::class, function (ProcessOvertimeRuleImport $job): bool {
            return $job->userId === 17
                && ! $job->updateDuplicateRecords
                && str_contains($job->filePath, 'overtime-rule-imports/');
        });
    }

    public function test_export_download_only_accepts_overtime_rule_export_names(): void
    {
        Storage::fake('local');
        $fileName = 'overtime_rules_20261020_123456.csv';
        Storage::disk('local')->put(
            'overtime-rule-exports/'.$fileName,
            implode(',', OvertimeRule::IMPORT_EXPORT_COLUMNS)."\nDefault,Sample,00:15,00:10,Yes,5.5,10.25,Active,00:00,01:00,00:00,1.5\n",
        );

        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn(null);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $response = app(OvertimeRuleController::class)->downloadExport($fileName);

        $this->assertStringContainsString('overtime_rules.csv', $response->headers->get('Content-Disposition', ''));
    }
}
