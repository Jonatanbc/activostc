<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onboarding_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('onboarding_requests', 'otm_roles')) {
                $table->json('otm_roles')->nullable()->after('platforms');
            }
        });
    }

    public function down(): void
    {
        Schema::table('onboarding_requests', function (Blueprint $table) {
            if (Schema::hasColumn('onboarding_requests', 'otm_roles')) {
                $table->dropColumn('otm_roles');
            }
        });
    }
};
