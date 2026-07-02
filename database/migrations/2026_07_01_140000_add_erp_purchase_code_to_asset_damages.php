<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_damages', function (Blueprint $table) {
            // ERP purchase-request code (typed by the user, e.g. from SAP/Siigo/etc.)
            $table->string('erp_purchase_code', 100)->nullable()->after('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::table('asset_damages', function (Blueprint $table) {
            $table->dropColumn('erp_purchase_code');
        });
    }
};
