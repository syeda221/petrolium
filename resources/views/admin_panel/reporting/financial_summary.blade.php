@extends('admin_panel.layout.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    .fin-dash { font-family: 'Inter', sans-serif; padding: 20px 0; }

    /* ── Summary Cards ── */
    .fin-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }

    .fin-card {
        border-radius: 16px;
        padding: 24px 20px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0,0,0,.12);
        transition: transform .2s;
    }
    .fin-card:hover { transform: translateY(-4px); }
    .fin-card::before {
        content: '';
        position: absolute; top: -30px; right: -30px;
        width: 100px; height: 100px;
        border-radius: 50%;
        background: rgba(255,255,255,.1);
    }
    .fin-card .card-icon { font-size: 2.2rem; margin-bottom: 8px; }
    .fin-card .card-label { font-size: .75rem; text-transform: uppercase; letter-spacing: 1px; opacity: .85; }
    .fin-card .card-value { font-size: 1.7rem; font-weight: 700; margin-top: 4px; }
    .fin-card .card-sub { font-size: .75rem; opacity: .75; margin-top: 4px; }

    .card-cust   { background: linear-gradient(135deg, #667eea, #764ba2); }
    .card-vend   { background: linear-gradient(135deg, #f7797d, #c6426e); }
    .card-cash   { background: linear-gradient(135deg, #11998e, #38ef7d); }
    .card-stock  { background: linear-gradient(135deg, #f6d365, #fda085); }
    .card-exp    { background: linear-gradient(135deg, #FF9966, #ff5e62); }

    /* ── Charts Row ── */
    .charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
    @media(max-width:768px){ .charts-row { grid-template-columns: 1fr; } }

    .chart-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 16px rgba(0,0,0,.08);
    }
    .chart-card h6 {
        font-weight: 600;
        color: #333;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ── Accounts Table ── */
    .accounts-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 16px rgba(0,0,0,.08);
        margin-bottom: 20px;
    }
    .accounts-card h6 { font-weight: 600; color: #333; margin-bottom: 16px; }

    .acc-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 14px;
        border-radius: 10px;
        margin-bottom: 8px;
        background: #f8f9fa;
        transition: background .15s;
    }
    .acc-row:hover { background: #e9ecef; }
    .acc-title { font-weight: 500; color: #444; font-size: .9rem; }
    .acc-bal { font-weight: 700; font-size: .95rem; }
    .acc-bal.pos { color: #11998e; }
    .acc-bal.neg { color: #f7797d; }
    .acc-head-badge { font-size: .7rem; background: #e9ecef; color: #666; padding: 2px 8px; border-radius: 20px; }

    /* ── Loader ── */
    #fin-loader { text-align: center; padding: 60px 0; }
    .spinner-ring {
        width: 50px; height: 50px;
        border: 4px solid #e9ecef;
        border-top-color: #667eea;
        border-radius: 50%;
        animation: spin .8s linear infinite;
        margin: 0 auto 12px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="main-content fin-dash">
    <div class="main-content-inner">
        <div class="container-fluid">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0 fw-bold">📊 Financial Summary Dashboard</h4>
                    <small class="text-muted">Real-time overview of your business finances</small>
                </div>
                <button class="btn btn-primary btn-sm" onclick="loadData()">
                    🔄 Refresh
                </button>
            </div>

            <!-- Loader -->
            <div id="fin-loader">
                <div class="spinner-ring"></div>
                <p class="text-muted">Loading financial data...</p>
            </div>

            <!-- Main Content (hidden until loaded) -->
            <div id="fin-content" style="display:none;">

                <!-- ── Summary Cards ── -->
                <div class="fin-cards" id="summaryCards"></div>

                <!-- ── Charts ── -->
                <div class="charts-row">
                    <div class="chart-card">
                        <h6>📈 Sales vs Purchases (Last 6 Months)</h6>
                        <canvas id="salesChart" height="220"></canvas>
                    </div>
                    <div class="chart-card">
                        <h6>🥧 Financial Breakdown</h6>
                        <canvas id="donutChart" height="220"></canvas>
                    </div>
                </div>

                <!-- ── Account Balances ── -->
                <div class="accounts-card">
                    <h6>🏦 Cash & Bank Account Balances</h6>
                    <div id="accountsList"></div>
                </div>

            </div><!-- /fin-content -->

        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<script>
    let salesChartInst = null;
    let donutChartInst = null;

    function fmt(n) {
        return 'Rs. ' + parseFloat(n).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function loadData() {
        $('#fin-loader').show();
        $('#fin-content').hide();

        $.get("{{ route('report.financial_summary.data') }}", function(d) {
            $('#fin-loader').hide();
            $('#fin-content').show();

            // ── Summary Cards ──
            const cards = [
                {
                    cls: 'card-cust',
                    icon: '👥',
                    label: 'Customer Dues (Receivable)',
                    value: fmt(d.customer_dues),
                    sub: 'Amount customers owe you'
                },
                {
                    cls: 'card-vend',
                    icon: '🏭',
                    label: 'Vendor Dues (Payable)',
                    value: fmt(d.vendor_dues),
                    sub: 'Amount you owe vendors'
                },
                {
                    cls: 'card-cash',
                    icon: '💰',
                    label: 'Cash & Bank Balance',
                    value: fmt(d.total_account_balance),
                    sub: 'Total across all accounts'
                },
                {
                    cls: 'card-stock',
                    icon: '📦',
                    label: 'Stock Value',
                    value: fmt(d.total_stock_value),
                    sub: 'Remaining inventory at cost'
                },
                {
                    cls: 'card-exp',
                    icon: '💸',
                    label: 'Total Expenses',
                    value: fmt(d.total_expenses),
                    sub: 'Total operational costs'
                },
            ];

            let cardsHtml = '';
            cards.forEach(c => {
                cardsHtml += `
                    <div class="fin-card ${c.cls}">
                        <div class="card-icon">${c.icon}</div>
                        <div class="card-label">${c.label}</div>
                        <div class="card-value">${c.value}</div>
                        <div class="card-sub">${c.sub}</div>
                    </div>`;
            });
            $('#summaryCards').html(cardsHtml);

            // ── Sales vs Purchases Chart ──
            const months = [];
            const salesTotals = [];
            const purchTotals = [];
            const expTotals = [];

            // Build a combined month set
            const monthMap = {};
            d.monthly_sales.forEach(r => { monthMap[r.month] = monthMap[r.month] || { s: 0, p: 0, e: 0 }; monthMap[r.month].s = parseFloat(r.total); });
            d.monthly_purchases.forEach(r => { monthMap[r.month] = monthMap[r.month] || { s: 0, p: 0, e: 0 }; monthMap[r.month].p = parseFloat(r.total); });
            d.monthly_expenses.forEach(r => { monthMap[r.month] = monthMap[r.month] || { s: 0, p: 0, e: 0 }; monthMap[r.month].e = parseFloat(r.total); });

            Object.keys(monthMap).sort().forEach(m => {
                // Format: Apr 26
                const [y, mo] = m.split('-');
                const label = new Date(y, mo - 1).toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
                months.push(label);
                salesTotals.push(monthMap[m].s);
                purchTotals.push(monthMap[m].p);
                expTotals.push(monthMap[m].e);
            });

            if (salesChartInst) salesChartInst.destroy();
            salesChartInst = new Chart(document.getElementById('salesChart'), {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Sales',
                            data: salesTotals,
                            backgroundColor: 'rgba(102, 126, 234, 0.85)',
                            borderRadius: 6,
                        },
                        {
                            label: 'Purchases',
                            data: purchTotals,
                            backgroundColor: 'rgba(247, 121, 125, 0.85)',
                            borderRadius: 6,
                        },
                        {
                            label: 'Expenses',
                            data: expTotals,
                            backgroundColor: 'rgba(255, 153, 102, 0.85)',
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        y: {
                            ticks: {
                                callback: v => 'Rs.' + (v / 1000).toFixed(0) + 'k'
                            }
                        }
                    }
                }
            });

            // ── Donut Chart ──
            if (donutChartInst) donutChartInst.destroy();
            donutChartInst = new Chart(document.getElementById('donutChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Customer Dues', 'Vendor Dues', 'Cash & Bank', 'Stock Value', 'Expenses'],
                    datasets: [{
                        data: [
                            Math.abs(d.customer_dues),
                            Math.abs(d.vendor_dues),
                            Math.abs(d.total_account_balance),
                            Math.abs(d.total_stock_value),
                            Math.abs(d.total_expenses)
                        ],
                        backgroundColor: ['#667eea', '#f7797d', '#11998e', '#fda085', '#ff5e62'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '65%',
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: ctx => ' Rs. ' + ctx.parsed.toLocaleString('en-PK', { minimumFractionDigits: 2 })
                            }
                        }
                    }
                }
            });

            // ── Account Balances ──
            let accHtml = '';
            if (d.account_balances.length === 0) {
                accHtml = '<p class="text-muted text-center">No accounts found in Chart of Accounts.</p>';
            } else {
                d.account_balances.forEach(a => {
                    const balClass = a.balance >= 0 ? 'pos' : 'neg';
                    accHtml += `
                        <div class="acc-row">
                            <div>
                                <span class="acc-title">${a.title}</span>
                                <span class="acc-head-badge ms-2">${a.head}</span>
                            </div>
                            <div class="acc-bal ${balClass}">${fmt(a.balance)}</div>
                        </div>`;
                });
            }
            $('#accountsList').html(accHtml);

        }).fail(function(xhr) {
            $('#fin-loader').hide();
            $('#fin-content').show();
            alert('Error loading data. Check console.');
            console.error(xhr.responseText);
        });
    }

    $(document).ready(function() {
        loadData();
    });
</script>
@endsection
