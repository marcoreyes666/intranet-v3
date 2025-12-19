<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Administrador','Rector','Encargado de departamento','Usuario','Sistemas'] as $name) {
            Role::firstOrCreate(['name' => $name]);
        }

        // Admin inicial (Sistemas / Administrador)
        $admin = User::firstOrCreate(
            ['email' => 'admin@intranet.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin123!'),
            ]
        );

        // Le damos ambos roles para que represente a Sistemas con control total
        $admin->syncRoles(['Administrador', 'Sistemas']);
    }
}
