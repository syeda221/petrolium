@extends('admin_panel.layout.app')
@section('content')

<style>
:root {
    --vh-primary: #2563eb;
    --vh-primary-hover: #1d4ed8;
    --vh-primary-light: rgba(37,99,235,0.08);
    --vh-bg: #f8fafc;
    --vh-card-bg: #ffffff;
    --vh-border: #d1d5db;
    --vh-text: #1e293b;
    --vh-text-muted: #64748b;
    --vh-success: #16a34a;
    --vh-danger: #dc2626;
    --vh-warning: #d97706;
    --vh-card-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
    --vh-card-shadow-hover: 0 4px 12px rgba(0,0,0,0.1);
}

.main-content {
    padding-bottom: 40px;
}

/* Summary Cards */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
    margin-bottom: 28px;
}

.summary-card {
    background: var(--vh-card-bg);
    border: 1px solid var(--vh-border);
    border-radius: 12px;
    padding: 18px 20px;
    box-shadow: var(--vh-card-shadow);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.summary-card:hover {
    box-shadow: var(--vh-card-shadow-hover);
    transform: translateY(-1px);
}

.summary-card .label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--vh-text-muted);
}

.summary-card .value {
    font-size: 22px;
    font-weight: 800;
    margin-top: 2px;
    line-height: 1.2;
}

.summary-card .icon {
    font-size: 28px;
    opacity: 0.2;
}

/* Filter Type Cards */
.filter-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}

.filter-card-btn {
    background: var(--vh-card-bg);
    border: 2px solid var(--vh-border);
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    color: var(--vh-text-muted);
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
    letter-spacing: 0.2px;
}

.filter-card-btn:hover {
    border-color: var(--vh-primary);
    color: var(--vh-text);
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(37,99,235,0.08);
}

.filter-card-btn.active {
    border-color: var(--vh-primary);
    background: var(--vh-primary-light);
    color: var(--vh-primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
}

/* Filter Section */
.filter-section {
    background: var(--vh-card-bg);
    border: 1px solid var(--vh-border);
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: var(--vh-card-shadow);
}

.filter-section .form-label {
    color: var(--vh-text-muted);
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 4px;
    letter-spacing: 0.2px;
}

.filter-section .form-control,
.filter-section .form-select {
    background: var(--vh-card-bg);
    border: 1px solid var(--vh-border);
    color: var(--vh-text);
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 13px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.filter-section .form-control:focus,
.filter-section .form-select:focus {
    border-color: var(--vh-primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}

/* DataTable Card */
.data-card {
    background: var(--vh-card-bg);
    border: 1px solid var(--vh-border);
    border-radius: 12px;
    padding: 0;
    box-shadow: var(--vh-card-shadow);
    overflow: hidden;
}

.data-card .card-header-custom {
    padding: 18px 24px;
    border-bottom: 1px solid var(--vh-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--vh-card-bg);
}

.data-card .card-header-custom h5 {
    margin: 0;
    font-weight: 700;
    font-size: 16px;
    color: var(--vh-text);
}

.data-card .table-wrap {
    padding: 0 24px 24px;
}

/* DataTable Overrides */
#voucherHistoryTable {
    font-size: 13px;
    border-collapse: separate;
    border-spacing: 0;
}

#voucherHistoryTable thead th {
    background: #f1f5f9;
    color: var(--vh-text);
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 12px 10px;
    border-bottom: 2px solid var(--vh-border);
    white-space: nowrap;
}

#voucherHistoryTable tbody td {
    padding: 10px;
    vertical-align: middle;
    border-bottom: 1px solid #e2e8f0;
    color: var(--vh-text);
}

#voucherHistoryTable tbody tr:hover {
    background: #f8fafc;
}

#voucherHistoryTable .badge {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
}

/* Action Buttons */
.action-group {
    display: flex;
    gap: 4px;
    justify-content: center;
}

.action-group .btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 13px;
    transition: all 0.15s ease;
}

.action-group .btn:hover {
    transform: translateY(-1px);
}

/* DataTable Search/Info */
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid var(--vh-border);
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 13px;
    margin-left: 6px;
    outline: none;
}

