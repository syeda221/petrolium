@extends('admin_panel.layout.app')
@section('content')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
    /* Modern Professional Styling */
    .journal-page {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 2rem;
        color: white;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        margin-bottom: 2rem;
    }

    .page-header h2 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .page-header .subtitle {
        opacity: 0.9;
        font-size: 0.95rem;
        margin-top: 0.5rem;
    }

    /* Date Selector Styling */
    .date-selector-wrapper {
        background: white;
        padding: 1rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }

    .date-selector-wrapper input[type="date"] {
        border: 2px solid #e0e7ff;
        border-radius: 8px;
        padding: 0.6rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .date-selector-wrapper input[type="date"]:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Summary Cards */
    .summary-card {
        border-radius: 15px;
        padding: 1.5rem;
        color: white;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }

    .summary-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: rgba(255,255,255,0.1);
        transform: rotate(45deg);
        transition: all 0.5s ease;
    }

    .summary-card:hover::before {
        top: -100%;
        right: -100%;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .card-opening {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .card-in {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .card-out {
        background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
    }

    .card-closing {
        background: linear-gradient(135deg, #000428 0%, #004e92 100%);
    }

    .summary-card .card-icon {
        font-size: 2.5rem;
        opacity: 0.3;
        position: absolute;
        top: 10px;
        right: 20px;
    }

    .summary-card h6 {
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        opacity: 0.9;
        margin-bottom: 0.5rem;
        letter-spacing: 1px;
    }

    .summary-card h2 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }

    .summary-card .breakdown {
        font-size: 0.8rem;
        opacity: 0.85;
        margin-top: 0.5rem;
        line-height: 1.6;
    }

    /* Main Content Card */
    .content-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .content-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        border: none;
    }

    .content-card .card-header h5 {
        margin: 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Section Headers */
    .section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .section-header.money-in {
        background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);
        color: #155724;
    }

    .section-header.money-out {
        background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
        color: #721c24;
    }

    /* Tables */
    .transaction-table {
        margin-bottom: 0;
    }

    .transaction-table thead {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .transaction-table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        padding: 1rem;
        border: none;
        color: #495057;
    }

    .transaction-table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    .transaction-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .transaction-table tfoot {
        background: #f8f9fa;
        font-weight: 700;
    }

    /* Scrollable Table Container */
    .table-scroll-container {
        position: relative;
        max-height: 400px;
        overflow-y: auto;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        background: white;
        box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
    }

    .table-scroll-container::-webkit-scrollbar {
        width: 10px;
    }

    .table-scroll-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .table-scroll-container::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
    }

    .table-scroll-container::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }

    /* Sticky Header */
    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 10;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* Scroll Footer Indicator */
    .scroll-footer {
        text-align: center;
        padding: 0.75rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 0 0 8px 8px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    /* Summary Row */
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }

    .summary-row .summary-label {
        font-weight: 600;
        font-size: 1.1rem;
        color: #495057;
    }

    .summary-row .summary-value {
        font-weight: 700;
        font-size: 1.3rem;
    }

    /* Badges */
    .type-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-sale {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .badge-receipt {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }

    .badge-payment {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .badge-expense {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
    }

    /* Summary Box */
    .summary-box {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);
    }

    .summary-box table {
        margin-bottom: 1.5rem;
    }

    .summary-box table tr {
        border-bottom: 1px solid #dee2e6;
    }

    .summary-box table tr:last-child {
        border-bottom: none;
    }

    .summary-box table th,
    .summary-box table td {
        padding: 0.75rem;
    }

    .summary-box .total-row {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
    }

    /* Close Day Button */
    .btn-close-day {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(245, 87, 108, 0.3);
    }

    .btn-close-day:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(245, 87, 108, 0.4);
        background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
    }

    /* Day Closed Alert */
    .day-closed-alert {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 10px;
        text-align: center;
        font-size: 1.1rem;
        font-weight: 600;
        box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        opacity: 0.3;
        margin-bottom: 1rem;
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-in {
        animation: fadeInUp 0.5s ease;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .summary-card h2 {
            font-size: 1.5rem;
        }
        
        .page-header h2 {
            font-size: 1.5rem;
        }
    }
</style>

<div class="journal-page">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="page-header animate-in">
            <h2>
                <i class="bi bi-journal-text"></i>
                Journal Vouchers (Day Book)
            </h2>
            <div class="subtitle">
                Complete daily financial summary with all money movements
            </div>
        </div>

        <!-- Date Selector -->
        <div class="row mb-4 animate-in">
            <div class="col-md-12">
                <div class="date-selector-wrapper">
                    <form action="{{ route('journal-vouchers') }}" method="GET" class="d-flex align-items-center gap-3">
                        <label class="form-label mb-0 fw-bold text-muted">Select Date:</label>
                        <input type="date" name="date" class="form-control" value="{{ $selectedDate }}" 
                               max="{{ now()->toDateString() }}" onchange="this.form.submit()" style="max-width: 200px;">
                        <div class="ms-auto text-muted">
                            <i class="bi bi-calendar-event me-2"></i>
                            Viewing: <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</strong>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show animate-in" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Summary Cards -->
        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="summary-card card-opening animate-in">
                    <i class="bi bi-wallet2 card-icon"></i>
                    <h6>Opening Balance</h6>
                    <h2>₨ {{ number_format($opening, 2) }}</h2>
                    <div class="breakdown">Previous day closing</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="summary-card card-in animate-in" style="animation-delay: 0.1s;">
                    <i class="bi bi-arrow-down-circle card-icon"></i>
                    <h6>Total IN (Received)</h6>
                    <h2>₨ {{ number_format($totalIn, 2) }}</h2>
                    <div class="breakdown">
                        Sales: ₨ {{ number_format($totalSales, 2) }}<br>
                        Receipts: ₨ {{ number_format($totalReceipts, 2) }}
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="summary-card card-out animate-in" style="animation-delay: 0.2s;">
                    <i class="bi bi-arrow-up-circle card-icon"></i>
                    <h6>Total OUT (Paid)</h6>
                    <h2>₨ {{ number_format($totalOut, 2) }}</h2>
                    <div class="breakdown">
                        Payments: ₨ {{ number_format($totalPayments, 2) }}<br>
                        Expenses: ₨ {{ number_format($totalExpenses, 2) }}
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="summary-card card-closing animate-in" style="animation-delay: 0.3s;">
                    <i class="bi bi-piggy-bank card-icon"></i>
                    <h6>Closing Balance</h6>
                    <h2>₨ {{ number_format($closing, 2) }}</h2>
                    <div class="breakdown">Net position for the day</div>
                </div>
            </div>
        </div>

        <!-- Transactions Detail -->
        <div class="content-card animate-in" style="animation-delay: 0.4s;">
            <div class="card-header">
                <h5>
                    <i class="bi bi-receipt"></i>
                    Transaction Details
                </h5>
            </div>
            <div class="card-body p-4">
                
                <!-- Money IN Section -->
                <div class="section-header money-in">
                    <i class="bi bi-arrow-down-circle-fill"></i>
                    Money IN (Received)
                    <span class="ms-auto badge bg-success">{{ $sales->count() + $receipts->count() }} Transactions</span>
                </div>

                @if($sales->count() > 0 || $receipts->count() > 0)
                <div class="table-scroll-container mb-4">
                    <div class="table-responsive">
                        <table class="table transaction-table">
                            <thead class="sticky-header">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 20%">Type</th>
                                    <th style="width: 25%">Reference</th>
                                    <th style="width: 20%">Time</th>
                                    <th class="text-end" style="width: 30%">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $counter = 1; @endphp
                                @foreach($sales as $item)
                                <tr>
                                    <td>{{ $counter++ }}</td>
                                    <td><span class="type-badge badge-sale"><i class="bi bi-cart-check"></i> Sale</span></td>
                                    <td>Sale #{{ $item->reference }}</td>
                                    <td><small class="text-muted">{{ \Carbon\Carbon::parse($item->date)->format('h:i A') }}</small></td>
                                    <td class="text-end fw-bold text-success">₨ {{ number_format($item->amount, 2) }}</td>
                                </tr>
                                @endforeach

                                @foreach($receipts as $item)
                                <tr>
                                    <td>{{ $counter++ }}</td>
                                    <td><span class="type-badge badge-receipt"><i class="bi bi-cash-coin"></i> Receipt</span></td>
                                    <td>{{ $item->reference }}</td>
                                    <td><small class="text-muted">{{ \Carbon\Carbon::parse($item->date)->format('h:i A') }}</small></td>
                                    <td class="text-end fw-bold text-success">₨ {{ number_format($item->amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="scroll-footer">
                        <i class="bi bi-arrow-down"></i> Scroll to see all {{ $sales->count() + $receipts->count() }} transactions
                    </div>
                </div>
                <div class="summary-row mb-4">
                    <div class="summary-label">Total IN:</div>
                    <div class="summary-value text-success">₨ {{ number_format($totalIn, 2) }}</div>
                </div>
                @else
                <div class="empty-state mb-4">
                    <i class="bi bi-inbox"></i>
                    <p>No money received on this date</p>
                </div>
                @endif

                <!-- Money OUT Section -->
                <div class="section-header money-out">
                    <i class="bi bi-arrow-up-circle-fill"></i>
                    Money OUT (Paid)
                    <span class="ms-auto badge bg-warning text-dark">{{ $payments->count() + $expenses->count() }} Transactions</span>
                </div>

                @if($payments->count() > 0 || $expenses->count() > 0)
                <div class="table-scroll-container mb-4">
                    <div class="table-responsive">
                        <table class="table transaction-table">
                            <thead class="sticky-header">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 20%">Type</th>
                                    <th style="width: 25%">Reference</th>
                                    <th style="width: 20%">Time</th>
                                    <th class="text-end" style="width: 30%">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $counter = 1; @endphp
                                @foreach($payments as $item)
                                <tr>
                                    <td>{{ $counter++ }}</td>
                                    <td><span class="type-badge badge-payment"><i class="bi bi-wallet"></i> Payment</span></td>
                                    <td>{{ $item->reference }}</td>
                                    <td><small class="text-muted">{{ \Carbon\Carbon::parse($item->date)->format('h:i A') }}</small></td>
                                    <td class="text-end fw-bold text-danger">₨ {{ number_format($item->amount, 2) }}</td>
                                </tr>
                                @endforeach

                                @foreach($expenses as $item)
                                <tr>
                                    <td>{{ $counter++ }}</td>
                                    <td><span class="type-badge badge-expense"><i class="bi bi-receipt-cutoff"></i> Expense</span></td>
                                    <td>{{ $item->reference }}</td>
                                    <td><small class="text-muted">{{ \Carbon\Carbon::parse($item->date)->format('h:i A') }}</small></td>
                                    <td class="text-end fw-bold text-danger">₨ {{ number_format($item->amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="scroll-footer">
                        <i class="bi bi-arrow-down"></i> Scroll to see all {{ $payments->count() + $expenses->count() }} transactions
                    </div>
                </div>
                <div class="summary-row mb-4">
                    <div class="summary-label">Total OUT:</div>
                    <div class="summary-value text-danger">₨ {{ number_format($totalOut, 2) }}</div>
                </div>
                @else
                <div class="empty-state mb-4">
                    <i class="bi bi-inbox"></i>
                    <p>No payments made on this date</p>
                </div>
                @endif

                <!-- Summary -->
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <div class="summary-box">
                            <table class="table mb-0">
                                <tr>
                                    <th>Opening Balance:</th>
                                    <td class="text-end">₨ {{ number_format($opening, 2) }}</td>
                                </tr>
                                <tr style="background: #d4fc79;">
                                    <th>Total IN (+):</th>
                                    <td class="text-end text-success fw-bold">₨ {{ number_format($totalIn, 2) }}</td>
                                </tr>
                                <tr style="background: #ffeaa7;">
                                    <th>Total OUT (-):</th>
                                    <td class="text-end text-danger fw-bold">₨ {{ number_format($totalOut, 2) }}</td>
                                </tr>
                                <tr class="total-row">
                                    <th>Closing Balance:</th>
                                    <td class="text-end">₨ {{ number_format($closing, 2) }}</td>
                                </tr>
                            </table>

                            <div class="mt-3">
                                @if(!$dayClosed)
                                <form action="{{ route('close-day') }}" method="POST" id="closeDayForm">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                                    <button type="button" class="btn btn-close-day w-100" onclick="confirmCloseDay()">
                                        <i class="bi bi-lock-fill me-2"></i>Close Day & Lock Transactions
                                    </button>
                                </form>
                                <small class="text-muted d-block mt-2 text-center">
                                    <i class="bi bi-info-circle"></i> Closing will lock this day and open next day
                                </small>
                                @else
                                <div class="day-closed-alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    This Day Has Been Closed & Locked
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function confirmCloseDay() {
    if(confirm('⚠️ Are you sure you want to close this day?\n\n✅ This will:\n• Lock all transactions for {{ \Carbon\Carbon::parse($selectedDate)->format("d M Y") }}\n• Save closing balance: ₨ {{ number_format($closing, 2) }}\n• Open next day with this closing as opening\n\n❌ You cannot undo this action!')) {
        document.getElementById('closeDayForm').submit();
    }
}
</script>

@endsection
