<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('damage_types')->where('name', 'Pin de carga')->exists()) {
            DB::table('damage_types')->insert([
                'name' => 'Pin de carga',
                'default_cost' => 80000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('damage_types')->where('name', 'Pin de carga')->delete();
    }
};
