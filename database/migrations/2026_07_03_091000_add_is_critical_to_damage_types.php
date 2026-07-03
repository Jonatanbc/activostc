<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('damage_types', function (Blueprint $table) {
            if (! Schema::hasColumn('damage_types', 'is_critical')) {
                $table->boolean('is_critical')->default(false)->after('default_cost');
            }
        });

        // Components that make the equipment unusable if damaged -> Critical.
        $critical = [
            'Pantalla', 'Pantalla táctil', 'Board (tarjeta madre)',
            'Teclado', 'Pin de carga', 'Cargador', 'Disco / SSD',
        ];
        DB::table('damage_types')->whereIn('name', $critical)->update(['is_critical' => true]);
    }

    public function down(): void
    {
        Schema::table('damage_types', function (Blueprint $table) {
            if (Schema::hasColumn('damage_types', 'is_critical')) {
                $table->dropColumn('is_critical');
            }
        });
    }
};
