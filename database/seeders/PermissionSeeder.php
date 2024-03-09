<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::create(['name' => 'Super-Admin']);
        //Cambiar luego a samtunc 'guard_name' => 'sanctum'
        $superAdmin = User::factory()->create([
            'name' => 'superadmin',
            'email' => 'superadmin@admin.com',
            'password' => bcrypt("12345678"),
            'active' => true,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
        $superAdmin->assignRole($superAdminRole);

        Permission::create(['name' => 'program users', 'description' => 'Programa - Usuario']);
        Permission::create(['name' => 'users create', 'description' => 'Crear Usuario']);
        Permission::create(['name' => 'module setting', 'description' => 'Modulo de Configuración']);
        Permission::create(['name' => 'users edit', 'description' => 'Editar Usuario']);
        Permission::create(['name' => 'users delete', 'description' => 'Eliminar Usuario']);
        Permission::create(['name' => 'program roles', 'description' => 'Programa - Roles y Permisos']);
        Permission::create(['name' => 'roles create', 'description' => 'Crear Roles y Permisos']);
        Permission::create(['name' => 'roles edit', 'description' => 'Editar Roles y Permisos']);
        Permission::create(['name' => 'roles delete', 'description' => 'Eliminar Roles y Permisos']);
        Permission::create(['name' => 'program attribute', 'description' => 'Programa - Atributos']);
        Permission::create(['name' => 'attributes edit', 'description' => 'Editar Atributos']);
        Permission::create(['name' => 'attributes delete', 'description' => 'Eliminar Atributos']);
        Permission::create(['name' => 'program categories', 'description' => 'Programa - Categorias']);
        Permission::create(['name' => 'categories create', 'description' => 'Crear Categorias']);
        Permission::create(['name' => 'categories edit', 'description' => 'Editar Categorias']);
        Permission::create(['name' => 'categories delete', 'description' => 'Eliminar Categorias']);
        Permission::create(['name' => 'program tourists', 'description' => 'Programa - Lugares Turisticos']);
        Permission::create(['name' => 'tourists create', 'description' => 'Agregar Lugares Turisticos']);
        Permission::create(['name' => 'attributes create', 'description' => 'Crear Atributos']);
        Permission::create(['name' => 'tourists edit', 'description' => 'Editar Lugares Turisticos']);
        Permission::create(['name' => 'tourists delete', 'description' => 'Eliminar Lugares Turisticos']);
        Permission::create(['name' => 'tourists view', 'description' => 'Ver Lugares Turisticos']);

    }
}
