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
        Schema::create('email_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users', 'id');
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id');
            $table->foreignId('deleted_by')->nullable()->constrained('users', 'id');
            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('email_configurations')->insert([
            [
                'key' => 'transport',
                'value' => 'smtp',
            ],
            [
                'key' => 'host',
                'value' => 'smtp.gmail.com',
            ],
            [
                'key' => 'port',
                'value' => '587',
            ],
            [
                'key' => 'username',
                'value' => 'prit89039@gmail.com',
            ],
            [
                'key' => 'password',
                'value' => 'szwpxieuneghttjw',
            ],
            [
                'key' => 'encryption',
                'value' => 'tls',
            ],
            [
                'key' => 'from_address',
                'value' => 'prit89039@gmail.com',
            ],
            [
                'key' => 'from_name',
                'value' => 'EasyTime Online',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_configurations');
    }
};
