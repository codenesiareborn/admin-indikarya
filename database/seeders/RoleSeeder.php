<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles if they don't exist
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $staff = Role::firstOrCreate(['name' => 'staff']);

        // Get all permissions
        $allPermissions = Permission::all();

        // Super Admin gets all permissions (already set by shield:super-admin)
        $superAdmin->syncPermissions($allPermissions);

        // Admin permissions (full access except shield resources)
        $adminPermissions = Permission::where('name', 'not like', '%shield%')
            ->where('name', 'not like', '%role%')
            ->get();
        $admin->syncPermissions($adminPermissions);

        // Manager permissions (view, create, update - no delete)
        $managerPermissions = Permission::whereIn('name', [
            // Projects
            'view_project',
            'view_any_project',
            'create_project',
            'update_project',
            
            // Employees
            'view_employee',
            'view_any_employee',
            
            // Attendance
            'view_attendance',
            'view_any_attendance',
            'create_attendance',
            'update_attendance',
            
            // Task Lists
            'view_task::list',
            'view_any_task::list',
            'create_task::list',
            'update_task::list',
            
            // Task Submissions
            'view_task::submission',
            'view_any_task::submission',
            'create_task::submission',
            'update_task::submission',
        ])->get();
        $manager->syncPermissions($managerPermissions);

        // Staff permissions (view only + create own attendance)
        $staffPermissions = Permission::whereIn('name', [
            // Projects - view only
            'view_project',
            'view_any_project',
            
            // Attendance - view and create own
            'view_attendance',
            'view_any_attendance',
            'create_attendance',
            
            // Task Lists - view only
            'view_task::list',
            'view_any_task::list',
            
            // Task Submissions - view and create own
            'view_task::submission',
            'view_any_task::submission',
            'create_task::submission',
        ])->get();
        $staff->syncPermissions($staffPermissions);

        $this->command->info('✅ Roles created successfully!');
        $this->command->info('   - Super Admin: Full access');
        $this->command->info('   - Admin: Full access (except shield)');
        $this->command->info('   - Manager: View, Create, Update');
        $this->command->info('   - Staff: View only + Create own records');
    }
}
