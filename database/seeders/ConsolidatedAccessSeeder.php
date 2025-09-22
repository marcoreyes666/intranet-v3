<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\PermissionRegistrar;

class ConsolidatedAccessSeeder extends Seeder
{
    public function run(): void
    {
        // Limpia caché de Spatie para evitar resultados inconsistentes
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /** ----------------------------------------------------------------
         * 1) Roles
         * ----------------------------------------------------------------*/
        $roles = [
            'Administrador',
            'Rector',
            'Encargado de departamento',
            'Usuario',
            'Contabilidad',
            'Compras',
        ];

        foreach ($roles as $name) {
            Role::firstOrCreate(['name' => $name]);
        }

        /** ----------------------------------------------------------------
         * 2) Permisos (ajusta/expande cuando agregues módulos nuevos)
         * ----------------------------------------------------------------*/
        $perms = [
            // Solicitudes de permiso
            'solicitudes.crear',
            'solicitudes.ver',
            'solicitudes.aprobar',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        /** ----------------------------------------------------------------
         * 3) Asignación de permisos por rol
         * ----------------------------------------------------------------*/
        Role::findByName('Administrador')->givePermissionTo($perms);
        Role::findByName('Rector')->givePermissionTo(['solicitudes.ver','solicitudes.aprobar']);
        Role::findByName('Encargado de departamento')->givePermissionTo(['solicitudes.ver','solicitudes.aprobar']);
        Role::findByName('Usuario')->givePermissionTo(['solicitudes.crear','solicitudes.ver']);

        // Roles "Contabilidad" y "Compras" no reciben permisos aquí,
        // pero quedan creados para otros módulos/flujo que implementes.

        /** ----------------------------------------------------------------
         * 4) Departamentos base (ajusta nombres a tu catálogo real)
         * ----------------------------------------------------------------*/
        $sistemas = Department::firstOrCreate(['name' => 'Sistemas']);
        $conta    = Department::firstOrCreate(['name' => 'Contabilidad']);
        $compras  = Department::firstOrCreate(['name' => 'Compras']);

        /** ----------------------------------------------------------------
         * 5) Usuarios demo (usa variables de entorno si quieres cambiarlos)
         *    En producción, reemplázalos o deshabilítalos.
         * ----------------------------------------------------------------*/
        // Admin
        $admin = User::firstOrCreate(
            ['email' => env('DEMO_ADMIN_EMAIL', 'admin@intranet.test')],
            [
                'name' => env('DEMO_ADMIN_NAME', 'Admin'),
                'password' => Hash::make(env('DEMO_ADMIN_PASS', 'Admin123!')),
                // 'department_id' => null,
            ]
        );
        $admin->syncRoles(['Administrador']); // asegura rol único

        // Encargado de departamento (Sistemas)
        $encargado = User::firstOrCreate(
            ['email' => env('DEMO_ENC_EMAIL', 'encargado@intranet.test')],
            [
                'name'           => env('DEMO_ENC_NAME', 'Encargado Demo'),
                'password'       => Hash::make(env('DEMO_ENC_PASS', 'Password123!')),
                'department_id'  => $sistemas->id,
            ]
        );
        $encargado->syncRoles(['Encargado de departamento']);

        // Usuario Contabilidad
        $contaUser = User::firstOrCreate(
            ['email' => env('DEMO_CONTA_EMAIL', 'conta@intranet.test')],
            [
                'name'           => env('DEMO_CONTA_NAME', 'Contabilidad Demo'),
                'password'       => Hash::make(env('DEMO_CONTA_PASS', 'Password123!')),
                'department_id'  => $conta->id,
            ]
        );
        $contaUser->syncRoles(['Contabilidad']);

        // Usuario Compras
        $comprasUser = User::firstOrCreate(
            ['email' => env('DEMO_COMPRAS_EMAIL', 'compras@intranet.test')],
            [
                'name'           => env('DEMO_COMPRAS_NAME', 'Compras Demo'),
                'password'       => Hash::make(env('DEMO_COMPRAS_PASS', 'Password123!')),
                'department_id'  => $compras->id,
            ]
        );
        $comprasUser->syncRoles(['Compras']);

        // Rector
        $rector = User::firstOrCreate(
            ['email' => env('DEMO_RECTOR_EMAIL', 'rector@intranet.test')],
            [
                'name'     => env('DEMO_RECTOR_NAME', 'Rector Demo'),
                'password' => Hash::make(env('DEMO_RECTOR_PASS', 'Password123!')),
                // 'department_id' => null,
            ]
        );
        $rector->syncRoles(['Rector']);
    }
}
