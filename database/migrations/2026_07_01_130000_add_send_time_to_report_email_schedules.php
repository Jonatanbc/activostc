<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_email_schedules', function (Blueprint $table) {
            // Day of week (0=Sunday .. 6=Saturday) and time (HH:MM), in Colombia time.
            $table->unsignedTinyInteger('send_day')->nullable()->after('frequency');
            $table->string('send_time', 5)->nullable()->after('send_day'); // "08:00"
        });
    }

    public function down(): void
    {
        Schema::table('report_email_schedules', function (Blueprint $table) {
            $table->dropColumn(['send_day', 'send_time']);
        });
    }
};
