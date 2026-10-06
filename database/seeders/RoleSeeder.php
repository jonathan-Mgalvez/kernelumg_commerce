<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Acceso y control total sobre el sistema, usuarios, transacciones y configuraciones.',
            ],
            [
                'name' => 'Gestor de Inventario',
                'slug' => 'inventory_manager',
                'description' => 'Mantenimiento del catálogo de productos, control de existencias, categorías y ofertas.',
            ],
            [
                'name' => 'Cliente',
                'slug' => 'customer',
                'description' => 'Usuario registrado con acceso a historial de compras, persistencia de carrito y seguimiento.',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}