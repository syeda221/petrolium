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
    --voucher-text: #1e293b;
    --voucher-text-muted: #64748b;
    --voucher-input-bg: #ffffff;
    --voucher-input-border: #b0b7c3;
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
}

.form-control[readonly] {
    background: #f8fafc;
    color: var(--voucher-text-muted);
    cursor: not-allowed;
}

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

.section-card {
    background: #f8fafc;
    border: 1px solid var(--voucher-border);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.sub-heading {
    font-weight: 700;
    font-size: 14px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.sub-heading-danger { color: #dc2626; }

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
</style>

<div class="main-content">
    <div class="container-fluid px-3 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1" style="color:var(--voucher-text);">Edit Payment Out Voucher</h3>
                <p class="text-muted mb-0" style="font-size:13px;">Update payment made details</p>
            </div>
            <a href="{{ route('voucher.history') }}" class="btn btn-outline-secondary px-3" style="border-radius:8px;font-weight:600;">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="voucher-form-card">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('payment.out.update', ['id' => $payment->id, 'type' => $type]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ $payment->payment_date }}" required>
                    </div>
                </div>

                <div class="section-card">
                    <h6 class="sub-heading sub-heading-danger"><i class="bi bi-person-up"></i> Pay To</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Party Type</label>
                            <input type="text" class="form-control" value="{{ ucfirst($type) }}" readonly>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Party</label>
                            <input type="text" class="form-control" value="{{ $partyName }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label">Pay From (Account)</label>
                        <input type="text" class="form-control" value="{{ $payment->account->title ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="1" value="{{ $payment->amount }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="{{ $payment->note }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('voucher.history') }}" class="btn btn-outline-secondary px-4 me-2" style="border-radius:8px;font-weight:600;">Cancel</a>
                    <button type="submit" class="btn btn-voucher">
                        <i class="bi bi-check2-circle me-1"></i> Update Payment Out
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
