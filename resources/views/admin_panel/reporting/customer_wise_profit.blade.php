@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <div class="page-header row mb-3">
                <div class="page-title col-lg-6">
                    <h4>Customer Wise Profit and Sale Report</h4>
                    <h6>View Total Sales and Profit by Customer</h6>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('report.customer_wise_profit') }}" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" id="btnExportCsv" class="btn btn-danger">Export CSV</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="profitReportTable">
                            <thead class="bg-gray">
                                <tr>
                                    <th>#</th>
                                    <th>Customer Name</th>
                                    <th class="text-end">Total Items Qty</th>
                                    <th class="text-end">Total Net Sales</th>
                                    <th class="text-end">Total Net Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 1;
                                    $gtSales = 0;
                                    $gtProfit = 0;
                                    $gtQty = 0;
                                @endphp
                                @forelse ($customerWiseData as $customer => $data)
                                    @php
                                        $gtSales += $data['total_sales'];
                                        $gtProfit += $data['total_profit'];
                                        $gtQty += $data['total_items_qty'];
                                    @endphp
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $customer }}</td>
                                        <td class="text-end">{{ number_format($data['total_items_qty'], 2) }}</td>
                                        <td class="text-end">{{ number_format($data['total_sales'], 2) }}</td>
                                        <td class="text-end {{ $data['total_profit'] < 0 ? 'text-danger' : 'text-success' }}">
                                            <strong>{{ number_format($data['total_profit'], 2) }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No Data Found.</td>
                                    </tr>
                                @endforelse
                                @if (count($customerWiseData) > 0)
                                    <tr class="fw-bold bg-light">
                                        <td colspan="2" class="text-end">Grand Total:</td>
                                        <td class="text-end">{{ number_format($gtQty, 2) }}</td>
                                        <td class="text-end">{{ number_format($gtSales, 2) }}</td>
                                        <td class="text-end {{ $gtProfit < 0 ? 'text-danger' : 'text-success' }}">
                                            <strong>{{ number_format($gtProfit, 2) }}</strong>
                                        </td>
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
        $('#btnExportCsv').click(function() {
            let csv = [];
            let rows = document.querySelectorAll("#profitReportTable tr");
            
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
                link.setAttribute("download", "customer_wise_profit_report.csv");
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } else {
                alert('CSV download not supported in this browser.');
            }
        });
    });
</script>
@endsection
