<?php

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
        Schema::create('dms_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users', 'id');
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id');
            $table->foreignId('deleted_by')->nullable()->constrained('users', 'id');
            $table->softDeletes();
            $table->timestamps();
        });

        $dms_url = env('DMS_URL', 'http://localhost:8088');
        $dms_username = env('DMS_USERNAME', 'admin');
        $dms_password = env('DMS_PASSWORD', 'admin');

        // dd($dms_url, $dms_username, $dms_password);

        DB::table('dms_settings')->insert([
            [
                'key' => 'sync_employees',
                'value' => '1',
            ],
            [
                'key' => 'sync_areas',
                'value' => '1',
            ],
            [
                'key' => 'sync_machines',
                'value' => '1',
            ],
            [
                'key' => 'auth_type',
                'value' => 'basic',
            ],
            [
                'key' => 'dms_url',
                'value' => $dms_url,
            ],
            [
                'key' => 'dms_username',
                'value' => $dms_username,
            ],
            [
                'key' => 'dms_password',
                'value' => $dms_password,
            ],
            [
                'key' => 'dms_token',
                'value' => null,
            ],
            [
                'key' => 'fetch_att_logs',
                'value' => '0',
            ],
            [
                'key' => 'dms_db_driver',
                'value' => 'sqlsrv',
            ],
            [
                'key' => 'dms_db_host',
                'value' => 'localhost',
            ],
            [
                'key' => 'dms_db_port',
                'value' => '1432',
            ],
            [
                'key' => 'dms_db_database',
                'value' => 'WDMS',
            ],
            [
                'key' => 'dms_db_username',
                'value' => 'sa',
            ],
            [
                'key' => 'dms_db_password',
                'value' => 'indian@1234',
            ],
            [
                'key' => 'dms_last_sync_id',
                'value' => '0',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dms_settings');
    }
};
