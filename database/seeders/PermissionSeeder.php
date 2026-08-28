<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminPassword = config('services.bootstrap.super_admin_password');

        if (! $superAdminPassword) {
            throw new \LogicException('Configure SUPER_ADMIN_PASSWORD antes de ejecutar PermissionSeeder.');
        }

        $permissions = [
            'program users' => 'Programa - Usuario',
            'users create' => 'Crear Usuario',
            'module setting' => 'Módulo de Configuración',
            'users edit' => 'Editar Usuario',
            'users delete' => 'Eliminar Usuario',
            'program roles' => 'Programa - Roles y Permisos',
            'roles create' => 'Crear Roles y Permisos',
            'roles edit' => 'Editar Roles y Permisos',
            'roles delete' => 'Eliminar Roles y Permisos',
            'program attributes' => 'Programa - Atributos',
            'attributes create' => 'Crear Atributos',
            'attributes edit' => 'Editar Atributos',
            'attributes delete' => 'Eliminar Atributos',
            'program categories' => 'Programa - Categorías',
            'categories create' => 'Crear Categorías',
            'categories edit' => 'Editar Categorías',
            'categories delete' => 'Eliminar Categorías',
            'program tourists' => 'Programa - Lugares Turísticos',
            'tourists create' => 'Agregar Lugares Turísticos',
            'tourists edit' => 'Editar Lugares Turísticos',
            'tourists delete' => 'Eliminar Lugares Turísticos',
            'tourists view' => 'Ver Lugares Turísticos',
            'program events' => 'Programa - Eventos-Noticias',
            'create events' => 'Crear Eventos-Noticias',
            'edit events' => 'Editar Eventos-Noticias',
            'delete events' => 'Eliminar Eventos-Noticias',
            'event categories manage' => 'Administrar Categorías de Eventos',
            'program services' => 'Programa - Servicios',
            'services manage' => 'Administrar Servicios',
            'ai ask' => 'Consultar asistente turístico',
        ];

        foreach ($permissions as $name => $description) {
            Permission::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['description' => $description],
            );
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'web']);
        $superAdmin = User::firstOrCreate(
            ['email' => config('services.bootstrap.super_admin_email')],
            [
                'name' => 'superadmin',
                'password' => Hash::make($superAdminPassword),
                'active' => true,
            ],
        );

        $superAdmin->assignRole($superAdminRole);
    }
}
