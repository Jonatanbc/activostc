<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $groupName = 'Consulta de disponibilidad';

    private array $superadmins = ['jbarreto', 'drios'];

    public function up(): void
    {
        // 1) Ensure the IT admins keep full access (superuser).
        foreach ($this->superadmins as $username) {
            $user = DB::table('users')->where('username', $username)->whereNull('deleted_at')->first();
            if ($user) {
                $perms = json_decode($user->permissions ?: '{}', true) ?: [];
                $perms['superuser'] = '1';
                DB::table('users')->where('id', $user->id)->update(['permissions' => json_encode($perms)]);
            }
        }

        // 2) Give every other (non-superuser) user the availability-only role.
        $group = DB::table('permission_groups')->where('name', $this->groupName)->first();
        if (! $group) {
            return;
        }

        $existing = DB::table('users_groups')->where('group_id', $group->id)->pluck('user_id')->flip();

        DB::table('users')->whereNull('deleted_at')->select('id', 'permissions')->orderBy('id')
            ->chunk(500, function ($users) use ($group, $existing) {
                $insert = [];
                foreach ($users as $u) {
                    $perms = json_decode($u->permissions ?: '{}', true) ?: [];
                    $isSuper = ($perms['superuser'] ?? '0') == '1';
                    if (! $isSuper && ! $existing->has($u->id)) {
                        $insert[] = ['user_id' => $u->id, 'group_id' => $group->id];
                    }
                }
                if ($insert) {
                    DB::table('users_groups')->insert($insert);
                }
            });
    }

    public function down(): void
    {
        $group = DB::table('permission_groups')->where('name', $this->groupName)->first();
        if ($group) {
            DB::table('users_groups')->where('group_id', $group->id)->delete();
        }
    }
};
