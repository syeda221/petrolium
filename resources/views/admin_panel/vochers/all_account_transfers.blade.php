@extends('admin_panel.layout.app')
@section('content')

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 text-white">All Account Transfers</h4>
                    <a href="{{ route('account-transfers') }}" class="btn btn-sm btn-light">Create New Transfer</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered text-center mt-3" id="datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Voucher ID</th>
                                    <th>Date</th>
                                    <th>From Account</th>
                                    <th>To Account</th>
                                    <th>Amount</th>
                                    <th>Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vouchers as $index => $voucher)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $voucher->atvid }}</td>
                                        <td>{{ \Carbon\Carbon::parse($voucher->transfer_date)->format('d-m-Y') }}</td>
                                        <td>
                                            @php
                                                $fromAccount = $voucher->fromAccount;
                                            @endphp
                                            {{ $fromAccount ? $fromAccount->title . ' (' . $fromAccount->account_code . ')' : 'Unknown' }}
                                        </td>
                                        <td>
                                            @php
                                                $toAccount = $voucher->toAccount;
                                            @endphp
                                            {{ $toAccount ? $toAccount->title . ' (' . $toAccount->account_code . ')' : 'Unknown' }}
                                        </td>
                                        <td class="font-weight-bold text-success">{{ number_format($voucher->amount, 2) }}</td>
                                        <td>{{ $voucher->remarks ?? '-' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" style="gap:2px;">
                                                <a href="{{ route('account-transfers.edit', $voucher->id) }}"
                                                   class="btn btn-outline-warning" title="Edit Voucher">
                                                   <i class="fas fa-pen"></i>
                                                </a>
                                                <a href="{{ route('account-transfers.print', $voucher->id) }}"
                                                   target="_blank" class="btn btn-outline-primary" title="Print Voucher">
                                                   <i class="fas fa-print"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-delete" title="Delete Voucher"
                                                    data-delete-url="{{ route('account-transfers.delete', $voucher->id) }}"
                                                    data-delete-method="DELETE"
                                                    data-label="{{ $voucher->atvid }} (Account Transfer)">
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

@section('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#datatable')) {
            $('#datatable').DataTable().destroy();
        }
        $('#datatable').DataTable();

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
