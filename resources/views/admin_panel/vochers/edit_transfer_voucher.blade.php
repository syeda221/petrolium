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

.section-side {
    background: #f8fafc;
    border: 1px solid var(--voucher-border);
    border-radius: 10px;
    padding: 20px;
    height: 100%;
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
.sub-heading-primary { color: var(--voucher-primary); }

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

.party-type-group {
    display: flex;
    gap: 6px;
    background: #f1f5f9;
    border: 1px solid var(--voucher-input-border);
    border-radius: 10px;
    padding: 4px;
    width: fit-content;
}

.party-type-option { position: relative; }
.party-type-option input { position: absolute; opacity: 0; width: 0; height: 0; }
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
.party-type-option label:hover { color: var(--voucher-text); }

.text-muted { color: var(--voucher-text-muted) !important; }
</style>

<div class="main-content">
    <div class="container-fluid px-3 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1" style="color:var(--voucher-text);">Edit Party-to-Party Transfer</h3>
                <p class="text-muted mb-0" style="font-size:13px;">Update transfer details between parties</p>
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

            <form action="{{ route('transfer-vouchers.update', $voucher->id) }}" method="POST">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
                        <input type="date" name="transfer_date" class="form-control" value="{{ $voucher->transfer_date }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Voucher ID</label>
                        <input type="text" class="form-control" value="{{ $voucher->tvid }}" readonly>
                    </div>
                </div>

                <div class="row g-4 mb-3">
                    <div class="col-md-6">
                        <div class="section-side">
                            <h6 class="sub-heading sub-heading-danger"><i class="bi bi-dash-circle"></i>Source Party (Deduct From)</h6>
                            <div class="mb-3">
                                <label class="form-label">Party Type</label>
                                <div class="party-type-group">
                                    <div class="party-type-option">
                                        <input type="radio" name="source_type" id="src_customer" value="customer" class="src-type" {{ $voucher->source_party_type == 'customer' ? 'checked' : '' }}>
                                        <label for="src_customer">Customer</label>
                                    </div>
                                    <div class="party-type-option">
                                        <input type="radio" name="source_type" id="src_vendor" value="vendor" class="src-type" {{ $voucher->source_party_type == 'vendor' ? 'checked' : '' }}>
                                        <label for="src_vendor">Vendor</label>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Select Party <span class="text-danger">*</span></label>
                                <select name="source_id" id="source_id" class="form-select" required>
                                    <option value="">-- Select Party --</option>
                                    @if($voucher->source_party_type == 'customer')
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}" {{ $voucher->source_party_id == $c->id ? 'selected' : '' }}>{{ $c->customer_name }}</option>
                                        @endforeach
                                    @else
                                        @foreach($vendors as $v)
                                            <option value="{{ $v->id }}" {{ $voucher->source_party_id == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="section-side">
                            <h6 class="sub-heading sub-heading-primary"><i class="bi bi-plus-circle"></i>Destination Party (Transfer To)</h6>
                            <div class="mb-3">
                                <label class="form-label">Party Type</label>
                                <div class="party-type-group">
                                    <div class="party-type-option">
                                        <input type="radio" name="destination_type" id="dst_vendor" value="vendor" class="dst-type" {{ $voucher->destination_party_type == 'vendor' ? 'checked' : '' }}>
                                        <label for="dst_vendor">Vendor</label>
                                    </div>
                                    <div class="party-type-option">
                                        <input type="radio" name="destination_type" id="dst_customer" value="customer" class="dst-type" {{ $voucher->destination_party_type == 'customer' ? 'checked' : '' }}>
                                        <label for="dst_customer">Customer</label>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Select Party <span class="text-danger">*</span></label>
                                <select name="destination_id" id="destination_id" class="form-select" required>
                                    <option value="">-- Select Party --</option>
                                    @if($voucher->destination_party_type == 'customer')
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}" {{ $voucher->destination_party_id == $c->id ? 'selected' : '' }}>{{ $c->customer_name }}</option>
                                        @endforeach
                                    @else
                                        @foreach($vendors as $v)
                                            <option value="{{ $v->id }}" {{ $voucher->destination_party_id == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control form-control-lg" step="0.01" min="1" value="{{ $voucher->amount }}" required placeholder="Enter amount">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="1" placeholder="Any additional notes...">{{ $voucher->remarks }}</textarea>
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

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script>
$(document).ready(function() {
    const customers = @json($customers);
    const vendors = @json($vendors);

    function updatePartyDropdown(typeVal, partySelectId, selectedId) {
        var $sel = $('#' + partySelectId);
        $sel.empty().append('<option value="">-- Select Party --</option>');
        var list = typeVal === 'customer' ? customers : vendors;
        var nameField = typeVal === 'customer' ? 'customer_name' : 'name';
        list.forEach(function(item) {
            var sel = selectedId && item.id == selectedId ? 'selected' : '';
            $sel.append('<option value="' + item.id + '" ' + sel + '>' + item[nameField] + '</option>');
        });
    }

    $(document).on('change', '.src-type', function() {
        updatePartyDropdown($(this).val(), 'source_id');
    });

    $(document).on('change', '.dst-type', function() {
        updatePartyDropdown($(this).val(), 'destination_id');
    });
});
</script>
@endsection
