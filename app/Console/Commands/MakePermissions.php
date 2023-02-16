<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MakePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sdk:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea los roles y permisos';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->createRoles();
        $this->createPermissions();

    }

    protected function createPermissions()
    {
        $models = ['user'];
        $permisions = ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'];

        foreach ($models as $model) {
            foreach ($permisions as $permision) {
                $this->findPermissionOrCreate($model, $permision);
            }
        }
    }

    protected function createRoles()
    {
        $roles = ['super-admin', 'admin', 'user'];

        foreach ($roles as $rol) {
            $this->findRoleOrCreate($rol);
        }
    }

    private function findRoleOrCreate(string $role): Role
    {
        return Role::firstOrCreate(['name' => $role]);
    }

    private function findPermissionOrCreate(string $model, string $permision): Permission
    {
        return Permission::firstOrCreate(['name' => "{$model}:{$permision}"]);
    }

    private function getDefaultPermissions(): array
    {
        return [
            'permission',
            'rol',
        ];
    }
}
