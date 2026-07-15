<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Role that can ONLY see the "Disponibilidad de equipos" module. */
    private string $name = 'Consulta de disponibilidad';

    public function up(): void
    {
        if (! DB::table('permission_groups')->where('name', $this->name)->exists()) {
            DB::table('permission_groups')->insert([
                'name' => $this->name,
                'permissions' => json_encode(['equipos.availability' => '1']),
                'notes' => 'Solo acceso al módulo de disponibilidad de equipos.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('permission_groups')->where('name', $this->name)->delete();
    }
};
