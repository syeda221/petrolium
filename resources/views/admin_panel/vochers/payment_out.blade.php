@extends('admin_panel.layout.app')
@section('content')

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0 text-white">Payment Out (Issue)</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Give money to a Vendor (Vendor balance will be reduced)</p>
                    
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif

                    <form action="{{ route('payment.out.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label>Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>Select Vendor <span class="text-danger">*</span></label>
                            <select name="vendor_id" class="form-control" required>
                                <option value="">-- Choose Vendor --</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label>Pay From (Account) <span class="text-danger">*</span></label>
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
                            <label>Amount Paid <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="1" required placeholder="Enter amount">
                        </div>

                        <div class="form-group mb-3">
                            <label>Remarks / Notes</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional details">
                        </div>
                        
                        <button type="submit" class="btn btn-danger w-100 py-2">Save Payment Out</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Recent Payments Out</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered text-center" id="datatable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Vendor</th>
                                    <th>Amount</th>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>
                                        <td>{{ $payment->vendor->name ?? 'Unknown' }}</td>
                                        <td class="text-danger font-weight-bold">{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->note ?? '-' }}</td>
                                        <td>
                                            <!-- Edit Button -->
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editModal{{ $payment->id }}">Edit</button>
                                            <!-- Delete Button -->
                                            <form action="{{ route('payment.out.delete', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payment? Vendor ledger will be reverted.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal{{ $payment->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                      <div class="modal-dialog" role="document">
                                        <div class="modal-content text-left">
                                          <form action="{{ route('payment.out.update', $payment->id) }}" method="POST">
                                              @csrf
                                              @method('PUT')
                                              <div class="modal-header">
                                                <h5 class="modal-title">Edit Payment Out</h5>
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
        $('#datatable').DataTable();
    });
</script>
@endsection
@endsection
