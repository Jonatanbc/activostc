<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Only jbarreto and drios keep full access. Everyone else (including former
 * members of the "Administrador" group, which grants superuser) is limited to
 * the "Consulta de disponibilidad" role.
 */
return new class extends Migration
{
    private array $keep = ['jbarreto', 'drios'];

    public function up(): void
    {
        $keepIds = DB::table('users')->whereIn('username', $this->keep)->pluck('id')->all();
        $keepIds = $keepIds ?: [0];

        // 1) Remove everyone else from the "Administrador" group (it grants superuser).
        $adminGroup = DB::table('permission_groups')->where('name', 'Administrador')->first();
        if ($adminGroup) {
            DB::table('users_groups')
                ->where('group_id', $adminGroup->id)
                ->whereNotIn('user_id', $keepIds)
                ->delete();
        }

        // 2) Defensive: clear any per-user superuser flag that isn't one of the two.
        DB::table('users')->whereNotIn('username', $this->keep)->whereNull('deleted_at')
            ->select('id', 'permissions')->orderBy('id')
            ->chunk(500, function ($users) {
                foreach ($users as $u) {
                    $p = json_decode($u->permissions ?: '{}', true) ?: [];
                    if (($p['superuser'] ?? '0') == '1') {
                        $p['superuser'] = '0';
                        DB::table('users')->where('id', $u->id)->update(['permissions' => json_encode($p)]);
                    }
                }
            });

        // 3) Make sure the freed-up users have the availability role.
        $availGroup = DB::table('permission_groups')->where('name', 'Consulta de disponibilidad')->first();
        if ($availGroup) {
            $existing = DB::table('users_groups')->where('group_id', $availGroup->id)->pluck('user_id')->flip();
            DB::table('users')->whereNotIn('username', $this->keep)->whereNull('deleted_at')
                ->select('id')->orderBy('id')
                ->chunk(500, function ($users) use ($availGroup, $existing) {
                    $insert = [];
                    foreach ($users as $u) {
                        if (! $existing->has($u->id)) {
                            $insert[] = ['user_id' => $u->id, 'group_id' => $availGroup->id];
                        }
                    }
                    if ($insert) {
                        DB::table('users_groups')->insert($insert);
                    }
                });
        }
    }

    public function down(): void
    {
        // Not reversible (original group membership is not recorded).
    }
};
