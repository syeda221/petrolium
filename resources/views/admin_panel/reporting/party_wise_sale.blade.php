@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <div class="page-header row mb-3">
                <div class="page-title col-lg-6">
                    <h4>Party Wise Sale Report</h4>
                    <h6>View details of which customer was sold which product</h6>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('report.party_wise_sale') }}" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Customer / Party</label>
                            <select name="customer_id" class="form-select select2" style="width: 100%;">
                                <option value="all">-- All Customers --</option>
                                @foreach($allCustomers as $cust)
                                    <option value="{{ $cust->id }}" {{ $customerId == $cust->id ? 'selected' : '' }}>
                                        {{ $cust->customer_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" id="btnExportCsv" class="btn btn-danger w-100">CSV</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="partySaleReportTable">
                            <thead class="bg-gray">
                                <tr>
                                    <th>#</th>
                                    <th>Customer / Party Name</th>
                                    <th>Product Item Name</th>
                                    <th class="text-end">Total Sold Qty</th>
                                    <th class="text-end">Total Sale Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 1;
                                    $gtQty = 0;
                                    $gtSales = 0;
                                @endphp
                                @forelse ($reportData as $customer => $products)
                                    @foreach ($products as $product => $data)
                                        @php
                                            $gtQty += $data['qty'];
                                            $gtSales += $data['total_amount'];
                                        @endphp
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $customer }}</td>
                                            <td>{{ $product }}</td>
                                            <td class="text-end">{{ number_format($data['qty'], 2) }}</td>
                                            <td class="text-end">{{ number_format($data['total_amount'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No Data Found.</td>
                                    </tr>
                                @endforelse
                                @if (count($reportData) > 0)
                                    <tr class="fw-bold bg-light">
                                        <td colspan="3" class="text-end">Grand Total:</td>
                                        <td class="text-end">{{ number_format($gtQty, 2) }}</td>
                                        <td class="text-end">{{ number_format($gtSales, 2) }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($('.select2').length > 0) {
            $('.select2').select2({ placeholder: "Search Customer..." });
        }

        $('#btnExportCsv').click(function() {
            let csv = [];
            let rows = document.querySelectorAll("#partySaleReportTable tr");
            
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll("td, th");
                
                for (let j = 0; j < cols.length; j++) 
                    row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
                
                csv.push(row.join(","));
            }

            let csvString = csv.join("\n");
            let blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            let link = document.createElement("a");
            if (link.download !== undefined) {
                let url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", "party_wise_sale_report.csv");
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
    });
</script>
@endsection
