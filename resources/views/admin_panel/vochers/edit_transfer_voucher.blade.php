@extends('admin_panel.layout.app')
@section('content')
<style>
    .card-header {
        background-color: #37a371;
        color: white;
    }
    .btn-submit {
        background-color: #37a371;
        color: white;
        font-weight: bold;
    }
    .btn-submit:hover {
        background-color: #2c8c5c;
        color: white;
    }
</style>

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-10 offset-md-1 grid-margin stretch-card">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 text-white">Edit Transfer Voucher</h4>
                    <a href="{{ route('transfer-vouchers.all') }}" class="btn btn-sm btn-light">Back to All Transfers</a>
                </div>
                <div class="card-body">
                    <p class="text-muted">Modify transfer details. Previous ledger impacts will be reversed and new ones applied.</p>
                    
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('transfer-vouchers.update', $voucher->id) }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold">Transfer Date</label>
                                <input type="date" name="transfer_date" class="form-control" value="{{ $voucher->transfer_date }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold">Voucher ID</label>
                                <input type="text" class="form-control" value="{{ $voucher->tvid }}" readonly>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <!-- Source Party -->
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-danger mb-3">SOURCE PARTY (Deduct From)</h6>
                                        <div class="mb-2">
                                            <label>Party Type</label>
                                            <select name="source_type" id="source_type" class="form-control" required>
                                                <option value="customer" {{ $voucher->source_party_type == 'customer' ? 'selected' : '' }}>Customer</option>
                                                <option value="vendor" {{ $voucher->source_party_type == 'vendor' ? 'selected' : '' }}>Vendor</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label>Select Party</label>
                                            <select name="source_id" id="source_id" class="form-control select2" required>
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
                            </div>

                            <!-- Destination Party -->
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-success mb-3">DESTINATION PARTY (Transfer To)</h6>
                                        <div class="mb-2">
                                            <label>Party Type</label>
                                            <select name="destination_type" id="destination_type" class="form-control" required>
                                                <option value="vendor" {{ $voucher->destination_party_type == 'vendor' ? 'selected' : '' }}>Vendor</option>
                                                <option value="customer" {{ $voucher->destination_party_type == 'customer' ? 'selected' : '' }}>Customer</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label>Select Party</label>
                                            <select name="destination_id" id="destination_id" class="form-control select2" required>
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
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold">Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control form-control-lg" step="0.01" min="1" value="{{ $voucher->amount }}" required placeholder="Enter transferred amount">
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="Any additional notes...">{{ $voucher->remarks }}</textarea>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-submit px-5 py-3 shadow-sm">Update Transfer Voucher</button>
                        </div>
                    </form>

                    <script>
                        $(document).ready(function() {
                            const customers = @json($customers);
                            const vendors = @json($vendors);

                            function updatePartyDropdown(typeSelectId, partySelectId, selectedId = null) {
                                const type = $(`#${typeSelectId}`).val();
                                const partySelect = $(`#${partySelectId}`);
                                partySelect.empty().append('<option value="">-- Select Party --</option>');
                                
                                if (type === 'customer') {
                                    customers.forEach(c => {
                                        const isSelected = (selectedId == c.id) ? 'selected' : '';
                                        partySelect.append(`<option value="${c.id}" ${isSelected}>${c.customer_name}</option>`);
                                    });
                                } else {
                                    vendors.forEach(v => {
                                        const isSelected = (selectedId == v.id) ? 'selected' : '';
                                        partySelect.append(`<option value="${v.id}" ${isSelected}>${v.name}</option>`);
                                    });
                                }
                            }

                            $('#source_type').change(function() {
                                updatePartyDropdown('source_type', 'source_id');
                            });

                            $('#destination_type').change(function() {
                                updatePartyDropdown('destination_type', 'destination_id');
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
