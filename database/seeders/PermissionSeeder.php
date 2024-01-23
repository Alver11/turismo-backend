<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::create(['name' => 'Super-Admin']);
        $superAdmin = User::factory()->create([
            'name' => 'superadmin',
            'email' => 'superadmin@admin.com',
            'password' => bcrypt("12345678"),
            'active' => true,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
        $superAdmin->assignRole($superAdminRole);
    }
}
