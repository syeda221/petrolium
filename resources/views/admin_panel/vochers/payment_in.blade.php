@extends('admin_panel.layout.app')
@section('content')

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0 text-white">Payment In (Receipt)</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Receive money from a Customer or Vendor (Balance will be adjusted)</p>
                    
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif

                    <form action="{{ route('payment.in.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label>Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Select Party Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check me-3">
                                    <input class="form-check-input party-type-radio" type="radio" name="party_type" id="type_customer" value="customer" checked>
                                    <label class="form-check-label" for="type_customer">Customer</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input party-type-radio" type="radio" name="party_type" id="type_vendor" value="vendor">
                                    <label class="form-check-label" for="type_vendor">Vendor</label>
                                </div>
                            </div>
                        </div>

                        <div id="customer_select_wrapper" class="form-group mb-3">
                            <label>Select Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-control select2" style="width: 100%;">
                                <option value="">-- Choose Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
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

                        <div class="form-group mb-3">
                            <label>Deposit To (Account) <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-control" required>
                                <option value="">-- Choose Account --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ str_contains(strtolower($acc->title), 'cash') ? 'selected' : '' }}>
                                        {{ $acc->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label>Amount Received <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="1" required placeholder="Enter amount">
                        </div>

                        <div class="form-group mb-3">
                            <label>Remarks / Notes</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional details">
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100 py-2">Save Payment In</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Recent Payments In</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered text-center" id="datatable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Party</th>
                                    <th>Amount</th>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>
                                        <td>
                                            <span class="badge {{ $payment->party_type == 'customer' ? 'badge-info' : 'badge-primary' }}">
                                                {{ ucfirst($payment->party_type) }}
                                            </span>
                                        </td>
                                        <td>{{ $payment->party_name }}</td>
                                        <td class="text-success font-weight-bold">{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->note ?? '-' }}</td>
                                        <td>
                                            <!-- Edit Button -->
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editModal{{ $payment->id }}{{ $payment->party_type }}">Edit</button>
                                            <!-- Delete Button -->
                                            <form action="{{ route('payment.in.delete', ['id' => $payment->id, 'type' => $payment->party_type]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payment? Ledger will be reverted.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal{{ $payment->id }}{{ $payment->party_type }}" tabindex="-1" role="dialog" aria-hidden="true">
                                      <div class="modal-dialog" role="document">
                                        <div class="modal-content text-left">
                                          <form action="{{ route('payment.in.update', ['id' => $payment->id, 'type' => $payment->party_type]) }}" method="POST">
                                              @csrf
                                              @method('PUT')
                                              <div class="modal-header">
                                                <h5 class="modal-title">Edit Payment In ({{ ucfirst($payment->party_type) }})</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                  <span aria-hidden="true">&times;</span>
                                                </button>
                                              </div>
                                              <div class="modal-body">
                                                  <div class="form-group mb-2">
                                                      <label>Payment Date</label>
                                                      <input type="date" name="payment_date" class="form-control" value="{{ $payment->payment_date }}" required>
                                                  </div>
                                                  <div class="form-group mb-2">
                                                      <label>Amount (PKR)</label>
                                                      <input type="number" name="amount" class="form-control" step="0.01" min="1" value="{{ $payment->amount }}" required>
                                                  </div>
                                                  <div class="form-group mb-2">
                                                      <label>Party</label>
                                                      <input type="text" class="form-control" value="{{ $payment->party_name }}" readonly disabled>
                                                  </div>
                                                  <div class="form-group mb-2">
                                                      <label>Remarks</label>
                                                      <input type="text" name="remarks" class="form-control" value="{{ $payment->note }}">
                                                  </div>
                                              </div>
                                              <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update Payment</button>
                                              </div>
                                          </form>
                                        </div>
                                      </div>
                                    </div>
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

        $('.party-type-radio').on('change', function() {
            if ($(this).val() === 'customer') {
                $('#customer_select_wrapper').show();
                $('#vendor_select_wrapper').hide();
                $('#customer_select_wrapper select').attr('required', true);
                $('#vendor_select_wrapper select').attr('required', false);
            } else {
                $('#customer_select_wrapper').hide();
                $('#vendor_select_wrapper').show();
                $('#customer_select_wrapper select').attr('required', false);
                $('#vendor_select_wrapper select').attr('required', true);
            }
        });
        
        // Initial state
        if ($('#type_customer').is(':checked')) {
            $('#customer_select_wrapper select').attr('required', true);
        } else {
            $('#vendor_select_wrapper select').attr('required', true);
        }
    });
</script>
@endsection
@endsection
