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
                    <p class="text-muted">Give money to a Vendor or Customer (Balance will be adjusted)</p>
                    
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
                            <label>Select Party Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check me-3">
                                    <input class="form-check-input party-type-radio" type="radio" name="party_type" id="type_vendor" value="vendor" checked>
                                    <label class="form-check-label" for="type_vendor">Vendor</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input party-type-radio" type="radio" name="party_type" id="type_customer" value="customer">
                                    <label class="form-check-label" for="type_customer">Customer</label>
                                </div>
                            </div>
                        </div>

                        <div id="vendor_select_wrapper" class="form-group mb-3">
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
                                            <span class="badge {{ $payment->party_type == 'vendor' ? 'badge-primary' : 'badge-info' }}">
                                                {{ ucfirst($payment->party_type) }}
                                            </span>
                                        </td>
                                        <td>{{ $payment->party_name }}</td>
                                        <td class="text-danger font-weight-bold">{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->note ?? '-' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" style="gap:2px;">
                                                <button type="button" class="btn btn-outline-warning" title="Edit Voucher"
                                                    data-bs-toggle="modal" data-bs-target="#editModal{{ $payment->id }}{{ $payment->party_type }}">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <a href="{{ route('payment.out.print', ['id' => $payment->id, 'type' => $payment->party_type]) }}"
                                                    target="_blank" class="btn btn-outline-primary" title="Print Voucher">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-delete" title="Delete Voucher"
                                                    data-delete-url="{{ route('payment.out.delete', ['id' => $payment->id, 'type' => $payment->party_type]) }}"
                                                    data-delete-method="DELETE"
                                                    data-label="POUT-{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }} (Payment Out)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
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

@foreach($payments as $payment)
<div class="modal fade" id="editModal{{ $payment->id }}{{ $payment->party_type }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('payment.out.update', ['id' => $payment->id, 'type' => $payment->party_type]) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-header">
            <h5 class="modal-title">Edit Payment Out ({{ ucfirst($payment->party_type) }})</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="mb-2">
                  <label class="form-label">Payment Date</label>
                  <input type="date" name="payment_date" class="form-control" value="{{ $payment->payment_date }}" required>
              </div>
              <div class="mb-2">
                  <label class="form-label">Amount (PKR)</label>
                  <input type="number" name="amount" class="form-control" step="0.01" min="1" value="{{ $payment->amount }}" required>
              </div>
              <div class="mb-2">
                  <label class="form-label">Party</label>
                  <input type="text" class="form-control" value="{{ $payment->party_name }}" readonly disabled>
              </div>
              <div class="mb-2">
                  <label class="form-label">Remarks</label>
                  <input type="text" name="remarks" class="form-control" value="{{ $payment->note }}">
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning">Update Payment</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endforeach

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
            if ($(this).val() === 'vendor') {
                $('#vendor_select_wrapper').show();
                $('#customer_select_wrapper').hide();
                $('#vendor_select_wrapper select').attr('required', true);
                $('#customer_select_wrapper select').attr('required', false);
            } else {
                $('#vendor_select_wrapper').hide();
                $('#customer_select_wrapper').show();
                $('#vendor_select_wrapper select').attr('required', false);
                $('#customer_select_wrapper select').attr('required', true);
            }
        });
        
        if ($('#type_vendor').is(':checked')) {
            $('#vendor_select_wrapper select').attr('required', true);
        } else {
            $('#customer_select_wrapper select').attr('required', true);
        }

        // SweetAlert2 Delete
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var url = $(this).data('delete-url');
            var method = $(this).data('delete-method') || 'GET';
            var label = $(this).data('label') || 'this voucher';
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
                            Swal.fire({ icon: 'success', title: 'Deleted!', timer: 1500, showConfirmButton: false });
                            setTimeout(function() { location.reload(); }, 1500);
                        },
                        error: function(xhr) {
                            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to delete.' });
                        }
                    });
                } else {
                    window.location.href = url;
                }
            });
        });
    });
</script>
@endsection
@endsection
