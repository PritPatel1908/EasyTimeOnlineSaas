<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approved_application_effect_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->morphs('approvable');
            $table->json('snapshot');
            $table->string('cancellation_status')->nullable();
            $table->unsignedBigInteger('cancellation_requested_by')->nullable();
            $table->timestamp('cancellation_requested_at')->nullable();
            $table->text('cancellation_error')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['approvable_type', 'approvable_id'], 'approved_effect_snapshot_approvable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approved_application_effect_snapshots');
    }
};
