<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A purchase request groups several damages together to request a quotation.
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20)->default('open'); // open|quoted|ordered|received|cancelled
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
        });

        // Link each damage to the purchase request that groups it (nullable).
        Schema::table('asset_damages', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_request_id')->nullable()->after('supplier_id');
            $table->index('purchase_request_id');
        });
    }

    public function down(): void
    {
        Schema::table('asset_damages', function (Blueprint $table) {
            $table->dropIndex(['purchase_request_id']);
            $table->dropColumn('purchase_request_id');
        });

        Schema::dropIfExists('purchase_requests');
    }
};
