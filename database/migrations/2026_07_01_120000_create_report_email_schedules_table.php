<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recurring email schedules for reports (e.g. damages by model).
        Schema::create('report_email_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('report_key', 50)->default('damages_by_model');
            $table->text('recipients');                 // comma-separated emails
            $table->string('frequency', 20);            // weekly|biweekly|monthly
            $table->boolean('only_pending')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'next_run_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_email_schedules');
    }
};
