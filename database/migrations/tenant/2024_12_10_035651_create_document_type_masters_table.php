<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_type_masters', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('document_type');
            $table->foreignId('document_type_master_id')->nullable()->constrained('document_type_masters');
            $table->string('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_type_masters');
    }
};
