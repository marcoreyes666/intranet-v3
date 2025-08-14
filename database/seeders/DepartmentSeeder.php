<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Sistemas', 'code' => 'SYS'],
            ['name' => 'Recursos Humanos', 'code' => 'RH'],
            ['name' => 'Contabilidad', 'code' => 'CNT'],
            ['name' => 'Compras', 'code' => 'CMP'],
            ['name' => 'Dirección Académica', 'code' => 'DA'],
        ];

        foreach ($items as $it) {
            Department::firstOrCreate(
                ['name' => $it['name']],
                ['code' => $it['code']]
            );
        }
    }
}
