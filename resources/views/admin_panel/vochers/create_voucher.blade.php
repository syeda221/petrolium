@extends('admin_panel.layout.app')
@section('content')

<style>
:root {
    --voucher-primary: #2563eb;
    --voucher-primary-hover: #1d4ed8;
    --voucher-primary-light: rgba(37,99,235,0.08);
    --voucher-bg: #f8fafc;
    --voucher-card-bg: #ffffff;
    --voucher-border: #e2e8f0;
    --voucher-border-focus: #2563eb;
    --voucher-text: #1e293b;
    --voucher-text-muted: #64748b;
    --voucher-input-bg: #ffffff;
    --voucher-input-border: #cbd5e1;
    --voucher-success: #16a34a;
    --voucher-danger: #dc2626;
    --voucher-card-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
}

.main-content {
    padding-bottom: 40px;
}

/* ========= VOUCHER TYPE CARDS ========= */
.voucher-types {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
    margin-bottom: 28px;
}

.voucher-type-btn {
    position: relative;
    background: var(--voucher-card-bg);
    border: 2px solid var(--voucher-border);
    border-radius: 12px;
    padding: 20px 12px 18px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: var(--voucher-text-muted);
    user-select: none;
}

.voucher-type-btn:hover {
    border-color: var(--voucher-primary);
    color: var(--voucher-text);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37,99,235,0.1);
}

