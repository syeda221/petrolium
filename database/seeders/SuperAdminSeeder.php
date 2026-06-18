<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            'View Dashboard',

            // Products
            'Create Product', 'Edit Product', 'Delete Product', 'View Product', 'Print Barcode',

            // Categories
            'Create Category', 'Edit Category', 'Delete Category', 'View Category',

            // Subcategories
            'Create Subcategory', 'Edit Subcategory', 'Delete Subcategory', 'View Subcategory',

            // Brands
            'Create Brand', 'Edit Brand', 'Delete Brand', 'View Brand',

            // Units
            'Create Unit', 'Edit Unit', 'Delete Unit', 'View Unit',

            // Customers
            'Create Customer', 'Edit Customer', 'Delete Customer', 'View Customer',
            'Manage Customer Payments', 'View Customer Ledger', 'Manage Dual Party',

            // Vendors
            'Create Vendor', 'Edit Vendor', 'Delete Vendor', 'View Vendor',
            'Manage Vendor Payments', 'View Vendor Ledger', 'Manage Vendor Bilties',

            // Purchases
            'Create Purchase', 'Edit Purchase', 'Delete Purchase', 'View Purchase',
            'Print Purchase Invoice', 'Manage Purchase Returns',

            // Inward Gatepass
            'Create Inward Gatepass', 'Edit Inward Gatepass', 'Delete Inward Gatepass',
            'View Inward Gatepass', 'Print Inward Gatepass', 'Manage Inward Bill',

            // Sales
            'Create Sale', 'Edit Sale', 'Delete Sale', 'View Sale',
            'Print Sale Invoice', 'Print Sale DC', 'Print Sale Receipt',
            'Manage Sale Returns',

            // Product Bookings
            'Create Booking', 'Edit Booking', 'Delete Booking', 'View Booking',
            'Print Booking Receipt',

            // Sales Officers
            'Create Sales Officer', 'Edit Sales Officer', 'Delete Sales Officer', 'View Sales Officer',

            // Zones
            'Create Zone', 'Edit Zone', 'Delete Zone', 'View Zone',

            // Warehouses
            'Create Warehouse', 'Edit Warehouse', 'Delete Warehouse', 'View Warehouse',

            // Warehouse Stocks
            'Create Warehouse Stock', 'Edit Warehouse Stock', 'Delete Warehouse Stock',
            'View Warehouse Stock',

            // Stock Transfers
            'Create Stock Transfer', 'Edit Stock Transfer', 'Delete Stock Transfer',
            'View Stock Transfer', 'Print Stock Transfer Receipt',

            // Discounts
            'Create Discount', 'Edit Discount', 'Delete Discount', 'View Discount',
            'Print Discount Barcode',

            // Narrations
            'Create Narration', 'Edit Narration', 'Delete Narration', 'View Narration',

            // Group Products
            'Create Group Product', 'Edit Group Product', 'Delete Group Product',
            'View Group Product',

            // Branches
            'Create Branch', 'Edit Branch', 'Delete Branch', 'View Branch',

            // Transports
            'Create Transport', 'Edit Transport', 'Delete Transport', 'View Transport',

            // Accounts (COA)
            'Create Account Head', 'Edit Account Head', 'Delete Account Head',
            'Create Account', 'Edit Account', 'Delete Account', 'View Account',
            'View Account Ledger',

            // Expense Vouchers
            'Create Expense Voucher', 'Edit Expense Voucher', 'Delete Expense Voucher',
            'View Expense Voucher', 'Print Expense Voucher',

            // Receipt Vouchers
            'Create Receipt Voucher', 'Edit Receipt Voucher', 'Delete Receipt Voucher',
            'View Receipt Voucher', 'Print Receipt Voucher',

            // Payment Vouchers
            'Create Payment Voucher', 'Edit Payment Voucher', 'Delete Payment Voucher',
            'View Payment Voucher', 'Print Payment Voucher',

            // Journal Vouchers
            'Create Journal Voucher', 'Edit Journal Voucher', 'Delete Journal Voucher',
            'View Journal Voucher',

            // Transfer Vouchers
            'Create Transfer Voucher', 'Edit Transfer Voucher', 'Delete Transfer Voucher',
            'View Transfer Voucher', 'Print Transfer Voucher',

            // Account Transfers
            'Create Account Transfer', 'Edit Account Transfer', 'Delete Account Transfer',
            'View Account Transfer', 'Print Account Transfer',

            // Other Income
            'Create Other Income', 'Edit Other Income', 'Delete Other Income',
            'View Other Income', 'Print Other Income',

            // Payment In / Payment Out
            'Create Payment In', 'Edit Payment In', 'Delete Payment In',
            'View Payment In', 'Print Payment In',
            'Create Payment Out', 'Edit Payment Out', 'Delete Payment Out',
            'View Payment Out', 'Print Payment Out',

            // Voucher History
            'View Voucher History',

            // Day Closing
            'Close Day', 'View Day Closing',

            // Cash Book
            'View Cash Book', 'Close Cash Book',

            // Reports
            'View Sale Report', 'View Purchase Report', 'View Expense Report',
            'View Customer Ledger Report', 'View Vendor Ledger Report',
            'View Dual Party Ledger Report', 'View Party Balance Report',
            'View Party Wise Sale Report', 'View Customer Wise Profit Report',
            'View Profit Loss Report', 'View Financial Summary Report',
            'View Item Stock Report', 'View Reports Hub',

            // Users & Roles
            'Create User', 'Edit User', 'Delete User', 'View User',
            'Create Role', 'Edit Role', 'Delete Role', 'View Role',
            'Create Permission', 'Delete Permission', 'View Permission',
            'Manage User Roles', 'Manage Role Permissions',

            // Settings
            'Access Settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin'),
            ]
        );

        if (!$adminUser->hasRole('super-admin')) {
            $adminUser->assignRole('super-admin');
        }

        $this->command->info('Super Admin user created with all permissions.');
    }
}
