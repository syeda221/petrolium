@extends('admin_panel.layout.app')
@section('content')

<div class="content-wrapper">
    <div class="row">
        <!-- Add New Other Income -->
        <div class="col-md-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0 text-white">Other Income</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Record independent miscellaneous income.</p>
                    
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif

                    <form action="{{ route('other.income.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label>Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Income Source / Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Scrape sale, Return diff">
                        </div>

                        <div class="form-group mb-3">
                            <label>Deposit To Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check me-3">
                                    <input class="form-check-input party-type-radio" type="radio" name="party_type" id="type_account" value="account" checked>
                                    <label class="form-check-label" for="type_account">Account</label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input party-type-radio" type="radio" name="party_type" id="type_vendor" value="vendor">
                                    <label class="form-check-label" for="type_vendor">Vendor</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input party-type-radio" type="radio" name="party_type" id="type_customer" value="customer">
                                    <label class="form-check-label" for="type_customer">Customer</label>
                                </div>
                            </div>
                        </div>

                        <div id="account_select_wrapper" class="form-group mb-3">
                            <label>Deposit To (Account) <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-control">
                                <option value="">-- Choose Account --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ str_contains(strtolower($acc->title), 'cash') ? 'selected' : '' }}>
                                        {{ $acc->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="vendor_select_wrapper" class="form-group mb-3" style="display: none;">
                            <label>Select Vendor <span class="text-danger">*</span></label>
                            <select name="vendor_id" class="form-control select2" style="width: 100%;">
                                <option value="">-- Choose Vendor --</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="customer_select_wrapper" class="form-group mb-3" style="display: none;">
                            <label>Select Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-control select2" style="width: 100%;">
                                <option value="">-- Choose Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label>Amount (PKR) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="1" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional details">
                        </div>
                        <button type="submit" class="btn btn-info w-100 py-2">Save Other Income</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- History & Edit/Delete -->
        <div class="col-md-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Recent Other Income</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered text-center" id="datatable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Source</th>
                                    <th>Deposit To</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($incomes as $inc)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($inc->date)->format('d-m-Y') }}</td>
                                        <td>
                                            <strong>{{ $inc->title }}</strong><br>
                                            <small class="text-muted">{{ $inc->remarks }}</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $inc->party_type == 'account' ? 'badge-dark' : ($inc->party_type == 'vendor' ? 'badge-primary' : 'badge-info') }}">
                                                {{ ucfirst($inc->party_type) }}
                                            </span><br>
                                            {{ $inc->deposit_to }}
                                        </td>
                                        <td class="text-info font-weight-bold">{{ number_format($inc->amount, 2) }}</td>
                                        <td>
                                            <!-- Edit Button removed for simplicity as it needs complex logic for party change, or can be kept with minimal fields -->
                                            
                                            <!-- Delete Button -->
                                            <form action="{{ route('other.income.delete', $inc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this specific income? Ledger/Balance will be reverted.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#datatable')) {
            $('#datatable').DataTable().destroy();
        }
        $('#datatable').DataTable({
            "order": [[ 0, "desc" ]]
        });

        if ($.fn.select2) {
            $('.select2').select2();
        }

        $('.party-type-radio').on('change', function() {
            let val = $(this).val();
            $('#account_select_wrapper, #vendor_select_wrapper, #customer_select_wrapper').hide();
            $('#account_select_wrapper select, #vendor_select_wrapper select, #customer_select_wrapper select').attr('required', false);

            if (val === 'account') {
                $('#account_select_wrapper').show();
                $('#account_select_wrapper select').attr('required', true);
            } else if (val === 'vendor') {
                $('#vendor_select_wrapper').show();
                $('#vendor_select_wrapper select').attr('required', true);
            } else {
                $('#customer_select_wrapper').show();
                $('#customer_select_wrapper select').attr('required', true);
            }
        });

        // Initial required state
        $('#account_select_wrapper select').attr('required', true);
    });
</script>
@endsection
@endsection
