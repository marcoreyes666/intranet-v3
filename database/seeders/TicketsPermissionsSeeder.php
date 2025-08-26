<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TicketsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'tickets.ver_todos',
            'tickets.crear',
            'tickets.editar',
            'tickets.eliminar',
            'tickets.asignar',
            'tickets.cambiar_estado',
        ];
        foreach ($perms as $p) Permission::firstOrCreate(['name'=>$p]);

        Role::findByName('Administrador')->givePermissionTo($perms);
        Role::findByName('Encargado de departamento')->givePermissionTo([
            'tickets.ver_todos','tickets.editar','tickets.asignar','tickets.cambiar_estado'
        ]);
        Role::findByName('Rector')->givePermissionTo(['tickets.ver_todos']);
        Role::findByName('Usuario')->givePermissionTo(['tickets.crear']);
    }
}
