<?php

namespace App\Policies;

class DamageTypePolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'maintenances';
    }
}
