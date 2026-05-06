<div class="container-scroller">
    <!-- Top Configuration -->
    <style>
        .top_nav {
            background-color: #37a371 !important; /* Theme Color */
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        .rt_nav_header .top_nav {
            height: 70px; /* Taller fresher header */
        }
        .rt_nav_header .nav-bottom {
            background-color: #fff;
            border-bottom: 1px solid #e5e5e5;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .nav-link .menu-title {
            font-weight: 500;
        }
        .submenu-item i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
            color: #37a371;
        }
        .profile_name {
            color: #fff !important;
            font-weight: 600;
        }
        .ft-chevron-down {
            color: #fff !important;
        }
        .rt_logo img {
            height: 40px; /* Better logo size */
            filter: brightness(0) invert(1); /* Make logo white if transparent */
        }
        /* Hover Effects */
        .page-navigation > .nav-item:hover > .nav-link .menu-title {
            color: #37a371 !important;
        }
        .page-navigation > .nav-item:hover > .nav-link i {
            color: #37a371 !important;
        }
        /* Mobile Toggle */
        .navbar-toggler span {
            color: #fff !important;
        }
    </style>

    <nav class="rt_nav_header horizontal-layout col-lg-12 col-12 p-0">
        <!-- Top Bar -->
        <div class="top_nav flex-grow-1">
            <div class="container d-flex flex-row h-100 align-items-center">
                <!-- Logo -->
                <div class="text-center rt_nav_wrapper d-flex align-items-center">
                    <a class="nav_logo rt_logo" href="{{ url('/home') }}">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="logo" />
                    </a>
                </div>
                
                <!-- Right Side Profile -->
                <div class="nav_wrapper_main d-flex align-items-center justify-content-between flex-grow-1">
                    <ul class="navbar-nav navbar-nav-right mr-0 ml-auto">
                        <li class="nav-item nav-profile dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                                <span class="profile_name">{{ Auth::user()->name }} <i class="feather ft-chevron-down"></i></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right navbar-dropdown pt-2" aria-labelledby="profileDropdown">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="ti-power-off text-dark mr-3"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </li>
                    </ul>

                    <button class="navbar-toggler align-self-center" type="button" data-toggle="minimize">
                        <span class="feather ft-menu text-white"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Bottom Navigation Bar -->
        <div class="nav-bottom">
            <div class="container">
                <ul class="nav page-navigation">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="{{ url('/home') }}" class="nav-link">
                            <i class="menu_icon feather ft-home"></i>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </li>

                    <!-- Sale -->
                    <li class="nav-item">
                        <a href="{{ url('/sale/create') }}" class="nav-link">
                            <i class="menu_icon fas fa-cash-register"></i>
                            <span class="menu-title">Sale</span>
                        </a>
                    </li>

                    <!-- Management (Mega Menu) -->
                    <li class="nav-item mega-menu">
                        <a href="#" class="nav-link">
                            <i class="menu_icon fas fa-tasks"></i>
                            <span class="menu-title">Management</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="submenu">
                            <div class="col-group-wrapper row">
                                <!-- Products & Categories -->
                                <div class="col-group col-md-3">
                                    <p class="category-heading">Inventory Setup</p>
                                    <ul class="submenu-item">
                                        @can('Products')
                                        <li><a href="{{ route('product') }}"><i class="fas fa-box"></i> Products</a></li>
                                        <!-- <li><a href="{{ route('group-products.index') }}"><i class="fas fa-cubes"></i> Group Products</a></li> -->
                                        <!-- @endcan
                                        @can('Discount Products')
                                        <li><a href="{{ route('discount.index') }}"><i class="fas fa-tags"></i> Discount Products</a></li>
                                        @endcan -->
                                        @can('Category')
                                        <li><a href="{{ route('Category.home') }}"><i class="fas fa-list"></i> Category</a></li>
                                        @endcan
                                        @can('Sub Category')
                                        <li><a href="{{ route('subcategory.home') }}"><i class="fas fa-th-list"></i> Sub Category</a></li>
                                        @endcan
                                        @can('Brands')
                                        <li><a href="{{ route('Brand.home') }}"><i class="fas fa-trademark"></i> Brands</a></li>
                                        @endcan
                                    </ul>
                                </div>

                                <!-- Purchase & Inventory -->
                                <div class="col-group col-md-3">
                                    <p class="category-heading">Purchase & Vendors</p>
                                    <ul class="submenu-item">
                                        <!-- @can('List Inwards')
                                        <li><a href="{{ route('InwardGatepass.home') }}"><i class="fas fa-arrow-circle-down"></i> List Inwards</a></li>
                                        @endcan 
                                        @can('Create Inward Gatepass')
                                        <li><a href="{{ route('add_inwardgatepass') }}"><i class="fas fa-plus-circle"></i> Create Inward</a></li>
                                        @endcan -->
                                        @can('Purchase')
                                        <li><a href="{{ route('Purchase.home') }}"><i class="fas fa-shopping-cart"></i> Purchase</a></li>
                                        @endcan
                                        @can('Purchase Return')
                                        <li><a href="{{ route('purchase.return.index') }}"><i class="fas fa-undo"></i> Purchase Return</a></li>
                                        @endcan
                                        @can('Vendor')
                                        <li><a href="{{ route('vendors') }}"><i class="fas fa-truck"></i> Vendors</a></li>
                                        @endcan
                                    </ul>
                                </div>

                                <!-- Warehouse & Stock -->
                                <div class="col-group col-md-3">
                                    <p class="category-heading">Warehouse & Stock</p>
                                    <ul class="submenu-item">
                                        @can('List Warehouse')
                                        <li><a href="{{ url('warehouse') }}"><i class="fas fa-warehouse"></i> Warehouses</a></li>
                                        @endcan
                                        @can('Warehouse Stock')
                                        <li><a href="{{ url('warehouse_stocks') }}"><i class="fas fa-boxes"></i> Stock Status</a></li>
                                        @endcan
                                        @can('Stock Transfer')
                                        <li><a href="{{ url('stock_transfers') }}"><i class="fas fa-exchange-alt"></i> Stock Transfer</a></li>
                                        @endcan
                                    </ul>
                                </div>

                                <!-- Sales & Customers -->
                                <div class="col-group col-md-3">
                                    <p class="category-heading">Sales Operations</p>
                                    <ul class="submenu-item">
                                        @can('Sales')
                                        <li><a href="{{ url('sale') }}"><i class="fas fa-receipt"></i> Sales History</a></li>
                                        @endcan
                                        @can('Sale Return')
                                        <li><a href="{{ url('sale-returns') }}"><i class="fas fa-undo"></i> Returns</a></li>
                                        @endcan
                                        @can('Bookings')
                                        <li><a href="{{ route('bookings.index') }}"><i class="fas fa-calendar-check"></i> Bookings</a></li>
                                        @endcan
                                        @can('Customer')
                                        <li><a href="{{ url('customers') }}"><i class="fas fa-users"></i> Customers</a></li>
                                        @endcan
                                        <li><a href="{{ route('dual.party.create') }}"><i class="fas fa-people-arrows"></i> Customer & Vendor</a></li>
                                        <!-- @can('Sales Officer')
                                        <li><a href="{{ url('sales-officers') }}"><i class="fas fa-user-tie"></i> Sales Officers</a></li>
                                        @endcan
                                         -->
                                        @can('Zone')
                                        <li><a href="{{ url('zone') }}"><i class="fas fa-map-marker-alt"></i> Zones</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- Vouchers -->
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="menu_icon fas fa-file-invoice-dollar"></i>
                            <span class="menu-title">Finance</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="submenu">
                            <ul class="submenu-item">
                                @can('Char Of Accounts')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('view_all') }}">
                                        <i class="fas fa-book mr-2"></i> Chart of Accounts
                                    </a>
                                </li>
                                @endcan
                                @can('Narrations')
                                <!-- <li class="nav-item">
                                    <a class="nav-link" href="{{ route('narrations.index') }}">
                                        <i class="fas fa-pen-nib mr-2"></i> Narrations
                                    </a>
                                </li> 
                                @endcan
                                @can('Receipts Voucher')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('all-recepit-vochers') }}">
                                        <i class="fas fa-money-bill mr-2"></i> Receipt Vouchers
                                    </a>
                                </li>
                                @endcan
                                @can('Payment Voucher')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('all-Payment-vochers') }}">
                                        <i class="fas fa-hand-holding-usd mr-2"></i> Payment Vouchers
                                    </a>
                                </li> -->
                                @endcan
                                @can('Expense Voucher')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('all-expense-vochers') }}">
                                        <i class="fas fa-receipt mr-2"></i> Expense Vouchers
                                    </a>
                                </li>
                                @endcan
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('payment.in') }}">
                                        <i class="fas fa-hand-holding-usd mr-2"></i> Payment In (Customer)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('payment.out') }}">
                                        <i class="fas fa-money-bill-wave mr-2"></i> Payment Out (Vendor)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('other.income') }}">
                                        <i class="fas fa-coins mr-2"></i> Other Income
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('transfer-vouchers') }}">
                                        <i class="fas fa-exchange-alt mr-2"></i> Party To Party
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('account-transfers') }}">
                                        <i class="fas fa-random mr-2"></i> Account Transfers Payment
                                    </a>
                                </li>
                                <!-- <li class="nav-item">
                                    <a class="nav-link" href="{{ route('journal-vouchers') }}">
                                        <i class="fas fa-journal-whills mr-2"></i> Journal Vouchers (Day Book)
                                    </a>
                                </li> -->
                            </ul>
                        </div>
                    </li>

                    <!-- Reports (Mega Menu) -->
                    <li class="nav-item mega-menu">
                        <a href="{{ route('reports.index') }}" class="nav-link">
                            <i class="menu_icon feather ft-pie-chart"></i>
                            <span class="menu-title">Reports</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="submenu">
                            <div class="col-group-wrapper row">
                                <!-- Col 1: Sales & Purchase -->
                                <div class="col-group col-md-3">
                                    <p class="category-heading">Sales & Purchase</p>
                                    <ul class="submenu-item">
                                        @can('Sale Report')
                                        <li><a href="{{ route('report.sale') }}"><i class="fas fa-chart-line"></i> Daily Sale</a></li>
                                        <li><a href="{{ route('report.sale.category') }}"><i class="fas fa-list-alt"></i> Category Sale</a></li>
                                        <li><a href="{{ route('report.party_wise_sale') }}"><i class="fas fa-user-tag"></i> Party Sale</a></li>
                                        @endcan
                                        @can('Purchase Report')
                                        <li><a href="{{ route('report.purchase') }}"><i class="fas fa-truck-loading"></i> Purchase Report</a></li>
                                        @endcan
                                    </ul>
                                </div>

                                <!-- Col 2: Cash & Finance -->
                                <div class="col-group col-md-3">
                                    <p class="category-heading">Cash & Finance</p>
                                    <ul class="submenu-item">
                                        @can('Sale Report')
                                        <li><a href="{{ route('report.cash_book') }}"><i class="fas fa-book-open"></i> Daily Cash Book</a></li>
                                        @endcan
                                        @can('Customer Ledger')
                                        <li><a href="{{ route('report.customer.ledger') }}"><i class="fas fa-user-invoice"></i> Customer Ledger</a></li>
                                        <li><a href="{{ route('report.vendor.ledger') }}"><i class="fas fa-file-invoice-dollar"></i> Vendor Ledger</a></li>
                                        <li><a href="{{ route('report.dual_party.ledger') }}"><i class="fas fa-sync"></i> Dual Party Ledger</a></li>
                                        @endcan
                                    </ul>
                                </div>

                                <!-- Col 3: Profit & Loss -->
                                <div class="col-group col-md-3">
                                    <p class="category-heading">Profit & Loss</p>
                                    <ul class="submenu-item">
                                        <li><a href="{{ route('report.financial_summary') }}"><i class="fas fa-chart-pie"></i> Financial Summary</a></li>
                                        @can('Sale Report')
                                        <li><a href="{{ route('report.profit_loss') }}"><i class="fas fa-file-invoice-dollar"></i> Profit & Loss</a></li>
                                        <li><a href="{{ route('report.customer_wise_profit') }}"><i class="fas fa-user-chart"></i> Customer Profit</a></li>
                                        @endcan
                                        <li><a href="{{ route('report.expense') }}"><i class="fas fa-receipt"></i> Expense Report</a></li>

                                    </ul>
                                </div>

                                <!-- Col 4: Stocks & Inventory -->
                                <div class="col-group col-md-3">
                                    <p class="category-heading">Stocks & Inventory</p>
                                    <ul class="submenu-item">
                                        @can('Item Stock Report')
                                        <li><a href="{{ route('report.item_stock') }}"><i class="fas fa-boxes"></i> Current Stock</a></li>
                                        @endcan
                                        @can('System Reports')
                                        <li><a href="{{ route('System.Reports') }}"><i class="fas fa-cogs"></i> System Reports</a></li>
                                        <li><a href="{{ route('report.party_balances') }}"><i class="fas fa-users-cog"></i> Parties Balances</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>

                </ul>
            </div>
        </div>
    </nav>
</div>