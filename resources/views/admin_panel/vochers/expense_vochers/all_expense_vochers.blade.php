@extends('admin_panel.layout.app')
@section('content')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="main-content">
    <div class="container-fluid">
        <div class="card-header mt-2 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Expense Vouchers</h4>
            <a class="btn btn-primary" href="{{ route('expense-vochers') }}">Add Expense Voucher</a>
        </div>
        <div class="card shadow">
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <div class="table-responsive mt-4 mb-4">

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Voucher No</th>
                                <th>Source Account</th>
                                <th>Expense Categories</th>
                                <th>Remarks</th>
                                <th>Total Amount</th>
                                <th>Entry Date</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vouchers as $voucher)
                            <tr>
                                <td>{{ $voucher->id }}</td>
                                <td>{{ $voucher->evid }}</td>
                                <td>{{ $voucher->party_name }}</td>
                                <td><span class="badge bg-info text-dark">{{ $voucher->narration_text }}</span></td>
                                <td>{{ $voucher->remarks }}</td>
                                <td><strong>{{ number_format($voucher->total_amount, 2) }}</strong></td>
                                <td>{{ $voucher->entry_date }}</td>
                                <td>{{ $voucher->created_at->format('d-M-Y H:i') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" style="gap:2px;">
                                        <button class="btn btn-outline-primary view-details-btn" 
                                                title="View Details"
                                                data-evid="{{ $voucher->evid }}"
                                                data-source="{{ $voucher->party_name }}"
                                                data-remarks="{{ $voucher->remarks }}"
                                                data-date="{{ $voucher->entry_date ?? $voucher->created_at->format('d-M-Y H:i') }}"
                                                data-total="{{ number_format($voucher->total_amount, 2) }}"
                                                data-rows='@json($voucher->details_json)'>
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="{{ route('expense-vocher.edit', $voucher->id) }}"
                                            class="btn btn-outline-warning" title="Edit Voucher">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="{{ route('expenseVoucher.print', $voucher->id) }}"
                                            target="_blank" class="btn btn-outline-primary" title="Print Voucher">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-delete" title="Delete Voucher"
                                            data-delete-url="{{ route('expense-vocher.delete', $voucher->id) }}"
                                            data-delete-method="GET"
                                            data-label="{{ $voucher->evid }} (Expense)">
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

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-info-circle"></i> Voucher Details - <span id="modal-evid"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Source Account:</strong> <span id="modal-source"></span></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p><strong>Date:</strong> <span id="modal-date"></span></p>
                    </div>
                    <div class="col-md-12">
                        <p><strong>Remarks:</strong> <span id="modal-remarks"></span></p>
                    </div>
                </div>
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Expense Category</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="modal-rows-body">
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-end">Total:</th>
                            <th class="text-end" id="modal-total"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.view-details-btn').click(function() {
            const evid = $(this).data('evid');
            const source = $(this).data('source');
            const remarks = $(this).data('remarks');
            const date = $(this).data('date');
            const total = $(this).data('total');
            const rows = $(this).data('rows');

            $('#modal-evid').text(evid);
            $('#modal-source').text(source);
            $('#modal-remarks').text(remarks || '-');
            $('#modal-date').text(date);
            $('#modal-total').text(total);

            let rowsHtml = '';
            rows.forEach(row => {
                rowsHtml += `<tr>
                    <td>${row.category}</td>
                    <td class="text-end">${parseFloat(row.amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                </tr>`;
            });
            $('#modal-rows-body').html(rowsHtml);

            $('#detailsModal').modal('show');
        });

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