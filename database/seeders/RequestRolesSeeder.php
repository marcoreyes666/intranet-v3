<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Department; // Asegúrate de tener este modelo
use Illuminate\Support\Facades\Hash;

class RequestRolesSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Roles exactos que usa resolverAprobador()
        foreach (['Encargado de departamento','Contabilidad','Compras','Rector'] as $name) {
            Role::firstOrCreate(['name' => $name]);
        }

        // 2) Departamentos usados en el ejemplo (ajusta nombres a los tuyos)
        $sistemas = Department::firstOrCreate(['name' => 'Sistemas']);
        $conta    = Department::firstOrCreate(['name' => 'Contabilidad']);
        $compras  = Department::firstOrCreate(['name' => 'Compras']);

        // 3) Usuarios demo (cámbialos en producción)
        $encargado = User::firstOrCreate(
            ['email' => 'encargado@intranet.test'],
            [
                'name'           => 'Encargado Demo',
                'password'       => Hash::make('Password123!'),
                'department_id'  => $sistemas->id, // Clave para el paso "encargado"
            ]
        );
        $encargado->assignRole('Encargado de departamento');

        $contaUser = User::firstOrCreate(
            ['email' => 'conta@intranet.test'],
            [
                'name'           => 'Contabilidad Demo',
                'password'       => Hash::make('Password123!'),
                'department_id'  => $conta->id,
            ]
        );
        $contaUser->assignRole('Contabilidad');

        $comprasUser = User::firstOrCreate(
            ['email' => 'compras@intranet.test'],
            [
                'name'           => 'Compras Demo',
                'password'       => Hash::make('Password123!'),
                'department_id'  => $compras->id,
            ]
        );
        $comprasUser->assignRole('Compras');

        $rector = User::firstOrCreate(
            ['email' => 'rector@intranet.test'],
            [
                'name'     => 'Rector Demo',
                'password' => Hash::make('Password123!'),
            ]
        );
        $rector->assignRole('Rector');
    }
}
