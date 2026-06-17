@extends('admin_panel.layout.app')
@section('content')

<style>
:root {
    --voucher-primary: #2563eb;
    --voucher-primary-hover: #1d4ed8;
    --voucher-primary-light: rgba(37,99,235,0.08);
    --voucher-bg: #f8fafc;
    --voucher-card-bg: #ffffff;
    --voucher-border: #d1d5db;
    --voucher-border-focus: #2563eb;
    --voucher-text: #1e293b;
    --voucher-text-muted: #64748b;
    --voucher-input-bg: #ffffff;
    --voucher-input-border: #b0b7c3;
    --voucher-success: #16a34a;
    --voucher-danger: #dc2626;
    --voucher-card-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
}

.main-content { padding-bottom: 40px; }

.form-label {
    color: var(--voucher-text-muted);
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
    letter-spacing: 0.2px;
}

.form-control, .form-select {
    background: var(--voucher-input-bg);
    border: 1px solid var(--voucher-input-border);
    color: var(--voucher-text);
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 14px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--voucher-primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    background: var(--voucher-input-bg);
    color: var(--voucher-text);
}

.form-control[readonly] {
    background: #f8fafc;
    color: var(--voucher-text-muted);
    cursor: not-allowed;
}

.section-heading {
    font-weight: 700;
    font-size: 15px;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--voucher-border);
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-heading-danger { color: #dc2626; }

.voucher-form-card {
    background: var(--voucher-card-bg);
    border: 1px solid var(--voucher-border);
    border-radius: 12px;
    padding: 28px;
    box-shadow: var(--voucher-card-shadow);
}

.voucher-form-card .card-title {
    color: var(--voucher-text);
    font-weight: 700;
    font-size: 18px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--voucher-border);
}

.table { color: var(--voucher-text); margin-bottom: 0; }
.table > :not(caption) > * > * { padding: 10px 12px; vertical-align: middle; }
.table-light th {
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    background: #f8fafc;
    color: var(--voucher-text-muted);
    border-color: var(--voucher-border) !important;
}
.table tbody td { border-color: var(--voucher-border); }
.table tbody tr:hover { background: rgba(37,99,235,0.02); }

.btn-voucher {
    background: var(--voucher-primary);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 11px 32px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s ease;
}
.btn-voucher:hover {
    background: var(--voucher-primary-hover);
    color: #fff;
    box-shadow: 0 4px 12px rgba(37,99,235,0.3);
}
.btn-add-row {
    border: 1px dashed var(--voucher-input-border) !important;
    color: var(--voucher-text-muted) !important;
    border-radius: 8px !important;
    padding: 9px 22px !important;
    font-weight: 500 !important;
    font-size: 13px !important;
    transition: all 0.2s ease !important;
}
.btn-add-row:hover {
    border-color: var(--voucher-primary) !important;
    color: var(--voucher-primary) !important;
    background: var(--voucher-primary-light) !important;
}
</style>

<div class="main-content">
    <div class="container-fluid px-3 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1" style="color:var(--voucher-text);">Edit Expense Voucher</h3>
                <p class="text-muted mb-0" style="font-size:13px;">Update expense voucher details</p>
            </div>
            <a href="{{ route('voucher.history') }}" class="btn btn-outline-secondary px-3" style="border-radius:8px;font-weight:600;">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="voucher-form-card">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <form action="{{ route('expense-vocher.update', $voucher->id) }}" method="POST">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-1">
                        <label class="form-label">EVID</label>
                        <input type="text" class="form-control" value="{{ $voucher->evid }}" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Entry Date</label>
                        <input type="date" name="entry_date" class="form-control" value="{{ $voucher->entry_date ?? now()->toDateString() }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Source Account <span class="text-danger">*</span></label>
                        <select name="vendor_id" class="form-select" required>
                            <option value="">Select Source</option>
                            @foreach($SourceAccounts as $acc)
                                <option value="{{ $acc->id }}" data-code="{{ $acc->account_code }}" {{ $voucher->party_id == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->account_code }} - {{ $acc->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Account Code</label>
                        <input type="text" name="tel" id="tel" class="form-control" readonly value="{{ $voucher->partyAccount->account_code ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="{{ $voucher->remarks }}">
                    </div>
                </div>

                <div class="section-heading section-heading-danger"><i class="bi bi-list-ul"></i>Expense Details</div>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle" id="voucherTable">
                        <thead class="table-light">
                            <tr>
                                <th width="60%">Expense Account</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                            <tr>
                                <td>
                                    <select name="row_account_id[]" class="form-select rowAccountSub" required>
                                        <option value="">Select Expense Category</option>
                                        @foreach($ExpenseAccounts as $acc)
                                            <option value="{{ $acc->id }}" {{ $row['account_id'] == $acc->id ? 'selected' : '' }}>{{ $acc->title }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input name="amount[]" type="text" class="form-control text-end amount" placeholder="0.00" value="{{ $row['amount'] }}" required></td>
                                <td><button type="button" class="btn btn-danger btn-sm removeRow"><i class="bi bi-trash"></i></button></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th class="text-end">Total:</th>
                                <th><input type="text" name="total_amount" class="form-control text-end fw-bold" id="totalAmount" value="{{ $voucher->total_amount }}" readonly></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-add-row" onclick="addNewRow()">
                        <i class="bi bi-plus-lg"></i> Add Row
                    </button>
                    <div class="d-flex gap-2">
                        <a href="{{ route('voucher.history') }}" class="btn btn-outline-secondary px-4" style="border-radius:8px;font-weight:600;">Cancel</a>
                        <button type="submit" class="btn btn-voucher">
                            <i class="bi bi-check2-circle me-1"></i> Update Voucher
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script>
$(document).ready(function() {
    calculateTotals();
    $('select[name="vendor_id"]').trigger('change');
});

$(document).on('change', 'select[name="vendor_id"]', function() {
    let $selected = $(this).find(':selected');
    let code = $selected.data('code');
    $('#tel').val(code || '');
});

function calculateTotals() {
    let total = 0;
    $('#voucherTable tbody tr').each(function() {
        total += parseFloat($(this).find('.amount').val()) || 0;
    });
    $('#totalAmount').val(total.toFixed(2));
}

$(document).on('input', '.amount', calculateTotals);

function addNewRow() {
    let newRow = `<tr>
        <td>
            <select name="row_account_id[]" class="form-select rowAccountSub">
                <option value="">Select Expense Category</option>
                @foreach($ExpenseAccounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->title }}</option>
                @endforeach
            </select>
        </td>
        <td><input name="amount[]" type="text" class="form-control text-end amount" placeholder="0.00"></td>
        <td><button type="button" class="btn btn-danger btn-sm removeRow"><i class="bi bi-trash"></i></button></td>
    </tr>`;
    $('#voucherTable tbody').append(newRow);
    $('#voucherTable tbody tr:last .rowAccountSub').focus();
}

$(document).on('click', '.removeRow', function() {
    $(this).closest('tr').remove();
    calculateTotals();
});
</script>
@endsection
