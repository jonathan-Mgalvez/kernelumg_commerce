<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $managerRole = Role::where('slug', 'inventory_manager')->first();
        $customerRole = Role::where('slug', 'customer')->first();

        $users = [
            [
                'role_id' => $adminRole->id,
                'name' => 'Administrador General',
                'email' => 'admin@kernelumg.com',
                'password' => Hash::make('Admin123*Seguro'),
                'phone' => '+502 5555-0101',
                'address' => 'Sede Central KernelUMG, Guatemala',
                'is_active' => true,
            ],
            [
                'role_id' => $managerRole->id,
                'name' => 'Gestor de Bodega',
                'email' => 'bodega@kernelumg.com',
                'password' => Hash::make('Gestor123*Bodega'),
                'phone' => '+502 5555-0102',
                'address' => 'Bodega Central, Guatemala',
                'is_active' => true,
            ],
            [
                'role_id' => $customerRole->id,
                'name' => 'Cliente Inicial',
                'email' => 'cliente@kernelumg.com',
                'password' => Hash::make('Cliente123*Seguro'),
                'phone' => '+502 5555-0103',
                'address' => 'Zona 1, Ciudad de Guatemala',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);
        }
    }
}