.voucher-type-btn.active {
    border-color: var(--voucher-primary);
    background: var(--voucher-primary-light);
    color: var(--voucher-primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
}

.voucher-type-btn .v-icon {
    font-size: 28px;
    display: block;
    margin-bottom: 8px;
    color: var(--voucher-text-muted);
    transition: color 0.2s ease;
}

.voucher-type-btn.active .v-icon {
    color: var(--voucher-primary);
}

.voucher-type-btn .v-label {
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}

/* ========= FORM CARD ========= */
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

/* ========= FORM SECTIONS ========= */
.voucher-form-section {
    display: none;
    animation: fadeSlideIn 0.3s ease;
}

.voucher-form-section.active {
    display: block;
}

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ========= SECTION HEADINGS ========= */
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

.section-heading i {
    font-size: 18px;
}

.section-heading-primary {
    color: var(--voucher-primary);
}

.section-heading-danger {
    color: #dc2626;
}

.sub-heading {
    font-weight: 700;
    font-size: 14px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.sub-heading i {
    font-size: 16px;
}

.sub-heading-primary {
    color: var(--voucher-primary);
}

.sub-heading-danger {
    color: #dc2626;
}

/* ========= FORM CONTROLS ========= */
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

.form-control::placeholder {
    color: #94a3b8;
}

.form-control[readonly] {
    background: #f8fafc;
    color: var(--voucher-text-muted);
    cursor: not-allowed;
}

.form-control-lg {
    font-size: 15px;
    padding: 12px 16px;
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

/* ========= SELECT2 OVERRIDES ========= */
.select2-container--default .select2-selection--single {
    background: var(--voucher-input-bg) !important;
    border: 1px solid var(--voucher-input-border) !important;
    border-radius: 8px !important;
    height: 45px !important;
    padding: 6px 12px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: var(--voucher-text) !important;
    line-height: 30px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 43px !important;
}

.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #94a3b8 !important;
}

.select2-dropdown {
    background: var(--voucher-card-bg) !important;
    border: 1px solid var(--voucher-input-border) !important;
    border-radius: 8px !important;
}

.select2-results__option {
    color: var(--voucher-text) !important;
    padding: 8px 14px !important;
}

.select2-results__option--highlighted {
    background: var(--voucher-primary) !important;
    color: #fff !important;
}

.select2-search__field {
    background: var(--voucher-input-bg) !important;
    color: var(--voucher-text) !important;
    border: 1px solid var(--voucher-input-border) !important;
    border-radius: 6px !important;
}

/* ========= PARTY TYPE RADIOS ========= */
.party-type-group {
    display: flex;
    gap: 6px;
    background: #f1f5f9;
    border: 1px solid var(--voucher-input-border);
    border-radius: 10px;
    padding: 4px;
    width: fit-content;
}

.party-type-option {
    position: relative;
}

.party-type-option input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.party-type-option label {
    display: block;
    padding: 8px 18px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    color: var(--voucher-text-muted);
    transition: all 0.2s ease;
    white-space: nowrap;
}

.party-type-option input:checked + label {
    background: var(--voucher-primary);
    color: #fff;
    box-shadow: 0 2px 8px rgba(37,99,235,0.25);
}

.party-type-option label:hover {
    color: var(--voucher-text);
}

/* ========= BUTTONS ========= */
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

.btn-voucher:disabled {
    opacity: 0.65;
    cursor: not-allowed;
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

/* ========= LOADING SPINNER ========= */
.spinner-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,0.4);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.spinner-overlay.show {
    display: flex;
}

.spinner-box {
    background: var(--voucher-card-bg);
    border-radius: 12px;
    padding: 36px 48px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.spinner-box .spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e2e8f0;
    border-top-color: var(--voucher-primary);
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    margin: 0 auto 14px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.spinner-box p {
    color: var(--voucher-text-muted);
    font-size: 14px;
    margin: 0;
}

/* ========= TABLE STYLING ========= */
.table {
    color: var(--voucher-text);
    margin-bottom: 0;
}

.table > :not(caption) > * > * {
    padding: 10px 12px;
    vertical-align: middle;
}

.table-light th {
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    background: #f8fafc;
    color: var(--voucher-text-muted);
    border-color: var(--voucher-border) !important;
}

.table tbody td {
    border-color: var(--voucher-border);
}

.table tbody tr:hover {
    background: rgba(37,99,235,0.02);
}

/* ========= SECTION CARD ========= */
.section-card {
    background: #f8fafc;
    border: 1px solid var(--voucher-border);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

/* ========= SIDE-BY-SIDE SECTION ========= */
.section-side {
    background: #f8fafc;
    border: 1px solid var(--voucher-border);
    border-radius: 10px;
    padding: 20px;
    height: 100%;
}

/* ========= MISC ========= */
.text-muted {
    color: var(--voucher-text-muted) !important;
}

small.text-muted {
    font-size: 12px;
    margin-top: 4px;
    display: block;
}

/* ========= RESPONSIVE ========= */
@media (max-width: 768px) {
    .voucher-types {
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }
    .voucher-type-btn {
        padding: 14px 8px;
    }
    .voucher-type-btn .v-icon {
        font-size: 22px;
    }
    .voucher-type-btn .v-label {
        font-size: 10px;
    }
    .voucher-form-card {
        padding: 16px;
    }
}

@media (max-width: 480px) {
    .voucher-types {
        grid-template-columns: repeat(2, 1fr);
    }
    .party-type-group {
        flex-wrap: wrap;
    }
}
</style>

<div class="main-content">
    <div class="container-fluid px-3 px-md-4">

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1" style="color:var(--voucher-text);">Create Voucher</h3>
                <p class="text-muted mb-0" style="font-size:13px;">Select a voucher type to begin</p>
            </div>
        </div>

        {{-- Voucher Type Cards --}}
        <div class="voucher-types" id="voucherTypeSelector">
            <div class="voucher-type-btn active" data-type="expense">
                <span class="v-icon bi bi-cash-stack"></span>
                <span class="v-label">Expense</span>
            </div>
            <div class="voucher-type-btn" data-type="payment_in">
                <span class="v-icon bi bi-box-arrow-in-left"></span>
                <span class="v-label">Payment In</span>
            </div>
            <div class="voucher-type-btn" data-type="payment_out">
                <span class="v-icon bi bi-box-arrow-right"></span>
                <span class="v-label">Payment Out</span>
            </div>
            <div class="voucher-type-btn" data-type="income">
                <span class="v-icon bi bi-graph-up-arrow"></span>
                <span class="v-label">Income</span>
            </div>
            <div class="voucher-type-btn" data-type="party_transfer">
                <span class="v-icon bi bi-arrow-left-right"></span>
                <span class="v-label">Party to Party</span>
            </div>
            <div class="voucher-type-btn" data-type="account_transfer">
                <span class="v-icon bi bi-bank"></span>
                <span class="v-label">Account Transfer</span>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="voucher-form-card">
            <div id="formTitle" class="card-title">
                <i class="bi bi-cash-stack me-2"></i>Expense Voucher
            </div>

            {{-- ==================== EXPENSE VOUCHER ==================== --}}
            <div class="voucher-form-section active" id="form-expense">
                <form class="voucher-form" data-action="{{ route('expense.vochers.store') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-1">
                            <label class="form-label">EVID</label>
                            <input type="text" class="form-control" value="{{ $nextEvid }}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="entry_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Source Account (Cash/Bank) <span class="text-danger">*</span></label>
                            <select name="vendor_id" class="form-select select2-account" required>
                                <option value="">Select Source</option>
                                @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" data-code="{{ $acc->account_code }}" data-balance="{{ $acc->opening_balance }}">
                                    {{ $acc->account_code }} - {{ $acc->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Account Code</label>
                            <input type="text" name="tel" class="form-control" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Balance</label>
                            <input type="text" id="expBalance" class="form-control" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Memo / Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional remarks">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle" id="expenseTable">
                            <thead class="table-light">
                            <tr>
                                <th>Expense Category</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody id="expenseRows">
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th class="text-end">Total:</th>
                                    <th id="expenseTotal">0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-add-row add-expense-row">
                            <i class="bi bi-plus-lg"></i> Add Row
                        </button>
                        <button type="submit" class="btn btn-voucher">
                            <i class="bi bi-check2-circle me-1"></i> Save Expense
                        </button>
                    </div>
                </form>
            </div>

            {{-- ==================== PAYMENT IN VOUCHER ==================== --}}
            <div class="voucher-form-section" id="form-payment_in">
                <form class="voucher-form" data-action="{{ route('payment.in.store') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    {{-- Party Section --}}
                    <div class="section-card">
                        <h6 class="sub-heading sub-heading-primary"><i class="bi bi-person-down"></i>Received From</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Party Type <span class="text-danger">*</span></label>
                                <div class="party-type-group">
                                    <div class="party-type-option">
                                        <input type="radio" name="party_type" id="pi_customer" value="customer" class="pi-party-type" checked>
                                        <label for="pi_customer">Customer</label>
                                    </div>
                                    <div class="party-type-option">
                                        <input type="radio" name="party_type" id="pi_vendor" value="vendor" class="pi-party-type">
                                        <label for="pi_vendor">Vendor</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div id="pi_customer_wrapper">
                                    <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                                    <select name="customer_id" class="form-select select2-customer" required>
                                        <option value="">-- Choose Customer --</option>
                                        @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="pi_vendor_wrapper" style="display:none;">
                                    <label class="form-label">Select Vendor <span class="text-danger">*</span></label>
                                    <select name="vendor_id" class="form-select select2-vendor">
                                        <option value="">-- Choose Vendor --</option>
                                        @foreach($vendors as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label">Deposit To (Account) <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-select select2-account" required>
                                <option value="">-- Choose Account --</option>
                                @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->title }} ({{ $acc->account_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="1" required placeholder="Enter amount">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional details">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-voucher">
                            <i class="bi bi-check2-circle me-1"></i> Save Payment In
                        </button>
                    </div>
                </form>
            </div>

            {{-- ==================== PAYMENT OUT VOUCHER ==================== --}}
            <div class="voucher-form-section" id="form-payment_out">
                <form class="voucher-form" data-action="{{ route('payment.out.store') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    {{-- Party Section --}}
                    <div class="section-card">
                        <h6 class="sub-heading sub-heading-danger"><i class="bi bi-person-up"></i>Pay To</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Party Type <span class="text-danger">*</span></label>
                                <div class="party-type-group">
                                    <div class="party-type-option">
                                        <input type="radio" name="party_type" id="po_vendor" value="vendor" class="po-party-type" checked>
                                        <label for="po_vendor">Vendor</label>
                                    </div>
                                    <div class="party-type-option">
                                        <input type="radio" name="party_type" id="po_customer" value="customer" class="po-party-type">
                                        <label for="po_customer">Customer</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div id="po_vendor_wrapper">
                                    <label class="form-label">Select Vendor <span class="text-danger">*</span></label>
                                    <select name="vendor_id" class="form-select select2-vendor" required>
                                        <option value="">-- Choose Vendor --</option>
                                        @foreach($vendors as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="po_customer_wrapper" style="display:none;">
                                    <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                                    <select name="customer_id" class="form-select select2-customer">
                                        <option value="">-- Choose Customer --</option>
                                        @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label">Pay From (Account) <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-select select2-account" required>
                                <option value="">-- Choose Account --</option>
                                @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->title }} ({{ $acc->account_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="1" required placeholder="Enter amount">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional details">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-voucher">
                            <i class="bi bi-check2-circle me-1"></i> Save Payment Out
                        </button>
                    </div>
                </form>
            </div>

            {{-- ==================== INCOME VOUCHER ==================== --}}
            <div class="voucher-form-section" id="form-income">
                <form class="voucher-form" data-action="{{ route('other.income.store') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Memo / Description <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Service charge, Return diff, Misc income">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="1" required placeholder="Enter amount">
                        </div>
                    </div>

                    <div class="section-card">
                        <h6 class="sub-heading sub-heading-primary"><i class="bi bi-person-badge"></i>Party Type</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Party Type <span class="text-danger">*</span></label>
                                <div class="party-type-group">
                                    <div class="party-type-option">
                                        <input type="radio" name="party_type" id="inc_account" value="account" class="inc-party-type" checked>
                                        <label for="inc_account">Account</label>
                                    </div>
                                    <div class="party-type-option">
                                        <input type="radio" name="party_type" id="inc_vendor" value="vendor" class="inc-party-type">
                                        <label for="inc_vendor">Vendor</label>
                                    </div>
                                    <div class="party-type-option">
                                        <input type="radio" name="party_type" id="inc_customer" value="customer" class="inc-party-type">
                                        <label for="inc_customer">Customer</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div id="inc_account_wrapper">
                                    <label class="form-label">Select Account <span class="text-danger">*</span></label>
                                    <select name="account_id" class="form-select select2-account">
                                        <option value="">-- Choose Account --</option>
                                        @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->title }} ({{ $acc->account_code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="inc_vendor_wrapper" style="display:none;">
                                    <label class="form-label">Select Vendor <span class="text-danger">*</span></label>
                                    <select name="vendor_id" class="form-select select2-vendor">
                                        <option value="">-- Choose Vendor --</option>
                                        @foreach($vendors as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="inc_customer_wrapper" style="display:none;">
                                    <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                                    <select name="customer_id" class="form-select select2-customer">
                                        <option value="">-- Choose Customer --</option>
                                        @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional notes">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-voucher">
                            <i class="bi bi-check2-circle me-1"></i> Save Income
                        </button>
                    </div>
                </form>
            </div>

            {{-- ==================== PARTY-TO-PARTY TRANSFER ==================== --}}
            <div class="voucher-form-section" id="form-party_transfer">
                <form class="voucher-form" data-action="{{ route('transfer-vouchers.store') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
                            <input type="date" name="transfer_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Voucher ID</label>
                            <input type="text" class="form-control" value="{{ $nextTvid }}" readonly>
                        </div>
                    </div>

                    <div class="row g-4 mb-3">
                        {{-- Source --}}
                        <div class="col-md-6">
                            <div class="section-side">
                                <h6 class="sub-heading sub-heading-danger"><i class="bi bi-dash-circle"></i>Source Party (Deduct From)</h6>
                                <div class="mb-3">
                                    <label class="form-label">Party Type</label>
                                    <select name="source_type" class="form-select">
                                        <option value="customer">Customer</option>
                                        <option value="vendor">Vendor</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Select Party <span class="text-danger">*</span></label>
                                    <select name="source_id" class="form-select select2-customer" required>
                                        <option value="">-- Select Party --</option>
                                        @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Destination --}}
                        <div class="col-md-6">
                            <div class="section-side">
                                <h6 class="sub-heading sub-heading-primary"><i class="bi bi-plus-circle"></i>Destination Party (Transfer To)</h6>
                                <div class="mb-3">
                                    <label class="form-label">Party Type</label>
                                    <select name="destination_type" class="form-select">
                                        <option value="vendor">Vendor</option>
                                        <option value="customer">Customer</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Select Party <span class="text-danger">*</span></label>
                                    <select name="destination_id" class="form-select select2-vendor" required>
                                        <option value="">-- Select Party --</option>
                                        @foreach($vendors as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control form-control-lg" step="0.01" min="1" required placeholder="Enter amount">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="1" placeholder="Any additional notes..."></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-voucher">
                            <i class="bi bi-check2-circle me-1"></i> Process Transfer
                        </button>
                    </div>
                </form>
            </div>

            {{-- ==================== ACCOUNT TRANSFER ==================== --}}
            <div class="voucher-form-section" id="form-account_transfer">
                <form class="voucher-form" data-action="{{ route('account-transfers.store') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
                            <input type="date" name="transfer_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Voucher ID</label>
                            <input type="text" class="form-control" value="{{ $nextAtvid }}" readonly>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Transfer From Account <span class="text-danger">*</span></label>
                            <select name="from_account_id" class="form-select select2-account" required>
                                <option value="">-- Select Source Account --</option>
                                @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->title }} ({{ $acc->account_code }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Funds will be deducted from this account</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transfer To Account <span class="text-danger">*</span></label>
                            <select name="to_account_id" class="form-select select2-account" required>
                                <option value="">-- Select Target Account --</option>
                                @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->title }} ({{ $acc->account_code }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Funds will be added to this account</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control form-control-lg" step="0.01" min="1" required placeholder="Enter amount">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Any additional notes...">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-voucher">
                            <i class="bi bi-check2-circle me-1"></i> Transfer Funds
                        </button>
                    </div>
                </form>
            </div>

        </div>{{-- /voucher-form-card --}}
    </div>
</div>

{{-- Loading Spinner --}}
<div class="spinner-overlay" id="loadingOverlay">
    <div class="spinner-box">
        <div class="spinner"></div>
        <p>Saving voucher...</p>
    </div>
</div>

@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<script>
$(document).ready(function() {

    // ============== VOUCHER TYPE SELECTOR ==============
    const formTitles = {
        expense:           '<i class="bi bi-cash-stack me-2"></i>Expense Voucher',
        payment_in:        '<i class="bi bi-box-arrow-in-left me-2"></i>Payment In Voucher',
        payment_out:       '<i class="bi bi-box-arrow-right me-2"></i>Payment Out Voucher',
        income:            '<i class="bi bi-graph-up-arrow me-2"></i>Income Voucher',
        party_transfer:    '<i class="bi bi-arrow-left-right me-2"></i>Party-to-Party Transfer',
        account_transfer:  '<i class="bi bi-bank me-2"></i>Account Transfer'
    };

    $('#voucherTypeSelector .voucher-type-btn').on('click', function() {
        var type = $(this).data('type');

        // Update active card
        $('#voucherTypeSelector .voucher-type-btn').removeClass('active');
        $(this).addClass('active');

        // Update form visibility
        $('.voucher-form-section').removeClass('active');
        $('#form-' + type).addClass('active');

        // Update title
        $('#formTitle').html(formTitles[type] || '');

        // Re-initialize Select2 for visible form
        initSelect2();
    });

    // ============== SELECT2 INIT ==============
    function initSelect2() {
        var $active = $('.voucher-form-section.active');

        $active.find('.select2-account, .select2-vendor, .select2-customer, select.form-select').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
        });

        $active.find('.select2-account').select2({ placeholder: 'Search account...', width: '100%' });
        $active.find('.select2-vendor').select2({ placeholder: 'Search vendor...', width: '100%' });
        $active.find('.select2-customer').select2({ placeholder: 'Search customer...', width: '100%' });

        $active.find('select.form-select:not(.select2-hidden-accessible)').each(function() {
            if ($(this).find('option').length > 10 && !$(this).closest('.select2-container').length) {
                $(this).select2({ width: '100%' });
            }
        });
    }

    // ============== INCOME: PARTY TYPE TOGGLE ==============
    $(document).on('change', '.inc-party-type', function() {
        var val = $(this).val();
        $('#inc_account_wrapper, #inc_vendor_wrapper, #inc_customer_wrapper').hide();
        $('[name="account_id"], [name="vendor_id"], [name="customer_id"]').prop('required', false);

        if (val === 'account') {
            $('#inc_account_wrapper').show();
            $('[name="account_id"]').prop('required', true);
        } else if (val === 'vendor') {
            $('#inc_vendor_wrapper').show();
            $('[name="vendor_id"]').prop('required', true);
        } else {
            $('#inc_customer_wrapper').show();
            $('[name="customer_id"]').prop('required', true);
        }
        initSelect2();
    });

    // ============== PAYMENT IN: PARTY TYPE TOGGLE ==============
    $(document).on('change', '.pi-party-type', function() {
        var val = $(this).val();
        $('#pi_customer_wrapper, #pi_vendor_wrapper').hide();
        $('[name="customer_id"], [name="vendor_id"]').prop('required', false);
        if (val === 'customer') {
            $('#pi_customer_wrapper').show();
            $('[name="customer_id"]').prop('required', true);
        } else {
            $('#pi_vendor_wrapper').show();
            $('[name="vendor_id"]').prop('required', true);
        }
        initSelect2();
    });

    // ============== PAYMENT OUT: PARTY TYPE TOGGLE ==============
    $(document).on('change', '.po-party-type', function() {
        var val = $(this).val();
        $('#po_vendor_wrapper, #po_customer_wrapper').hide();
        $('[name="vendor_id"], [name="customer_id"]').prop('required', false);
        if (val === 'vendor') {
            $('#po_vendor_wrapper').show();
            $('[name="vendor_id"]').prop('required', true);
        } else {
            $('#po_customer_wrapper').show();
            $('[name="customer_id"]').prop('required', true);
        }
        initSelect2();
    });

    // ============== PARTY TRANSFER: TYPE SWITCH ==============
    var customers = @json($customers);
    var vendors = @json($vendors);

    function updatePartyDropdown(typeSelect, partySelect) {
        var type = $(typeSelect).val();
        var $sel = $(partySelect);
        $sel.empty().append('<option value="">-- Select Party --</option>');
        if (type === 'customer') {
            customers.forEach(function(c) {
                $sel.append('<option value="' + c.id + '">' + c.customer_name + '</option>');
            });
        } else {
            vendors.forEach(function(v) {
                $sel.append('<option value="' + v.id + '">' + v.name + '</option>');
            });
        }
        if ($sel.hasClass('select2-hidden-accessible')) {
            $sel.select2('destroy').select2({ width: '100%' });
        }
    }

    $(document).on('change', 'select[name="source_type"]', function() {
        updatePartyDropdown(this, 'select[name="source_id"]');
    });
    $(document).on('change', 'select[name="destination_type"]', function() {
        updatePartyDropdown(this, 'select[name="destination_id"]');
    });

    // ============== DYNAMIC ROWS: EXPENSE ==============
    function calcExpenseTotal() {
        var total = 0;
        $('#expenseTable tbody tr').each(function() {
            total += parseFloat($(this).find('.amount').val()) || 0;
        });
        $('#expenseTotal').text(total.toFixed(2));
    }

    $(document).on('input', '#expenseTable .amount', calcExpenseTotal);

    $('.add-expense-row').on('click', function() {
        var row = `<tr>
            <td>
                <select name="row_account_id[]" class="form-select rowAccountSub" required>
                    <option value="">Select Expense Category</option>
                    @foreach($ExpenseAccounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->title }}</option>
                    @endforeach
                </select>
            </td>
            <td><input name="amount[]" type="text" class="form-control text-end amount" placeholder="0.00"></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow"><i class="bi bi-trash"></i></button></td>
        </tr>`;
        $('#expenseTable tbody').append(row);
        $('#expenseTable tbody tr:last .rowAccountSub').focus();
    });

    // ============== REMOVE ROW ==============
    $(document).on('click', '.removeRow', function() {
        $(this).closest('tr').remove();
        calcExpenseTotal();
    });

    // ============== EXISTING EXPENSE SOURCE SELECT ==============
    $(document).on('change', '#form-expense select[name="vendor_id"]', function() {
        var $sel = $(this).find(':selected');
        var code = $sel.data('code');
        var balance = $sel.data('balance');
        $(this).closest('form').find('input[name="tel"]').val(code || '');
        if (balance !== undefined) {
            $('#expBalance').val(parseFloat(balance).toFixed(2));
        }
    });

    // ============== FORM SUBMISSION (AJAX) ==============
    $('.voucher-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var action = $form.data('action');
        var formData = new FormData(this);

        // Show loading
        $('#loadingOverlay').addClass('show');
        $form.find('button[type="submit"]').prop('disabled', true);

        $.ajax({
            url: action,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#loadingOverlay').removeClass('show');
                $form.find('button[type="submit"]').prop('disabled', false);

                // Check for redirect
                if (response.redirect) {
                    Swal.fire({ icon: 'success', title: 'Success', text: response.success || 'Voucher saved!', timer: 1500, showConfirmButton: false });
                    setTimeout(function() { window.location.href = response.redirect; }, 1500);
                    return;
                }

                // Show success toast
                var msg = response.success || response.message || 'Voucher saved successfully!';
                Swal.fire({ icon: 'success', title: 'Success!', text: msg, timer: 2000, showConfirmButton: false });

                // Reload the page to reset form with fresh IDs
                setTimeout(function() { window.location.reload(); }, 1500);
            },
            error: function(xhr) {
                $('#loadingOverlay').removeClass('show');
                $form.find('button[type="submit"]').prop('disabled', false);

                var resp = xhr.responseJSON;
                if (resp && resp.errors) {
                    var msg = '';
                    $.each(resp.errors, function(key, errs) {
                        msg += errs.join('<br>') + '<br>';
                    });
                    Swal.fire({ icon: 'error', title: 'Validation Error', html: msg });
                } else if (resp && resp.error) {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.error });
                } else if (resp && resp.message) {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.message });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again.' });
                }
            }
        });
    });

    // ============== INIT ==============
    initSelect2();

    // Trigger initial income party-type state
    $('#form-income .inc-party-type').trigger('change');

    // Trigger initial payment-in party-type state (default Customer)
    $('#form-payment_in .pi-party-type:checked').trigger('change');

    // Trigger initial payment-out party-type state (default Vendor)
    $('#form-payment_out .po-party-type:checked').trigger('change');

    // Initial Expense source account code display
    $('#form-expense select[name="vendor_id"]').trigger('change');

});
</script>
@endsection
