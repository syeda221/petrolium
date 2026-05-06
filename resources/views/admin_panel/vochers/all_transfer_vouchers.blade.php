@extends('admin_panel.layout.app')
@section('content')

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 text-white">All Transfer Vouchers</h4>
                    <a href="{{ route('transfer-vouchers') }}" class="btn btn-sm btn-light">Create New Transfer</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered text-center mt-3" id="datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Voucher ID</th>
                                    <th>Date</th>
                                    <th>Source (From)</th>
                                    <th>Destination (To)</th>
                                    <th>Amount</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vouchers as $index => $voucher)
                                    @php
                                        // Determine Source Name
                                        $sourceName = 'N/A';
                                        if($voucher->source_party_type == 'customer') {
                                            $party = \App\Models\Customer::find($voucher->source_party_id);
                                            $sourceName = ($party ? $party->customer_name : 'Unknown') . ' (Cust)';
                                        } elseif($voucher->source_party_type == 'vendor') {
                                            $party = \App\Models\Vendor::find($voucher->source_party_id);
                                            $sourceName = ($party ? $party->name : 'Unknown') . ' (Vend)';
                                        } else {
                                            // Fallback for old records
                                            $party = \App\Models\Customer::find($voucher->customer_id);
                                            $sourceName = ($party ? $party->customer_name : 'N/A') . ' (Cust)';
                                        }

                                        // Determine Destination Name
                                        $destName = 'N/A';
                                        if($voucher->destination_party_type == 'customer') {
                                            $party = \App\Models\Customer::find($voucher->destination_party_id);
                                            $destName = ($party ? $party->customer_name : 'Unknown') . ' (Cust)';
                                        } elseif($voucher->destination_party_type == 'vendor') {
                                            $party = \App\Models\Vendor::find($voucher->destination_party_id);
                                            $destName = ($party ? $party->name : 'Unknown') . ' (Vend)';
                                        } else {
                                            // Fallback for old records
                                            $party = \App\Models\Vendor::find($voucher->vendor_id);
                                            $destName = ($party ? $party->name : 'N/A') . ' (Vend)';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $voucher->tvid }}</td>
                                        <td>{{ \Carbon\Carbon::parse($voucher->transfer_date)->format('d-m-Y') }}</td>
                                        <td class="text-danger fw-bold">{{ $sourceName }}</td>
                                        <td class="text-success fw-bold">{{ $destName }}</td>
                                        <td class="font-weight-bold text-dark">{{ number_format($voucher->amount, 2) }}</td>
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
