<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesExtraSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Contabilidad','Compras'] as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }
}
