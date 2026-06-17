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

.small.text-muted {
    font-size: 12px;
    margin-top: 4px;
    display: block;
}

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
                <h3 class="fw-bold mb-1" style="color:var(--voucher-text);">Edit Account Transfer</h3>
                <p class="text-muted mb-0" style="font-size:13px;">Update transfer between accounts</p>
            </div>
            <a href="{{ route('voucher.history') }}" class="btn btn-outline-secondary px-3" style="border-radius:8px;font-weight:600;">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="voucher-form-card">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form action="{{ route('account-transfers.update', $voucher->id) }}" method="POST">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
                        <input type="date" name="transfer_date" class="form-control" value="{{ $voucher->transfer_date }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Voucher ID</label>
                        <input type="text" class="form-control" value="{{ $voucher->atvid }}" readonly>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Transfer From Account <span class="text-danger">*</span></label>
                        <select name="from_account_id" class="form-select" required>
                            <option value="">-- Select Source Account --</option>
                            @foreach($accounts as $ac)
                                <option value="{{ $ac->id }}" {{ $voucher->from_account_id == $ac->id ? 'selected' : '' }}>{{ $ac->title }} ({{ $ac->account_code ?? '' }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Funds will be deducted from this account</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Transfer To Account <span class="text-danger">*</span></label>
                        <select name="to_account_id" class="form-select" required>
                            <option value="">-- Select Target Account --</option>
                            @foreach($accounts as $ac)
                                <option value="{{ $ac->id }}" {{ $voucher->to_account_id == $ac->id ? 'selected' : '' }}>{{ $ac->title }} ({{ $ac->account_code ?? '' }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Funds will be added to this account</small>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-lg" value="{{ $voucher->amount }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="{{ $voucher->remarks }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('voucher.history') }}" class="btn btn-outline-secondary px-4 me-2" style="border-radius:8px;font-weight:600;">Cancel</a>
                    <button type="submit" class="btn btn-voucher">
                        <i class="bi bi-check2-circle me-1"></i> Update Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
