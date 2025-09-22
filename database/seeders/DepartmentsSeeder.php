<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        // Ajusta a tu organigrama real
        $items = [
            'Rectoría',
            'Dirección Académica',
            'Sistemas',
            'Contabilidad',
            'Compras',
            'Servicios Escolares',
            'Recursos Humanos',
            'Mantenimiento',
        ];

        foreach ($items as $name) {
            Department::firstOrCreate(['name' => $name]);
        }
    }
}
