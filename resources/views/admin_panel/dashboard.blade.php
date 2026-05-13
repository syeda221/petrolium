@extends('admin_panel.layout.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background: #f4f6fb;
        }

        /* ── Welcome Box ── */
        .welcome-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 0 20px;
        }

        .welcome-box {
            text-align: center;
            padding: 40px 40px 30px;
            background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .25);
            max-width: 100%;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .welcome-box::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .04);
        }

        .welcome-title {
            font-family: 'Poppins', sans-serif;
            font-size: 32px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
        }

        .welcome-subtitle {
            font-size: 15px;
            color: rgba(255, 255, 255, .65);
            margin-bottom: 0;
        }

        .welcome-footer {
            font-size: 12px;
            color: rgba(255, 255, 255, .4);
            margin-top: 18px;
        }

        /* ── Section Title ── */
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #334155;
            margin: 28px 0 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Summary Cards ── */
        .fin-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media(max-width:992px) {
            .fin-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:576px) {
            .fin-cards {
                grid-template-columns: 1fr;
            }
        }

        .fin-card {
            border-radius: 16px;
            padding: 22px 18px;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .13);
            transition: transform .2s, box-shadow .2s;
            cursor: default;
        }

        .fin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, .18);
        }

        .fin-card::after {
            content: '';
            position: absolute;
            bottom: -25px;
            right: -25px;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
        }

        .fin-card .ci {
            font-size: 2rem;
            margin-bottom: 6px;
        }

        .fin-card .cl {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: .8;
        }

        .fin-card .cv {
            font-size: 1.5rem;
            font-weight: 800;
            margin-top: 2px;
            line-height: 1.2;
            word-break: break-all;
        }

        .fin-card .cs {
            font-size: .72rem;
            opacity: .7;
            margin-top: 4px;
        }

        .c1 {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .c2 {
            background: linear-gradient(135deg, #f7797d, #c6426e);
        }

        .c3 {
            background: linear-gradient(135deg, #11998e, #38ef7d);
        }

        .c4 {
            background: linear-gradient(135deg, #f6d365, #fda085);
        }

        /* ── Charts Grid ── */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        @media(max-width:768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: #fff;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .07);
        }

        .chart-card h6 {
            font-weight: 700;
            color: #334155;
            margin-bottom: 16px;
            font-size: .9rem;
        }

        /* ── Accounts List ── */
        .acc-card {
            background: #fff;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .07);
            margin-bottom: 24px;
        }

        .acc-card h6 {
            font-weight: 700;
            color: #334155;
            margin-bottom: 14px;
            font-size: .9rem;
        }

        .acc-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 7px;
            background: #f8fafc;
            transition: background .15s;
        }

        .acc-row:hover {
            background: #e9ecef;
        }

        .acc-name {
            font-weight: 600;
            color: #475569;
            font-size: .88rem;
        }

        .acc-badge {
            font-size: .68rem;
            background: #e2e8f0;
            color: #64748b;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 8px;
        }

        .acc-amount {
            font-weight: 800;
            font-size: .95rem;
        }

        .pos {
            color: #10b981;
        }

        .neg {
            color: #ef4444;
        }

        /* ── Loader ── */
        .fin-loader {
            text-align: center;
            padding: 50px 0;
        }

        .spin-ring {
            width: 44px;
            height: 44px;
            border: 4px solid #e9ecef;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin .8s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ── Quick Links ── */
        .quick-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 24px;
        }

        .ql-btn {
            padding: 8px 18px;
            border-radius: 30px;
            font-size: .82rem;
            font-weight: 600;
            border: 2px solid transparent;
            color: #fff;
            transition: all .2s;
            text-decoration: none;
        }

        .ql-btn:hover {
            transform: translateY(-2px);
            color: #fff;
        }

        .ql-1 {
            background: #667eea;
        }

        .ql-2 {
            background: #f7797d;
        }

        .ql-3 {
            background: #11998e;
        }

        .ql-4 {
            background: #fda085;
        }

        .ql-5 {
            background: #764ba2;
        }

        .ql-6 {
            background: #c6426e;
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid">

                <!-- ── Welcome Banner ── -->
                <div class="welcome-wrapper">
                    <div class="welcome-box">
                        <h1 class="welcome-title">🏢 Al-Owais Petroleum Service</h1>
                        <p class="welcome-subtitle">Business Management & Financial Dashboard</p>
                        <p class="welcome-footer">Developed by <strong style="color:rgba(255,255,255,.6)">ProWave
                                Technologies</strong></p>
                    </div>
                </div>

                <!-- ── Quick Links ── -->
                <div class="quick-links">
                    <a href="{{ route('sale.add') }}" class="ql-btn ql-1">🛒 New Sale</a>
                    <a href="{{ route('add_purchase') }}" class="ql-btn ql-2">📦 New Purchase</a>
                    <a href="{{ route('report.item_stock') }}" class="ql-btn ql-3">📊 Stock Report</a>
                    <a href="{{ route('report.customer.ledger') }}" class="ql-btn ql-4">👥 Customer Ledger</a>
                    <a href="{{ route('report.vendor.ledger') }}" class="ql-btn ql-5">🏭 Vendor Ledger</a>
                    <a href="{{ route('payment.in') }}" class="ql-btn ql-6">💰 Payment In</a>
                </div>

                <!-- ── Loader ── -->
                <div class="fin-loader" id="finLoader">
                    <div class="spin-ring"></div>
                    <p class="text-muted" style="font-size:.85rem;">Loading financial data...</p>
                </div>

                <div id="finContent" style="display:none;">

                    <!-- ── Summary Cards ── -->
                    <p class="section-title">💼 Financial Overview</p>
                    <div class="fin-cards" id="sumCards"></div>
                    
                    <!-- ── Business Conclusion ── -->
                    <div id="businessConclusion" style="margin-bottom: 24px;"></div>

                    <!-- ── Charts ── -->
                    <p class="section-title">📈 Sales & Purchase Trend (Last 6 Months)</p>
                    <div class="charts-grid">
                        <div class="chart-card">
                            <h6>Sales vs Purchases</h6>
                            <canvas id="barChart" height="200"></canvas>
                        </div>
                        <div class="chart-card">
                            <h6>Fund Distribution</h6>
                            <canvas id="doughnutChart" height="200"></canvas>
                        </div>
                    </div>

                    <!-- ── Account Balances ── -->
                    <p class="section-title">🏦 Cash & Bank Balances</p>
                    <div class="acc-card">
                        <div id="accList"></div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
        let barInst = null, donutInst = null;

        function fmt(n) {
            let v = parseFloat(n);
            return 'Rs. ' + v.toLocaleString('en-PK', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }

        $.get("{{ route('report.financial_summary.data') }}", function (d) {
            $('#finLoader').hide();
            $('#finContent').show();

            // ── Cards ──
            const cards = [
                { cls: 'c1', icon: '👥', label: 'Customer Dues', value: fmt(d.customer_dues), sub: 'Receivable from customers' },
                { cls: 'c2', icon: '🏭', label: 'Vendor Dues', value: fmt(d.vendor_dues), sub: 'Payable to vendors' },
                { cls: 'c3', icon: '💰', label: 'Cash & Bank', value: fmt(d.total_account_balance), sub: 'Total account balances' },
                { cls: 'c4', icon: '📦', label: 'Stock Value', value: fmt(d.total_stock_value), sub: 'Inventory at cost price' },
            ];
            let html = '';
            cards.forEach(c => {
                html += `<div class="fin-card ${c.cls}">
                                <div class="ci">${c.icon}</div>
                                <div class="cl">${c.label}</div>
                                <div class="cv">${c.value}</div>
                                <div class="cs">${c.sub}</div>
                             </div>`;
            });
            $('#sumCards').html(html);

            // ── Business Conclusion ──
            let custDues = parseFloat(d.customer_dues) || 0;
            let vendDues = parseFloat(d.vendor_dues) || 0;
            let cashBank = parseFloat(d.total_account_balance) || 0;
            let stockVal = parseFloat(d.total_stock_value) || 0;

            let netBalance = (custDues + cashBank) - vendDues;
            let totalBusinessValue = netBalance + stockVal;

            let conclusionHtml = `
            <div style="background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff; padding: 20px 24px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 25px rgba(0,0,0,0.1); flex-wrap: wrap; gap: 15px;">
                <div style="flex: 1; min-width: 250px;">
                    <h5 style="margin: 0 0 8px 0; font-weight: 700; font-size: 1.15rem; color: #e2e8f0; display: flex; align-items: center; gap: 8px;">
                        <span>💡</span> Business Conclusion
                    </h5>
                    <div style="font-size: 0.9rem; color: #94a3b8; margin-top: 5px; line-height: 1.5;">
                        <div>(Customer Dues <strong style="color:#fff;">${fmt(custDues)}</strong> + Cash & Bank <strong style="color:#fff;">${fmt(cashBank)}</strong>)</div>
                        <div style="border-bottom: 1px dashed rgba(255,255,255,0.2); padding-bottom: 4px; margin-bottom: 4px;">- Vendor Dues <strong style="color:#fff;">${fmt(vendDues)}</strong></div>
                        <div style="color: #34d399;">= Net Liquid Balance: <strong style="color:#fff;">${fmt(netBalance)}</strong></div>
                    </div>
                </div>
                <div style="flex: 1; min-width: 250px; border-left: 2px solid rgba(255,255,255,0.08); padding-left: 20px; text-align: right;">
                    <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8;">Total Business Value</div>
                    <div style="font-size: 0.85rem; color: #cbd5e1; margin-top: 6px;">
                        Net Liquid Balance <strong style="color:#fff;">${fmt(netBalance)}</strong>
                    </div>
                    <div style="font-size: 0.85rem; color: #cbd5e1; margin-top: 2px;">
                        + Stock Value <strong style="color:#fff;">${fmt(stockVal)}</strong>
                    </div>
                    <div style="font-size: 1.8rem; font-weight: 800; color: #38bdf8; margin-top: 8px; line-height: 1;">${fmt(totalBusinessValue)}</div>
                </div>
            </div>`;
            $('#businessConclusion').html(conclusionHtml);

            // ── Bar Chart ──
            const monthMap = {};
            d.monthly_sales.forEach(r => { monthMap[r.month] = monthMap[r.month] || { s: 0, p: 0 }; monthMap[r.month].s = parseFloat(r.total); });
            d.monthly_purchases.forEach(r => { monthMap[r.month] = monthMap[r.month] || { s: 0, p: 0 }; monthMap[r.month].p = parseFloat(r.total); });

            const labels = [], sales = [], purch = [];
            Object.keys(monthMap).sort().forEach(m => {
                const [y, mo] = m.split('-');
                labels.push(new Date(y, mo - 1).toLocaleDateString('en-US', { month: 'short', year: '2-digit' }));
                sales.push(monthMap[m].s);
                purch.push(monthMap[m].p);
            });

            if (barInst) barInst.destroy();
            barInst = new Chart(document.getElementById('barChart'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Sales', data: sales, backgroundColor: 'rgba(102,126,234,.85)', borderRadius: 6 },
                        { label: 'Purchases', data: purch, backgroundColor: 'rgba(247,121,125,.85)', borderRadius: 6 }
                    ]
                },
                options: {
                    responsive: true, plugins: { legend: { position: 'top' } },
                    scales: { y: { ticks: { callback: v => 'Rs.' + (v / 1000).toFixed(0) + 'k' } } }
                }
            });

            // ── Donut Chart ──
            if (donutInst) donutInst.destroy();
            donutInst = new Chart(document.getElementById('doughnutChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Customer Dues', 'Vendor Dues', 'Cash & Bank', 'Stock Value'],
                    datasets: [{
                        data: [Math.abs(d.customer_dues), Math.abs(d.vendor_dues), Math.abs(d.total_account_balance), Math.abs(d.total_stock_value)],
                        backgroundColor: ['#667eea', '#f7797d', '#11998e', '#fda085'],
                        borderWidth: 0, hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true, cutout: '65%',
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: { callbacks: { label: ctx => ' Rs.' + ctx.parsed.toLocaleString('en-PK') } }
                    }
                }
            });

            // ── Account List ──
            let accHtml = '';
            let totalBalance = 0;
            if (!d.account_balances.length) {
                accHtml = '<p class="text-muted text-center">No active accounts found.</p>';
            } else {
                d.account_balances.forEach(a => {
                    const balance = parseFloat(a.balance) || 0;
                    totalBalance += balance;
                    const cls = balance >= 0 ? 'pos' : 'neg';
                    accHtml += `<div class="acc-row">
                            <div><span class="acc-name">${a.title}</span><span class="acc-badge">${a.head}</span></div>
                            <div class="acc-amount ${cls}">${fmt(balance)}</div>
                        </div>`;
                });

                // Append Total Row
                accHtml += `
                    <div class="acc-row mt-3 pt-3" style="border-top: 2px solid #CBD5E1; background: #F1F5F9; border-radius: 12px;">
                        <div><span class="acc-name text-dark" style="font-size: 1rem; letter-spacing: 0.5px;">TOTAL BALANCE</span></div>
                        <div class="acc-amount ${totalBalance >= 0 ? 'pos' : 'neg'}" style="font-size: 1.1rem; text-decoration: underline;">${fmt(totalBalance)}</div>
                    </div>`;
            }
            $('#accList').html(accHtml);

        }).fail(function (xhr) {
            $('#finLoader').html('<p class="text-danger">⚠️ Could not load data. <button onclick="location.reload()" class="btn btn-sm btn-outline-danger">Retry</button></p>');
            console.error(xhr.responseText);
        });
    </script>
@endsection