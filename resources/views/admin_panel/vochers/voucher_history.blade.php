@extends('admin_panel.layout.app')
@section('content')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
:root {
    --primary: #2563eb;
    --primary-light: #dbeafe;
    --success: #16a34a;
    --success-light: #dcfce7;
    --danger: #dc2626;
    --danger-light: #fee2e2;
    --warning: #d97706;
    --warning-light: #fef3c7;
    --indigo: #6366f1;
    --indigo-light: #e0e7ff;
    --pink: #ec4899;
    --pink-light: #fce7f3;
    --border: #d1d5db;
    --bg-light: #f8fafc;
}

.summary-card {
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 16px 20px;
    background: #fff;
    transition: 0.2s;
    min-height: 90px;
}
.summary-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.summary-card .label {
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
}
.summary-card .value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-top: 4px;
}
.type-filter-btn {
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 5px 16px;
    font-size: 0.82rem;
    font-weight: 500;
    background: #fff;
    color: #374151;
    cursor: pointer;
    transition: 0.15s;
}
.type-filter-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}
.type-filter-btn.active {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
}
.filter-section {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 16px 20px;
}
.filter-section .form-label {
    font-size: 0.78rem;
    font-weight: 600;
    margin-bottom: 2px;
    color: #374151;
}
.filter-section .form-control,
.filter-section .form-select {
    font-size: 0.85rem;
    border-color: var(--border);
    box-shadow: none !important;
}
.filter-section .form-control:focus,
.filter-section .form-select:focus {
    border-color: var(--primary);
}
</style>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0" style="color:#1e293b;font-weight:700;">Voucher History</h4>
        <a href="{{ route('create.voucher') }}" class="btn btn-primary btn-sm">+ New Voucher</a>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4" id="summaryCards">
        <div class="col-md-3">
            <div class="summary-card d-flex align-items-center justify-content-between">
                <div>
                    <div class="label">Total Vouchers</div>
                    <div class="value" id="sumTotal" style="color:var(--primary);">0</div>
                </div>
                <div style="font-size:2rem;color:var(--primary);opacity:0.3;"><i class="bi bi-receipt"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card d-flex align-items-center justify-content-between">
                <div>
                    <div class="label">Expense</div>
                    <div class="value" id="sumExpense" style="color:var(--danger);">0</div>
                </div>
                <div style="font-size:2rem;color:var(--danger);opacity:0.3;"><i class="bi bi-arrow-up-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card d-flex align-items-center justify-content-between">
                <div>
                    <div class="label">Income</div>
                    <div class="value" id="sumIncome" style="color:var(--success);">0</div>
                </div>
                <div style="font-size:2rem;color:var(--success);opacity:0.3;"><i class="bi bi-arrow-down-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card d-flex align-items-center justify-content-between">
                <div>
                    <div class="label">Payment In / Out</div>
                    <div class="d-flex gap-3">
                        <span><span style="font-size:0.7rem;color:#6b7280;">In</span> <span id="sumPaymentIn" style="color:var(--success);font-weight:700;">0</span></span>
                        <span><span style="font-size:0.7rem;color:#6b7280;">Out</span> <span id="sumPaymentOut" style="color:var(--danger);font-weight:700;">0</span></span>
                    </div>
                </div>
                <div style="font-size:2rem;color:var(--indigo);opacity:0.3;"><i class="bi bi-currency-exchange"></i></div>
            </div>
        </div>
    </div>

    {{-- Filter Buttons --}}
    <div class="mb-3 d-flex flex-wrap gap-2" id="typeFilterGroup">
        <button class="type-filter-btn active" data-type="all">All</button>
        <button class="type-filter-btn" data-type="expense">Expense</button>
        <button class="type-filter-btn" data-type="payment_in">Payment In</button>
        <button class="type-filter-btn" data-type="payment_out">Payment Out</button>
        <button class="type-filter-btn" data-type="income">Income</button>
        <button class="type-filter-btn" data-type="party_transfer">Party to Party</button>
        <button class="type-filter-btn" data-type="account_transfer">Account Transfer</button>
    </div>

    {{-- Advanced Filters --}}
    <div class="filter-section mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" id="filterFromDate">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" id="filterToDate">
            </div>
            <div class="col-md-2">
                <label class="form-label">Party Type</label>
                <select class="form-select" id="filterPartyType">
                    <option value="">All Parties</option>
                    <option value="customer">Customer</option>
                    <option value="vendor">Vendor</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Account</label>
                <select class="form-select" id="filterAccount">
                    <option value="">All Accounts</option>
                    @foreach($accounts as $ac)
                    <option value="{{ $ac->id }}">{{ $ac->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Min Amt</label>
                <input type="number" step="0.01" class="form-control" id="filterMinAmount" placeholder="Min">
            </div>
            <div class="col-md-1">
                <label class="form-label">Max Amt</label>
                <input type="number" step="0.01" class="form-control" id="filterMaxAmount" placeholder="Max">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-primary btn-sm w-100" id="applyFilters">Apply</button>
                <button class="btn btn-outline-secondary btn-sm" id="resetFilters">Reset</button>
            </div>
        </div>
    </div>

    {{-- DataTable --}}
    <div class="card shadow" style="border:1px solid var(--border);border-radius:10px;">
        <div class="card-body">
            <div class="table-responsive">
                <table id="voucherHistoryTable" class="table table-bordered table-striped" style="width:100%;font-size:0.85rem;">
                    <thead class="table-dark">
                        <tr>
                            <th>Voucher No</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Party</th>
                            <th>Details</th>
                            <th>Amount</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(function() {

    let table = $('#voucherHistoryTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: '{{ route("voucher.history.data") }}',
            data: function(d) {
                d.type = $('#typeFilterGroup .active').data('type');
                d.from_date = $('#filterFromDate').val();
                d.to_date = $('#filterToDate').val();
                d.party_type = $('#filterPartyType').val();
                d.account_id = $('#filterAccount').val();
                d.min_amount = $('#filterMinAmount').val();
                d.max_amount = $('#filterMaxAmount').val();
            },
            dataSrc: function(json) {
                // Update summary cards
                if (json.summary) {
                    $('#sumTotal').text(numberFormat(json.summary.total_amount));
                    $('#sumExpense').text(numberFormat(json.summary.total_expense));
                    $('#sumIncome').text(numberFormat(json.summary.total_income));
                    $('#sumPaymentIn').text(numberFormat(json.summary.total_payment_in));
                    $('#sumPaymentOut').text(numberFormat(json.summary.total_payment_out));
                }
                return json.data;
            }
        },
        columns: [
            { data: 'voucher_no' },
            {
                data: 'type_label',
                className: 'text-center',
                render: function(v, t, r) {
                    let badge = 'bg-secondary';
                    if (r.source === 'expense') badge = 'bg-danger';
                    else if (r.source === 'payment_in') badge = 'bg-success';
                    else if (r.source === 'payment_out') badge = 'bg-warning text-dark';
                    else if (r.source === 'income') badge = 'bg-info text-dark';
                    else if (r.source === 'party_transfer') badge = 'bg-primary';
                    else if (r.source === 'account_transfer') badge = 'bg-dark';
                    return '<span class="badge ' + badge + '">' + v + '</span>';
                }
            },
            { data: 'date' },
            {
                data: null,
                render: function(r) {
                    let html = r.party_name || '-';
                    if (r.party_type_label) {
                        html += ' <small class="text-muted">(' + r.party_type_label + ')</small>';
                    }
                    return html;
                }
            },
            {
                data: 'detail',
                render: function(v) { return v || '-'; }
            },
            { data: 'amount', render: function(v) { return numberFormat(v); }, className: 'text-end' },
            { data: 'remarks', render: function(v) { return v || '-'; } }
        ],
        order: [[2, 'desc']],
        language: {
            searchPlaceholder: 'Search vouchers...',
            processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...'
        }
    });

    // Type filter buttons
    $('#typeFilterGroup').on('click', '.type-filter-btn', function() {
        $('#typeFilterGroup .type-filter-btn').removeClass('active');
        $(this).addClass('active');
        table.ajax.reload();
    });

    // Apply / Reset filters
    $('#applyFilters').on('click', function() { table.ajax.reload(); });
    $('#resetFilters').on('click', function() {
        $('#filterFromDate, #filterToDate, #filterMinAmount, #filterMaxAmount').val('');
        $('#filterPartyType, #filterAccount').val('');
        table.ajax.reload();
    });

    // Press Enter to apply filters
    $('#filterFromDate, #filterToDate, #filterPartyType, #filterAccount, #filterMinAmount, #filterMaxAmount').on('keypress', function(e) {
        if (e.which === 13) { $('#applyFilters').click(); }
    });

    function numberFormat(v) {
        v = parseFloat(v) || 0;
        return v.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

});
</script>

@endsection
