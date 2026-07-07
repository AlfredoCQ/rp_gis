<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permisos del sistema GIS agrupados por módulo.
     */
    private array $permissions = [
        // Capas
        'view-layers',
        'create-layers',
        'edit-layers',
        'delete-layers',
        'reorder-layers',

        // Elementos geográficos
        'view-features',
        'create-features',
        'edit-features',
        'delete-features',
        'restore-features',

        // Categorías
        'view-categories',
        'create-categories',
        'edit-categories',
        'delete-categories',

        // Estilos de capas
        'manage-layer-styles',

        // Campos dinámicos
        'manage-feature-fields',

        // Importación / Exportación
        'export-data',
        'import-data',

        // Usuarios y administración
        'manage-users',
        'manage-roles',

        // Auditoría
        'view-audit-logs',

        // Dashboard
        'view-dashboard',
    ];

    /**
     * Permisos asignados a cada rol.
     */
    private array $rolePermissions = [
        'admin' => '*', // todos los permisos

        'editor' => [
            'view-layers',
            'view-features',
            'create-features',
            'edit-features',
            'delete-features',
            'view-categories',
            'export-data',
            'import-data',
            'view-dashboard',
        ],

        'viewer' => [
            'view-layers',
            'view-features',
            'view-categories',
            'export-data',
            'view-dashboard',
        ],
    ];

    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear todos los permisos
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Crear roles y asignar permisos
        foreach ($this->rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($perms === '*') {
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions($perms);
            }
        }

        $this->command->info('✅ Roles y permisos creados correctamente.');
    }
}
