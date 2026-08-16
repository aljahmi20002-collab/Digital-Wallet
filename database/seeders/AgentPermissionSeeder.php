<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Adds the Agent permissions without truncating the permissions
 * table. Safe to run on a production database with existing role and
 * permission assignments — uses firstOrCreate for permissions and only
 * grants the new ones to the super-admin role.
 */
class AgentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions so spatie sees the new rows
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $newPermissions = [
            ['category' => 'agent', 'name' => 'agent-list'],
            ['category' => 'agent', 'name' => 'agent-manage'],
            ['category' => 'agent', 'name' => 'agent-commission-rules-manage'],
            ['category' => 'agent', 'name' => 'agent-request-notification'],
        ];

        $created = collect();

        foreach ($newPermissions as $perm) {
            $permission = Permission::firstOrCreate(
                [
                    'name'       => $perm['name'],
                    'guard_name' => 'admin',
                ],
                [
                    'category' => $perm['category'],
                ]
            );

            // Backfill category if the row pre-existed without it
            if ($permission->wasRecentlyCreated === false && empty($permission->category)) {
                $permission->forceFill(['category' => $perm['category']])->save();
            }

            $created->push($permission);
        }

        // Grant the new permissions to the super-admin role if it exists
        $superRole = Role::query()
            ->where('guard_name', 'admin')
            ->where('name', 'super-admin')
            ->first();

        if ($superRole instanceof Role) {
            $superRole->givePermissionTo($created->all());
        }

        // Final cache flush so the admin UI sees them immediately
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
