@extends('admin_panel.layout.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

    .reports-hub {
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding: 20px 0;
        background-color: #f8fbff;
    }

    .hub-header {
        margin-bottom: 40px;
        text-align: center;
    }

    .hub-header h2 {
        font-weight: 800;
        color: #1a1f36;
        letter-spacing: -0.5px;
    }

    .hub-header p {
        color: #697386;
        font-size: 1.1rem;
    }

    .category-section {
        margin-bottom: 50px;
    }

    .category-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1a1f36;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
        padding-bottom: 10px;
    }

    .category-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 40px;
        height: 3px;
        background: #37a371;
        border-radius: 2px;
    }

    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
    }

    .report-card {
        background: #fff;
        border-radius: 20px;
        padding: 28px;
        text-decoration: none !important;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .report-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        border-color: #37a371;
    }

    .card-icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        font-size: 24px;
        transition: all 0.3s ease;
    }

    .report-card:hover .card-icon-wrapper {
        transform: scale(1.1) rotate(-5deg);
    }

    .report-card h3 {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1a1f36;
        margin-bottom: 10px;
    }

    .report-card p {
        font-size: 0.9rem;
        color: #697386;
        line-height: 1.6;
        margin-bottom: 0;
    }

    /* Color Variants */
    .bg-soft-green { background-color: #e6f6ef; color: #37a371; }
    .bg-soft-blue { background-color: #e8f2ff; color: #2e71ff; }
    .bg-soft-purple { background-color: #f1eeff; color: #6e56cf; }
    .bg-soft-orange { background-color: #fff4e5; color: #cc6d00; }
    .bg-soft-red { background-color: #fce8e8; color: #e02d2d; }
    .bg-soft-teal { background-color: #e0f2f1; color: #00897b; }

    .arrow-icon {
        position: absolute;
        bottom: 20px;
        right: 20px;
        opacity: 0;
        transform: translateX(-10px);
        transition: all 0.3s ease;
        color: #37a371;
    }

    .report-card:hover .arrow-icon {
        opacity: 1;
        transform: translateX(0);
    }

    @media (max-width: 768px) {
        .reports-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="main-content reports-hub">
    <div class="main-content-inner">
        <div class="container-fluid">

            <div class="hub-header">
                <h2>Reports Center</h2>
                <p>Comprehensive insights and financial analysis for your business</p>
            </div>

            <!-- Section 1: Sales & Purchases -->
            <div class="category-section">
                <h2 class="category-title">
                    <i class="fas fa-shopping-cart text-primary"></i> Sales & Purchases
                </h2>
                <div class="reports-grid">
                    @can('Sale Report')
                    <a href="{{ route('report.sale') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-blue">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Daily Sale Report</h3>
                        <p>Monitor your sales performance on a day-to-day basis.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>

                    <a href="{{ route('report.sale.category') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-teal">
                            <i class="fas fa-list-alt"></i>
                        </div>
                        <h3>Category Wise Sales</h3>
                        <p>Analyze which product categories are driving your revenue.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>

                    <a href="{{ route('report.party_wise_sale') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-purple">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <h3>Party Wise Sale</h3>
                        <p>Analyze sales volume and frequency by specific parties.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>
                    @endcan

                    @can('Purchase Report')
                    <a href="{{ route('report.purchase') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-orange">
                            <i class="fas fa-truck-loading"></i>
                        </div>
                        <h3>Purchase Report</h3>
                        <p>In-depth look at your procurement and vendor costs.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>
                    @endcan
                </div>
            </div>

            <!-- Section 2: Cash & Finance -->
            <div class="category-section">
                <h2 class="category-title">
                    <i class="fas fa-wallet text-success"></i> Cash & Finance
                </h2>
                <div class="reports-grid">
                    @can('Sale Report')
                    <a href="{{ route('report.cash_book') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-orange">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h3>Daily Cash Book</h3>
                        <p>Track your daily cash inflow and outflow transactions.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>
                    @endcan

                    <a href="{{ route('report.expense') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-red">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <h3>Expense Report</h3>
                        <p>Detailed breakdown of operational costs and expenditures.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>

                    @can('Customer Ledger')
                    <a href="{{ route('report.customer.ledger') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-blue">
                            <i class="fas fa-user-invoice"></i>
                        </div>
                        <h3>Customer Ledger</h3>
                        <p>Detailed transaction history for individual customers.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>

                    <a href="{{ route('report.dual_party.ledger') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-purple">
                            <i class="fas fa-sync"></i>
                        </div>
                        <h3>Dual Party Ledger</h3>
                        <p>Special ledger for parties that are both customers and vendors.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>
                    @endcan

                    @can('Vendor Ledger')
                    <a href="{{ route('report.vendor.ledger') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-red">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <h3>Vendor Ledger</h3>
                        <p>Track all payments and balances for your suppliers.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>
                    @endcan

                    <a href="{{ route('report.party_balances') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-green">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h3>Party Balances</h3>
                        <p>Summary of accurate closing balances for all customers and vendors.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>
                </div>
            </div>

            <!-- Section 3: Profit & Loss -->
            <div class="category-section">
                <h2 class="category-title">
                    <i class="fas fa-percentage text-warning"></i> Profit & Loss
                </h2>
                <div class="reports-grid">
                    <a href="{{ route('report.financial_summary') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-blue">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h3>Financial Summary</h3>
                        <p>Comprehensive dashboard of dues, cash balances, and stock value.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>

                    @can('Sale Report')
                    <a href="{{ route('report.profit_loss') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-green">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <h3>Profit & Loss</h3>
                        <p>Detailed analysis of your net income and expenses over time.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>

                    <a href="{{ route('report.customer_wise_profit') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-teal">
                            <i class="fas fa-user-chart"></i>
                        </div>
                        <h3>Customer Wise Profit</h3>
                        <p>Identify your most profitable customers and regions.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>
                    @endcan
                </div>
            </div>

            <!-- Section 4: Stocks & Inventory -->
            <div class="category-section">
                <h2 class="category-title">
                    <i class="fas fa-box text-secondary"></i> Stocks & Inventory
                </h2>
                <div class="reports-grid">
                    @can('Item Stock Report')
                    <a href="{{ route('report.item_stock') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-purple">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h3>Current Stock Report</h3>
                        <p>Real-time inventory levels and warehouse stock availability.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>
                    @endcan

                    @can('System Reports')
                    <a href="{{ route('System.Reports') }}" class="report-card">
                        <div class="card-icon-wrapper bg-soft-red">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3>System Reports</h3>
                        <p>Automated system-level reports and audit trails.</p>
                        <i class="fas fa-arrow-right arrow-icon"></i>
                    </a>
                    @endcan
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
