<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catalog of damage / spare-part types with a standard (average) cost.
        Schema::create('damage_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('default_cost', 12, 2)->nullable();
            $table->unsignedBigInteger('category_id')->nullable(); // optional scoping (laptop/desktop)
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Damage records logged against a specific asset (also serves as the damage history).
        Schema::create('asset_damages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('damage_type_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('cost', 12, 2)->nullable();         // real quoted cost
            $table->string('status', 20)->default('reported');  // reported|quoted|repaired|discarded
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('reported_by')->nullable();
            $table->date('reported_at')->nullable();
            $table->date('repaired_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('asset_id');
            $table->index('damage_type_id');
            $table->index('status');
        });

        // Seed common laptop/desktop damage types with example COP costs (editable later).
        $now = now();
        $types = [
            ['name' => 'Teclado', 'default_cost' => 120000],
            ['name' => 'Pantalla', 'default_cost' => 350000],
            ['name' => 'Pantalla táctil', 'default_cost' => 450000],
            ['name' => 'Carcasa', 'default_cost' => 180000],
            ['name' => 'Touchpad', 'default_cost' => 90000],
            ['name' => 'Board (tarjeta madre)', 'default_cost' => 600000],
            ['name' => 'Batería', 'default_cost' => 150000],
            ['name' => 'Bisagra', 'default_cost' => 60000],
            ['name' => 'Cargador', 'default_cost' => 80000],
            ['name' => 'Memoria RAM', 'default_cost' => 130000],
            ['name' => 'Disco / SSD', 'default_cost' => 200000],
            ['name' => 'Ventilador / Cooler', 'default_cost' => 70000],
        ];

        foreach ($types as $type) {
            DB::table('damage_types')->insert([
                'name' => $type['name'],
                'default_cost' => $type['default_cost'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_damages');
        Schema::dropIfExists('damage_types');
    }
};
