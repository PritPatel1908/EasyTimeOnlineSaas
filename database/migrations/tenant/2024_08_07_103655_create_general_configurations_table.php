<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('general_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users', 'id');
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id');
            $table->foreignId('deleted_by')->nullable()->constrained('users', 'id');
            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('general_configurations')->insert([
            [
                'key' => 'create_new_week_of_change_in_user_edit',
                'value' => '1',
            ],
            [
                'key' => 'create_new_shift_change_in_user_edit',
                'value' => '1',
            ],
            [
                'key' => 'process_another_in_punch',
                'value' => '0',
            ],
            [
                'key' => 'attendance_schedule_time',
                'value' => '05:00:00',
            ],
            [
                'key' => 'first_time_muster_date',

                'value' => Carbon::today()->format('d-m-Y'),
            ],
            [
                'key' => 'last_log_process_time',
                'value' => null,
            ],
            [
                'key' => 'monthly_leave_check_date',
                'value' => null,
            ],
            [
                'key' => 'yearly_leave_check_date',
                'value' => null,
            ],
            [
                'key' => 'datetime_format',
                'value' => 'd-m-Y h:i A',
            ],
            [
                'key' => 'date_format',
                'value' => 'd-m-Y',
            ],
            [
                'key' => 'time_format',
                'value' => 'h:i A',
            ],
            [
                'key' => 'report_requires_approval',
                'value' => '0',
            ],
            [
                'key' => 'punch_direction_consider_same',
                'value' => '2',
            ],
            [
                'key' => 'password_attempts_count',
                'value' => '5',
            ],
            [
                'key' => 'default_email',
                'value' => 'support@indianinfotech.org',
            ],
            // [
            //     'key' => 'flow_check_order',
            //     'value' => '["area", "sub_department","department", "company","location"]'
            // ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_configurations');
    }
};
