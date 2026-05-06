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
        $('#datatable').DataTable();
    });
</script>
@endsection
@endsection