.dataTables_wrapper .dataTables_filter input:focus {
    border-color: var(--vh-primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}

.dataTables_wrapper .dataTables_length select {
    border: 1px solid var(--vh-border);
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 13px;
}

.dataTables_info, .dataTables_paginate {
    font-size: 13px;
    color: var(--vh-text-muted);
}

.dataTables_paginate .paginate_button {
    border-radius: 6px !important;
    padding: 4px 10px !important;
    margin: 0 2px;
}

.dataTables_paginate .paginate_button.current {
    background: var(--vh-primary) !important;
    border-color: var(--vh-primary) !important;
    color: #fff !important;
}

/* Responsive */
@media (max-width: 768px) {
    .summary-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .filter-section .row {
        gap: 8px;
    }
    .filter-card-btn {
        font-size: 12px;
        padding: 7px 12px;
    }
}
</style>

<div class="main-content container-fluid px-3 px-lg-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0" style="color:var(--vh-text);font-weight:800;font-size:20px;">Voucher History</h4>
        <a href="{{ route('create.voucher') }}" class="btn btn-primary px-4 py-2" style="border-radius:10px;font-weight:600;font-size:14px;background:var(--vh-primary);border:0;">
            <i class="fas fa-plus me-1"></i> New Voucher
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="summary-grid" id="summaryCards">
        <div class="summary-card">
            <div>
                <div class="label">Total Vouchers</div>
                <div class="value" id="sumTotal" style="color:var(--vh-primary);">0</div>
            </div>
            <div class="icon"><i class="fas fa-receipt"></i></div>
        </div>
        <div class="summary-card">
            <div>
                <div class="label">Expense</div>
                <div class="value" id="sumExpense" style="color:var(--vh-danger);">0</div>
            </div>
            <div class="icon"><i class="fas fa-arrow-up"></i></div>
        </div>
        <div class="summary-card">
            <div>
                <div class="label">Income</div>
                <div class="value" id="sumIncome" style="color:var(--vh-success);">0</div>
            </div>
            <div class="icon"><i class="fas fa-arrow-down"></i></div>
        </div>
        <div class="summary-card">
            <div>
                <div class="label">Payment In</div>
                <div class="value" id="sumPaymentIn" style="color:var(--vh-success);">0</div>
            </div>
            <div class="icon"><i class="fas fa-arrow-right"></i></div>
        </div>
        <div class="summary-card">
            <div>
                <div class="label">Payment Out</div>
                <div class="value" id="sumPaymentOut" style="color:var(--vh-danger);">0</div>
            </div>
            <div class="icon"><i class="fas fa-arrow-left"></i></div>
        </div>
    </div>

    {{-- Filter Type Cards --}}
    <div class="filter-cards" id="typeFilterGroup">
        <button class="filter-card-btn active" data-type="all">All Vouchers</button>
        <button class="filter-card-btn" data-type="expense">Expense</button>
        <button class="filter-card-btn" data-type="payment_in">Payment In</button>
        <button class="filter-card-btn" data-type="payment_out">Payment Out</button>
        <button class="filter-card-btn" data-type="income">Income</button>
        <button class="filter-card-btn" data-type="party_transfer">Party to Party</button>
        <button class="filter-card-btn" data-type="account_transfer">Account Transfer</button>
    </div>

    {{-- Advanced Filters --}}
    <div class="filter-section">
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
                <button class="btn btn-primary btn-sm w-100" id="applyFilters" style="border-radius:8px;font-weight:600;background:var(--vh-primary);border:0;">Apply</button>
                <button class="btn btn-outline-secondary btn-sm w-100" id="resetFilters" style="border-radius:8px;font-weight:600;">Reset</button>
            </div>
        </div>
    </div>

    {{-- DataTable --}}
    <div class="data-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-list me-2" style="color:var(--vh-primary);"></i>All Vouchers</h5>
        </div>
        <div class="table-wrap">
            <table id="voucherHistoryTable" class="table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Voucher No</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Party</th>
                        <th>Details</th>
                        <th>Amount</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
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
            { data: 'amount', render: function(v) { return numberFormat(v); }, className: 'text-end fw-bold' },
            { data: 'remarks', render: function(v) { return v || '-'; } },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(r) {
                    var btns = [];
                    if (r.edit_url) btns.push({ label: 'Edit', icon: 'fa-pen', color: 'warning', url: r.edit_url, title: 'Edit Voucher' });
                    if (r.print_url) btns.push({ label: 'Print', icon: 'fa-print', color: 'primary', url: r.print_url, title: 'Print Voucher', target: '_blank' });
                    if (r.delete_url) btns.push({ label: 'Delete', icon: 'fa-trash', color: 'danger', url: r.delete_url, title: 'Delete Voucher', isDelete: true, deleteMethod: r.delete_method });
                    if (btns.length === 0) return '-';
                    var html = '<div class="action-group">';
                    $.each(btns, function(i, b) {
                        var cls = 'btn btn-outline-' + b.color;
                        if (b.isDelete) {
                            html += '<button type="button" class="' + cls + '" title="' + b.title + '" data-delete-url="' + b.url + '" data-delete-method="' + (b.deleteMethod || 'GET') + '"><i class="fas ' + b.icon + '"></i></button>';
                        } else {
                            var tgt = b.target ? ' target="' + b.target + '"' : '';
                            html += '<a href="' + b.url + '" class="' + cls + '" title="' + b.title + '"' + tgt + '><i class="fas ' + b.icon + '"></i></a>';
                        }
                    });
                    html += '</div>';
                    return html;
                }
            }
        ],
        order: [[2, 'desc']],
        language: {
            searchPlaceholder: 'Search vouchers...',
            processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...'
        },
        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>tip'
    });

    // Delete handler
    $('#voucherHistoryTable').on('click', '[data-delete-url]', function(e) {
        e.preventDefault();
        var url = $(this).data('delete-url');
        var method = $(this).data('delete-method') || 'GET';
        var row = table.row($(this).closest('tr'));
        var label = row.data() ? row.data().voucher_no + ' (' + row.data().type_label + ')' : 'this voucher';
        Swal.fire({
            title: 'Delete Voucher?',
            html: 'Are you sure you want to delete <strong>' + label + '</strong>?<br>This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then(function(result) {
            if (!result.isConfirmed) return;
            if (method === 'DELETE') {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function() {
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Voucher has been deleted.', timer: 2000, showConfirmButton: false });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to delete voucher.' });
                    }
                });
            } else {
                window.location.href = url;
            }
        });
    });

    // Type filter buttons
    $('#typeFilterGroup').on('click', '.filter-card-btn', function() {
        $('#typeFilterGroup .filter-card-btn').removeClass('active');
        $(this).addClass('active');
        table.ajax.reload();
    });

    $('#applyFilters').on('click', function() { table.ajax.reload(); });
    $('#resetFilters').on('click', function() {
        $('#filterFromDate, #filterToDate, #filterMinAmount, #filterMaxAmount').val('');
        $('#filterPartyType, #filterAccount').val('');
        table.ajax.reload();
    });

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
