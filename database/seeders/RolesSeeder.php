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
        foreach (['Administrador','Rector','Encargado de departamento','Usuario'] as $name) {
            Role::firstOrCreate(['name' => $name]);
        }

        // Admin inicial
        $admin = User::firstOrCreate(
            ['email' => 'admin@intranet.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin123!'),
            ]
        );

        $admin->syncRoles(['Administrador']);
    }
}
