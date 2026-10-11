<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('current_flat_building')->nullable();
            $table->string('current_house_no')->nullable();
            $table->string('current_flore')->nullable();
            $table->string('current_street')->nullable();
            $table->string('current_landmark')->nullable();
            $table->string('current_country')->nullable();
            $table->string('current_state')->nullable();
            $table->string('current_city')->nullable();
            $table->string('current_pincode')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn([
                'current_flat_building',
                'current_house_no',
                'current_flore',
                'current_street',
                'current_landmark',
                'current_country',
                'current_state',
                'current_city',
                'current_pincode',
            ]);
        });
    }
};
