<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // No factories aquí
        $this->call([
            RolesSeeder::class,
        ]);

        $this->call(DepartmentSeeder::class);

        // Elimina/ comenta cualquier User::factory()...
        // User::factory(10)->create();
        // User::factory()->create([...]);
    }
}
