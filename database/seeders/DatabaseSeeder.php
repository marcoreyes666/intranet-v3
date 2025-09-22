<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentsSeeder::class,
            ConsolidatedAccessSeeder::class,
            RolesSeeder::class,
            RolesExtraSeeder::class,   // ya crea Compras/Contabilidad
            ApproverUsersSeeder::class,
        ]);
    }
}
