<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear roles y permisos
        $this->call(RolePermissionSeeder::class);

        // 2. Crear usuario administrador inicial
        $admin = User::firstOrCreate(
            ['email' => 'admin@gis.local'],
            [
                'name'     => 'Administrador GIS',
                'password' => Hash::make('Admin1234!'),
                'status'   => true,
            ]
        );
        $admin->assignRole('admin');
        $this->command->info('✅ Admin creado: admin@gis.local / Admin1234!');

        // 3. Crear usuario editor de prueba
        $editor = User::firstOrCreate(
            ['email' => 'editor@gis.local'],
            [
                'name'     => 'Editor GIS',
                'password' => Hash::make('Editor1234!'),
                'status'   => true,
            ]
        );
        $editor->assignRole('editor');
        $this->command->info('✅ Editor creado: editor@gis.local / Editor1234!');

        // 4. Crear usuario visor de prueba
        $viewer = User::firstOrCreate(
            ['email' => 'viewer@gis.local'],
            [
                'name'     => 'Visor GIS',
                'password' => Hash::make('Viewer1234!'),
                'status'   => true,
            ]
        );
        $viewer->assignRole('viewer');
        $this->command->info('✅ Visor creado: viewer@gis.local / Viewer1234!');

        // 5. Sembrar establecimientos de salud y límites del Callao
        $this->call(HealthCentersSeeder::class);
    }
}
