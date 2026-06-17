<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ManualCOntroller;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\ManuallController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\NarrationController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\AccountsHeadController;
use App\Http\Controllers\SalesOfficerController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\InwardgatepassController;
use App\Http\Controllers\ProductBookingController;
use App\Http\Controllers\WarehouseStockController;
use App\Http\Controllers\GroupProductController;

/*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider and all of them will
    | be assigned to the "web" middleware group. Make something great!
    |
    */

Route::get('/home', [HomeController::class, 'index'])->middleware('auth')->name('home');
 
Route::get('System/Reports', [HomeController::class, 'System_Reports'])->name('System.Reports')->middleware('permission:System Reports');
Route::get('/category-products/{id}', [HomeController::class, 'categoryProducts']);

// Route::get('/adminpage', [HomeController::class, 'adminpage'])->middleware(['auth','admin'])->name('adminpage');

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    route::get('/category', [CategoryController::class, 'index'])->name('Category.home')->middleware('permission:Category');
    Route::get('/category/delete/{id}', [CategoryController::class, 'delete'])->name('delete.category');
    route::post('/category/stote', [CategoryController::class, 'store'])->name("store.category");

    route::get('/Brand', [BrandController::class, 'index'])->name('Brand.home')->middleware('permission:Brands');
    Route::get('/Brand/delete/{id}', [BrandController::class, 'delete'])->name('delete.Brand');
    route::post('/Brand/stote', [BrandController::class, 'store'])->name("store.Brand");

    route::get('/Unit', [UnitController::class, 'index'])->name('Unit.home');
    Route::get('/Unit/delete/{id}', [UnitController::class, 'delete'])->name('delete.Unit');
    route::post('/Unit/stote', [UnitController::class, 'store'])->name("store.Unit");

    route::get('/subcategory', [SubcategoryController::class, 'index'])->name('subcategory.home')->middleware('permission:Sub Category');
    Route::get('/subcategory/delete/{id}', [SubcategoryController::class, 'delete'])->name('delete.subcategory');
    route::post('/subcategory/stote', [SubcategoryController::class, 'store'])->name("store.subcategory");

    Route::get('/Product', [ProductController::class, 'product'])->name('product')->middleware('permission:Products');
    // Route::get('/Product', [ProductController::class, 'product'])->name('product')->middleware('permission:View Product');
    Route::get('/create_prodcut', [ProductController::class, 'view_store'])->name('store');
    // Route::get('/create_prodcut', [ProductController::class, 'view_store'])->name('store')->middleware('permission:Create Product');
    Route::post('/store-product', [ProductController::class, 'store_product'])->name('store-product');
    Route::put('/product/update/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::get('/get-subcategories/{category_id}', [ProductController::class, 'getSubcategories'])->name('fetch-subcategories');
    Route::get('/generate-barcode-image', [ProductController::class, 'generateBarcode'])->name('generate-barcode-image');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::get('/barcode/{id}', [ProductController::class, 'barcode'])->name('product.barcode');

    Route::prefix('discount')->group(function () {
        Route::get('/', [DiscountController::class, 'index'])->name('discount.index')->middleware('permission:Discount Products');
        Route::get('/create', [DiscountController::class, 'create'])->name('discount.create');
        Route::post('/store', [DiscountController::class, 'store'])->name('discount.store');
        Route::post('/toggle-status/{id}', [DiscountController::class, 'toggleStatus'])->name('discount.toggleStatus');
        Route::get('/barcode/{id}', [DiscountController::class, 'barcode'])->name('discount.barcode');
    });




    // Customer Routes


    //Cutomer create 
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index')->middleware('permission:Customer');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers/store', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/edit/{id}', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::post('/customers/update/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::get('/customers/delete/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Dual Party (Customer & Vendor)
    Route::get('/dual-party/create', [CustomerController::class, 'createDual'])->name('dual.party.create');
    Route::post('/dual-party/store', [CustomerController::class, 'storeDual'])->name('dual.party.store');
    Route::get('/dual-party/ledger/{id}', [CustomerController::class, 'dualPartyLedger'])->name('dual.party.ledger');


    // New
    Route::get('/customers/inactive', [CustomerController::class, 'inactiveCustomers'])->name('customers.inactive');
    Route::get('/customers/inactive/{id}', [CustomerController::class, 'markInactive'])->name('customers.markInactive');
    Route::get('customers/toggle-status/{id}', [CustomerController::class, 'toggleStatus'])->name('customers.toggleStatus');
    Route::get('/customers/ledger', [CustomerController::class, 'customer_ledger'])->name('customers.ledger');
    Route::get('/customer/payments', [CustomerController::class, 'customer_payments'])->name('customer.payments');
    Route::post('/customer/payments', [CustomerController::class, 'store_customer_payment'])->name('customer.payments.store');
    // web.php
    Route::get('/customer/ledger/{id}', [CustomerController::class, 'getCustomerLedger']);
    Route::delete('/customer-payments/{id}', [CustomerController::class, 'destroy_payment'])->name('customer.payments.destroy');
    Route::get('customer-payments/receipt/{id}', [CustomerController::class, 'customer_payment_receipt'])->name('customer.payments.receipt');


    // Vendor Routes
    Route::get('/vendors', [VendorController::class, 'index'])->name('vendors')->middleware('permission:Vendor');
    Route::post('/vendor/store', [VendorController::class, 'store'])->name('vendor.store');
    Route::get('/vendor/delete/{id}', [VendorController::class, 'delete'])->name('vendor.delete');
    Route::get('/vendors-ledger', [VendorController::class, 'vendors_ledger'])->name('vendors-ledger');
    Route::get('/vendor/payments', [VendorController::class, 'vendor_payments'])->name('vendor.payments');
    Route::post('/vendor/payments', [VendorController::class, 'store_vendor_payment'])->name('vendor.payments.store');
    Route::get('/vendor/bilties', [VendorController::class, 'vendor_bilties'])->name('vendor.bilties');
    Route::post('/vendor/bilties', [VendorController::class, 'store_vendor_bilty'])->name('vendor.bilties.store');
    Route::get('vendor/payment-receipt/{id}',[VendorController::class, 'printReceipt'])->name('vendor.payment.receipt');


    // Warehouse Routes
    Route::get('/warehouse', [WarehouseController::class, 'index'])->middleware('permission:List Warehouse');
    Route::post('/warehouse/store', [WarehouseController::class, 'store']);
    Route::get('/warehouse/delete/{id}', [WarehouseController::class, 'delete']);

    // Branches
    Route::resource('branch', BranchController::class)->names('branch')->only(['index', 'store']);
    Route::get('/branch/delete/{id}', [BranchController::class, 'delete'])->name('branch.delete');

    // Roles
    Route::resource('roles', RoleController::class)->names('roles')->only(['index', 'store']);
    Route::get('/roles/delete/{id}', [RoleController::class, 'delete'])->name('roles.delete');
    Route::post('/admin/roles/update-permission', [RoleController::class, 'updatePermissions'])->name('roles.update.permission');


    // Permissions
    Route::resource('permissions', PermissionController::class)->names('permissions')->only(['index', 'store']);
    Route::get('/permissions/delete/{id}', [PermissionController::class, 'delete'])->name('permission.delete');

    // Users
    Route::resource('users', UserController::class)->names('users')->only(['index', 'store']);
    Route::get('/users/delete/{id}', [UserController::class, 'delete'])->name('users.delete');
    Route::post('/admin/users/update-roles', [UserController::class, 'updateRoles'])->name('users.update.roles');
    // Route::put('/users/{id}/roles', [UserController::class, 'updateRoles'])->name('users.update.roles');

    // Zone
    Route::get('zone', [ZoneController::class, 'index'])->name('zone.index')->middleware('permission:Zone');
    Route::post('zones/store', [ZoneController::class, 'store'])->name('zone.store');
    Route::get('zones/edit/{id}', [ZoneController::class, 'edit'])->name('zone.edit');
    Route::get('zones/delete/{id}', [ZoneController::class, 'destroy'])->name('zone.delete');

    //Sales Officer
    Route::get('sales-officers', [SalesOfficerController::class, 'index'])->name('sales.officer.index')->middleware('permission:Sales Officer');
    Route::post('sales-officers/store', [SalesOfficerController::class, 'store'])->name('sales-officer.store');
    Route::get('sales-officers/edit/{id}', [SalesOfficerController::class, 'edit'])->name('sales.officer.edit');
    Route::delete('sales-officers/{id}', [SalesOfficerController::class, 'destroy'])->name('sales-officer.delete');


    // products

    route::get('/Purchase', [PurchaseController::class, 'index'])->name('Purchase.home')->middleware('permission:Purchase');
    route::get('/add/Purchase', [PurchaseController::class, 'add_purchase'])->name('add_purchase');
    route::post('/Purchase/stote', [PurchaseController::class, 'store'])->name("store.Purchase");
    Route::get('/purchase/{id}/edit', [PurchaseController::class, 'edit'])->name('purchase.edit');
    Route::put('/purchase/{id}', [PurchaseController::class, 'update'])->name('purchase.update');
    Route::delete('/purchase/{id}', [PurchaseController::class, 'destroy'])->name('purchase.destroy');
    Route::get('/search-products', [ProductController::class, 'searchProducts'])->name('search-products');
    Route::get('/purchase/{id}/invoice', [PurchaseController::class, 'Invoice'])->name('purchase.invoice');


    Route::get('/purchasereturn/{id}/invoice', [PurchaseController::class, 'ReturnInvoice'])->name('purchasereturn.invoice');

    // Inward Gatepass Routes
    Route::get('/InwardGatepass', [InwardgatepassController::class, 'index'])->name('InwardGatepass.home')->middleware('permission:List Inwards');
    Route::get('/add/InwardGatepass', [InwardgatepassController::class, 'create'])->name('add_inwardgatepass')->middleware('permission:Create Inward Gatepass');
    Route::post('/InwardGatepass/store', [InwardgatepassController::class, 'store'])->name("store.InwardGatepass");
    // Route::get('/InwardGatepas/{id}', [InwardgatepassController::class, 'show'])->name('InwardGatepass.show');
    Route::get('/InwardGatepasinv/{id}', [InwardgatepassController::class, 'show_inv'])->name('InwardGatepass.inv');
    // Route::delete('/inward-gatepass/{id}', [InwardGatepassController::class, 'destroy'])->name('InwardGatepass.destroy');

    Route::get('inward-gatepass/{id}/add-details', [InwardGatepassController::class, 'addDetails'])
    ->name('InwardGatepass.addDetails');

    Route::get('/search-product-by-barcode', [InwardGatepassController::class, 'searchByBarcode'])
    ->name('search-product-by-barcode');

    Route::post('inward-gatepass/{id}/store-details', [InwardGatepassController::class, 'storeDetails'])
    ->name('InwardGatepass.storeDetails');

    // edit/update/delete abhi comment kiye hue hain
    Route::get('/InwardGatepass/{id}/edit', [InwardgatepassController::class, 'edit'])->name('InwardGatepass.edit');
    Route::put('/InwardGatepass/{id}', [InwardgatepassController::class, 'update'])->name('InwardGatepass.update');
    Route::get('/inward-gatepass/{id}/pdf', [InwardgatepassController::class, 'pdf'])->name('InwardGatepass.pdf');


    Route::delete('/InwardGatepass/{id}', [InwardgatepassController::class, 'destroy'])->name('InwardGatepass.destroy');
    // Products search
    Route::get('/search-products', [InwardgatepassController::class, 'searchProducts'])->name('search-products');


    // Show Add Bill Form
    Route::get('inward-gatepass/{id}/add-bill', [PurchaseController::class, 'addBill'])->name('add_bill');
    // Store Bill
    Route::post('inward-gatepass/{id}/store-bill', [PurchaseController::class, 'store_inwardbill'])->name('store.bill');
    // Purchase Return Routes
    Route::get('purchase/return', [PurchaseController::class, 'purchaseReturnIndex'])->name('purchase.return.index')->middleware('permission:Purchase Return');

    Route::get('purchase/return/{id}', [PurchaseController::class, 'showReturnForm'])->name('purchase.return.show');
    Route::post('purchase/return/store', [PurchaseController::class, 'storeReturn'])->name('purchase.return.store');

    // Route::get('/fetch-product', [PurchaseController::class, 'fetchProduct'])->name('item.search');
    // Route::post('/fetch-item-details', [PurchaseController::class, 'fetchItemDetails']);
    // Route::get('/Purchase/create', function () {
    //     return view('admin_panel.purchase.add_purchase');
    // });
    // Route::get('/get-items-by-category/{categoryId}', [PurchaseController::class, 'getItemsByCategory'])->name('get-items-by-category');
    // Route::get('/get-product-details/{productName}', [ProductController::class, 'getProductDetails'])->name('get-product-details');

    // Route::get('booking/system', [SaleController::class,'booking-system'])->name('booking.index');
    Route::get('sale', [SaleController::class, 'index'])->name('sale.index')->middleware('permission:Sales');
    Route::get('sale/create', [SaleController::class, 'addsale'])->name('sale.add');
    // Route::get('/products/search', [SaleController::class, 'searchProducts'])->name('products.search');
    Route::get('/search-product-name', [SaleController::class, 'searchpname'])->name('search-product-name');
    Route::get('/sale/customer-balance/{id}', [SaleController::class, 'getCustomerBalance'])->name('sale.customer.balance');
    Route::post('/sales/store', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{id}/return', [SaleController::class, 'saleretun'])->name('sales.return.create');
    Route::post('/sales-return/store', [SaleController::class, 'storeSaleReturn'])->name('sales.return.store');
    Route::get('/sale-returns', [App\Http\Controllers\SaleController::class, 'salereturnview'])->name('sale.returns.index')->middleware('permission:Sale Return');
    Route::get('/sales/{id}/invoice', [SaleController::class, 'saleinvoice'])->name('sales.invoice');
    Route::get('/sales/invoice-a4/{id}', [SaleController::class, 'saleinvoiceA4'])->name('sales.invoice_a4');
    Route::get('/sales/{id}/edit', [SaleController::class, 'saleedit'])->name('sales.edit');
    Route::put('/sales/{id}', [SaleController::class, 'updatesale'])->name('sales.update');
    Route::get('/sales/{id}/dc', [SaleController::class, 'saledc'])->name('sales.dc');
    Route::get('/sales/{id}/recepit', [SaleController::class, 'salerecepit'])->name('sales.recepit');
    Route::get('/sale-return/invoice/{id}', [SaleController::class, 'retrninvoice'])->name('saleReturn.invoice');

    // booking system

    Route::get('bookings', [ProductBookingController::class, 'index'])->name('bookings.index')->middleware('permission:Bookings');
    Route::get('bookings/create', [ProductBookingController::class, 'create'])->name('bookings.create');
    Route::post('bookings/store', [ProductBookingController::class, 'store'])->name('bookings.store');
    Route::get('booking/receipt/{id}', [ProductBookingController::class, 'receipt'])->name('booking.receipt');
    Route::get('/sales/from-booking/{id}', [SaleController::class, 'convertFromBooking'])->name('sales.from.booking');
    Route::delete('bookings/{id}', [ProductBookingController::class, 'destroy'])->name('bookings.destroy');
    
    // web.php
    Route::get('/warehouse-stock-quantity', [StockTransferController::class, 'getStockQuantity'])->name('warehouse.stock.quantity');
    Route::get('/warehouse-stock-receipt/{id?}', [StockTransferController::class, 'receipt'])->name('recipt.warehouse');

    // narratiions
    Route::get('/get-customers-by-type', [CustomerController::class, 'getByType']);
    Route::resource('warehouse_stocks', WarehouseStockController::class)->middleware('permission:Warehouse Stock');
    Route::resource('stock_transfers', StockTransferController::class)->middleware('permission:Stock Transfer');

    Route::resource('narrations', NarrationController::class)->only(['index', 'store', 'destroy']);
    Route::get('vouchers/{type}', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::post('vouchers/store', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::get('/get-vendor-balance/{id}', [VendorController::class, 'getVendorBalance']);

    // reporting routes 
    Route::get('/reports', [ReportingController::class, 'reports_hub'])->name('reports.index');
    Route::get('/report/expense', [ReportingController::class, 'expense_report'])->name('report.expense');
    Route::get('/report/item-stock', [ReportingController::class, 'item_stock_report'])->name('report.item_stock')->middleware('permission:Item Stock Report');
    Route::post('/report/item-stock-fetch', [ReportingController::class, 'fetchItemStock'])->name('report.item_stock.fetch');

    Route::get('report/purchase', [ReportingController::class, 'purchase_report'])->name('report.purchase')->middleware('permission:Purchase Report');
    Route::post('report/purchase/fetch', [ReportingController::class, 'fetchPurchaseReport'])->name('report.purchase.fetch');

    Route::get('report/sale', [ReportingController::class, 'sale_report'])->name('report.sale')->middleware('permission:Sale Report');
    Route::get('report/sale/fetch', [ReportingController::class, 'fetchsaleReport'])->name('report.sale.fetch');

    Route::get('report/sale/category', [ReportingController::class, 'sale_report_category'])->name('report.sale.category')->middleware('permission:Sale Report');
    Route::get('report/sale/category/fetch', [ReportingController::class, 'fetchsalecategoryReport'])->name('report.sale.category.fetch');
    Route::get('report/profit-loss', [ReportingController::class, 'profit_loss_report'])->name('report.profit_loss')->middleware('permission:Sale Report');
    Route::get('report/customer-wise-profit', [ReportingController::class, 'customer_wise_profit'])->name('report.customer_wise_profit')->middleware('permission:Sale Report');
    Route::get('report/party-wise-sale', [ReportingController::class, 'party_wise_sale'])->name('report.party_wise_sale');


    Route::get('report/customer/ledger', [ReportingController::class, 'customer_ledger_report'])->name('report.customer.ledger')->middleware('permission:Customer Ledger');
    Route::get('report/customer-ledger/fetch', [ReportingController::class, 'fetch_customer_ledger'])->name('report.customer.ledger.fetch');

    Route::get('report/dual-party/ledger', [ReportingController::class, 'dual_party_ledger_report'])->name('report.dual_party.ledger');
    Route::get('report/dual-party-ledger/fetch', [ReportingController::class, 'fetch_dual_party_ledger'])->name('report.dual_party.ledger.fetch');

    Route::get('report/vendor/ledger', [ReportingController::class, 'vendor_ledger_report'])->name('report.vendor.ledger')->middleware('permission:Vendor Ledger');
    Route::get('report/vendor-ledger/fetch', [ReportingController::class, 'fetch_vendor_ledger'])->name('report.vendor.ledger.fetch');

    Route::get('report/party-balances', [ReportingController::class, 'party_balance_report'])->name('report.party_balances');
    Route::get('report/party-balances/fetch', [ReportingController::class, 'fetch_party_balances'])->name('report.party_balances.fetch');

    Route::get('report/financial-summary', [ReportingController::class, 'financialSummary'])->name('report.financial_summary');
    Route::get('report/financial-summary/data', [ReportingController::class, 'fetchFinancialSummary'])->name('report.financial_summary.data');


    // Vochers work

    Route::get('/view_all', [AccountsHeadController::class, 'index'])->name('view_all')->middleware('permission:Char Of Accounts');

    Route::resource('narrations', NarrationController::class)->only(['index', 'store', 'destroy'])->middleware('permission:Narrations');
    Route::get('/getPartyList', [NarrationController::class, 'getPartyList'])->name('party.list');
    Route::get('/get-customer/{id}', [NarrationController::class, 'getCustomerData'])->name('customers.show');
    Route::get('/get-accounts-by-head/{headId}', [NarrationController::class, 'getAccountsByHead']);

    Route::get('/create-voucher', [VoucherController::class, 'createVoucher'])->name('create.voucher');

    Route::get('/all-recepit-vochers', [VoucherController::class, 'all_recepit_vochers'])->name('all-recepit-vochers')->middleware('permission:Receipts Voucher');
    Route::get('/recepit-vochers', [VoucherController::class, 'recepit_vochers'])->name('recepit-vochers');
    Route::post('/recepit/vochers/stote', [VoucherController::class, 'store_rec_vochers'])->name('recepit.vochers.store');
    Route::get('/receipt-voucher/print/{id}', [VoucherController::class, 'print'])->name('receiptVoucher.print');


    Route::get('/Payment-vochers', [VoucherController::class, 'Payment_vochers'])->name('Payment-vochers');
    route::post('/Payment/vochers/stote', [VoucherController::class, 'store_Pay_vochers'])->name('Payment.vochers.store');
    Route::get('/all-Payment-vochers', [VoucherController::class, 'all_Payment_vochers'])->name('all-Payment-vochers')->middleware('permission:Payment Voucher');
    Route::get('/Payment-voucher/print/{id}', [VoucherController::class, 'Paymentprint'])->name('PaymentVoucher.print');

    // Journal Vouchers (Day Book)
    Route::get('/journal-vouchers', [VoucherController::class, 'journalVouchers'])->name('journal-vouchers');
    Route::post('/close-day', [VoucherController::class, 'closeDay'])->name('close-day');

    Route::get('/expense-vochers', [VoucherController::class, 'expense_vochers'])->name('expense-vochers');
    Route::post('/expense-category/store', [VoucherController::class, 'store_expense_category'])->name('expense.category.store');
    Route::post('/expense-vochers/store', [VoucherController::class, 'store_expense_vochers'])->name('expense.vochers.store');
    Route::get('/all-expense-vochers', [VoucherController::class, 'all_expense_vochers'])->name('all-expense-vochers')->middleware('permission:Expense Voucher');
    Route::get('/expense-voucher/print/{id}', [VoucherController::class, 'expenseprint'])->name('expenseVoucher.print');
    Route::get('/expense-voucher/edit/{id}', [VoucherController::class, 'edit_expense_vocher'])->name('expense-vocher.edit');
    Route::post('/expense-voucher/update/{id}', [VoucherController::class, 'update_expense_vocher'])->name('expense-vocher.update');
    Route::get('/expense-voucher/delete/{id}', [VoucherController::class, 'destroy_expense_vocher'])->name('expense-vocher.delete');

    // Transfer Vouchers
    Route::get('/transfer-vouchers', [\App\Http\Controllers\TransferVoucherController::class, 'index'])->name('transfer-vouchers');
    Route::post('/transfer-vouchers/store', [\App\Http\Controllers\TransferVoucherController::class, 'store'])->name('transfer-vouchers.store');
    Route::get('/transfer-vouchers/all', [\App\Http\Controllers\TransferVoucherController::class, 'all_transfer_vouchers'])->name('transfer-vouchers.all');
    Route::get('/transfer-vouchers/edit/{id}', [\App\Http\Controllers\TransferVoucherController::class, 'edit'])->name('transfer-vouchers.edit');
    Route::post('/transfer-vouchers/update/{id}', [\App\Http\Controllers\TransferVoucherController::class, 'update'])->name('transfer-vouchers.update');

    Route::get('/account-transfers', [\App\Http\Controllers\AccountTransferController::class, 'index'])->name('account-transfers');
    Route::post('/account-transfers/store', [\App\Http\Controllers\AccountTransferController::class, 'store'])->name('account-transfers.store');
    Route::get('/account-transfers/all', [\App\Http\Controllers\AccountTransferController::class, 'all_account_transfers'])->name('account-transfers.all');

    // Simple Finance (Payment In & Out, Other Income)
    Route::get('/finance/payment-in', [\App\Http\Controllers\SimpleFinanceController::class, 'paymentIn'])->name('payment.in');
    Route::post('/finance/payment-in', [\App\Http\Controllers\SimpleFinanceController::class, 'storePaymentIn'])->name('payment.in.store');
    Route::put('/finance/payment-in/{id}/{type}', [\App\Http\Controllers\SimpleFinanceController::class, 'updatePaymentIn'])->name('payment.in.update');
    Route::delete('/finance/payment-in/{id}/{type}', [\App\Http\Controllers\SimpleFinanceController::class, 'destroyPaymentIn'])->name('payment.in.delete');

    Route::get('/finance/payment-out', [\App\Http\Controllers\SimpleFinanceController::class, 'paymentOut'])->name('payment.out');
    Route::post('/finance/payment-out', [\App\Http\Controllers\SimpleFinanceController::class, 'storePaymentOut'])->name('payment.out.store');
    Route::put('/finance/payment-out/{id}/{type}', [\App\Http\Controllers\SimpleFinanceController::class, 'updatePaymentOut'])->name('payment.out.update');
    Route::delete('/finance/payment-out/{id}/{type}', [\App\Http\Controllers\SimpleFinanceController::class, 'destroyPaymentOut'])->name('payment.out.delete');

    Route::get('/finance/other-income', [\App\Http\Controllers\SimpleFinanceController::class, 'otherIncome'])->name('other.income');
    Route::post('/finance/other-income', [\App\Http\Controllers\SimpleFinanceController::class, 'storeOtherIncome'])->name('other.income.store');
    Route::put('/finance/other-income/{id}', [\App\Http\Controllers\SimpleFinanceController::class, 'updateOtherIncome'])->name('other.income.update');
    Route::delete('/finance/other-income/{id}', [\App\Http\Controllers\SimpleFinanceController::class, 'destroyOtherIncome'])->name('other.income.delete');

    Route::get('cashbook', [ReportingController::class, 'cashbook'])->name('cashbook');
    Route::get('report/cash-book', [ReportingController::class, 'simple_cash_book'])->name('report.cash_book');
    Route::post('report/cash-book/close', [ReportingController::class, 'close_day_cash'])->name('report.cash_book.close');

    Route::prefix('coa')->group(function () {
        Route::get('/', [AccountsHeadController::class, 'index'])->name('coa.index');
        Route::post('/head', [AccountsHeadController::class, 'storeHead'])->name('coa.head.store');
        Route::post('/head/update/{id}', [AccountsHeadController::class, 'updateHead'])->name('coa.head.update');
        Route::get('/head/delete/{id}', [AccountsHeadController::class, 'destroyHead'])->name('coa.head.destroy');
        Route::post('/account', [AccountsHeadController::class, 'storeAccount'])->name('coa.account.store');
        Route::post('/account/update/{id}', [AccountsHeadController::class, 'updateAccount'])->name('coa.account.update');
        Route::get('/account/delete/{id}', [AccountsHeadController::class, 'destroyAccount'])->name('coa.account.destroy');
        Route::get('/account/ledger/{id}', [AccountsHeadController::class, 'accountLedger'])->name('coa.account.ledger');
    });

    // Group Products Routes
    Route::get('group-products', [GroupProductController::class, 'index'])->name('group-products.index');
    Route::get('group-products/create', [GroupProductController::class, 'create'])->name('group-products.create');
    Route::post('group-products/store', [GroupProductController::class, 'store'])->name('group-products.store');
    Route::get('group-products/{id}', [GroupProductController::class, 'show'])->name('group-products.show');
    Route::put('group-products/{id}/toggle', [GroupProductController::class, 'toggleStatus'])->name('group-products.toggle-status');
    Route::delete('group-products/{id}', [GroupProductController::class, 'destroy'])->name('group-products.destroy');
    Route::get('product-stock/{id}', [GroupProductController::class, 'getProductStock'])->name('product.stock');

    route::post('/subcategory/manual', [ManuallController::class, 'subcategory'])->name("manual.subcategory");
    route::post('/Brand/manual', [ManuallController::class, 'brand'])->name("manual.Brand");
    route::post('/category/manual', [ManuallController::class, 'category'])->name("manual.category");
    route::post('/Unit/manual', [ManuallController::class, 'unit'])->name("manual.Unit");
    route::post('/Brand/manual', [ManuallController::class, 'brand'])->name("manual.Brand");
});
require __DIR__ . '/auth.php';
