<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('leave_applications')
            ->select(['id', 'user_id'])
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->chunk(500, function ($applications): void {
                $pivotRows = [];

                foreach ($applications as $application) {
                    $exists = DB::table('leave_application_users')
                        ->where('leave_application_id', $application->id)
                        ->where('user_id', $application->user_id)
                        ->exists();

                    if (! $exists) {
                        $pivotRows[] = [
                            'leave_application_id' => $application->id,
                            'user_id' => $application->user_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if ($pivotRows !== []) {
                    DB::table('leave_application_users')->insert($pivotRows);
                }
            });

        Schema::table('leave_applications', function (Blueprint $table): void {
            $table->renameColumn('is_only_second_half', 'is_only_half');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table): void {
            $table->renameColumn('is_only_half', 'is_only_second_half');
            $table->foreignId('user_id')->nullable()->constrained();
        });

        DB::table('leave_applications')
            ->select('id')
            ->orderBy('id')
            ->chunk(500, function ($applications): void {
                foreach ($applications as $application) {
                    $userId = DB::table('leave_application_users')
                        ->where('leave_application_id', $application->id)
                        ->orderBy('user_id')
                        ->value('user_id');

                    if ($userId !== null) {
                        DB::table('leave_applications')
                            ->where('id', $application->id)
                            ->update(['user_id' => $userId]);
                    }
                }
            });
    }
};
