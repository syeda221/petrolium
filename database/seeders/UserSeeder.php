<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Roles ───────────────────────────────────────────────────────────
        $roles = [
            'Admin', 'Accountant', 'Manager', 'Assistant accountant', 'Cashier', 'Super Admin',
        ];
        $roleIds = [];
        foreach ($roles as $name) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $roleIds[$name] = $role->id;
        }

        // ─── Permissions matching @can() in Blade templates ─────────────────
        $permNames = [
            'Products',
            'Category',
            'Sub Category',
            'Brands',
            'Purchase',
            'Purchase Return',
            'Vendor',
            'List Warehouse',
            'Warehouse Stock',
            'Stock Transfer',
            'Sales',
            'Sale Return',
            'Bookings',
            'Customer',
            'Zone',
            'Char Of Accounts',
            'Sale Report',
            'Purchase Report',
            'Customer Ledger',
            'Vendor Ledger',
            'Item Stock Report',
            'System Reports',
        ];

        $permIds = [];
        foreach ($permNames as $i => $name) {
            $perm = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $permIds[$i] = $perm->id;
        }

        // ─── Role–Permission assignments (role IDs: 1=Admin, 2=Accountant, 3=Manager, 4=Asst accountant, 5=Cashier) ───
        // Each entry: permissionIndex => [role IDs that get it]
        $assignments = [
            'Products'           => [1, 3, 4],
            'Category'           => [1, 3, 4],
            'Sub Category'       => [1, 3, 4],
            'Brands'             => [1, 3, 4],
            'Purchase'           => [1, 2, 3, 4],
            'Purchase Return'    => [1, 3, 4],
            'Vendor'             => [1, 2, 3, 4],
            'List Warehouse'     => [1, 3, 4],
            'Warehouse Stock'    => [1, 3, 4],
            'Stock Transfer'     => [1, 3, 4],
            'Sales'              => [1, 3, 4, 5],
            'Sale Return'        => [1, 3, 4],
            'Bookings'           => [1, 2, 3, 4],
            'Customer'           => [1, 2, 3, 4, 5],
            'Zone'               => [1, 3, 4],
            'Char Of Accounts'   => [1, 2, 4],
            'Sale Report'        => [1, 2, 3, 4],
            'Purchase Report'    => [1, 2, 3, 4],
            'Customer Ledger'    => [1, 2, 4],
            'Vendor Ledger'      => [1, 2, 4],
            'Item Stock Report'  => [1, 3, 4],
            'System Reports'     => [1],
        ];

        // Role name by ID (1-based)
        $roleNameById = [1 => 'Admin', 2 => 'Accountant', 3 => 'Manager', 4 => 'Assistant accountant', 5 => 'Cashier'];

        // Sync permissions for each role
        foreach ($roleNameById as $rid => $rname) {
            $role = Role::findByName($rname, 'web');
            $pnames = [];
            foreach ($assignments as $permName => $roleIds) {
                if (in_array($rid, $roleIds)) {
                    $pnames[] = $permName;
                }
            }
            $role->syncPermissions(Permission::whereIn('name', $pnames)->get());
        }

        // Super Admin gets ALL permissions
        $superAdminRole = Role::findByName('Super Admin', 'web');
        $superAdminRole->syncPermissions(Permission::whereIn('name', $permNames)->get());

        // ─── Users (matching the dump) ──────────────────────────────────────
        $users = [
            ['name' => 'Admin',              'email' => 'admin@admin.com',           'password' => 'admin',           'role' => 'Admin'],
            ['name' => 'Talib',              'email' => 'Talib@wijdanstore.pk',      'password' => 'talib123',        'role' => 'Accountant'],
            ['name' => 'Danish',             'email' => 'danish@wijdanstore.pk',     'password' => 'danish123',       'role' => 'Assistant accountant'],
            ['name' => 'usama naskani',      'email' => 'usamamnaskani@wijdanstore.pk', 'password' => 'usama123',   'role' => 'Admin'],
            ['name' => 'test@gmail.com',     'email' => 'test@gmail.com',            'password' => 'test123',         'role' => null],
            ['name' => 'Super Admin',        'email' => 'superadmin@example.com',    'password' => 'superadmin',      'role' => 'Super Admin'],
            ['name' => 'soban',              'email' => 'soban@soban.com',           'password' => 'soban',           'role' => null],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make($u['password']),
                    'usertype' => 'admin',
                ]
            );
            if ($u['role'] && !$user->hasRole($u['role'])) {
                $user->assignRole($u['role']);
            }
        }
    }
}
