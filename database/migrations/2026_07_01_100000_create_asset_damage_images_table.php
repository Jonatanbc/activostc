<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Photos attached to a specific damage record (many photos per damage).
        Schema::create('asset_damage_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_damage_id');
            $table->string('filename');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('asset_damage_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_damage_images');
    }
};
