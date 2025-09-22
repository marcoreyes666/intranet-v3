<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ApproverUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Compras
        $compras = User::updateOrCreate(
            ['email' => 'compras@intranet.test'],
            ['name' => 'Usuario Compras', 'password' => Hash::make('Compras123!')]
        );
        $compras->syncRoles(['Compras']);

        // Contabilidad
        $conta = User::updateOrCreate(
            ['email' => 'contabilidad@intranet.test'],
            ['name' => 'Usuario Contabilidad', 'password' => Hash::make('Contabilidad123!')]
        );
        $conta->syncRoles(['Contabilidad']);
    }
}