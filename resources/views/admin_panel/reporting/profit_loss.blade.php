@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <div class="page-header row mb-3">
                <div class="page-title col-lg-12">
                    <h4>Profit & Loss Report</h4>
                    <h6>Overview of Earnings and Expenses</h6>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('report.profit_loss') }}" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Filter</label>
                            <select name="filter" class="form-select" onchange="this.form.submit()">
                                <option value="" {{ request('filter') == '' ? 'selected' : '' }}>Custom Range</option>
                                <option value="daily" {{ request('filter') == 'daily' ? 'selected' : '' }}>Today</option>
                                <option value="weekly" {{ request('filter') == 'weekly' ? 'selected' : '' }}>This Week</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" onclick="window.print()" class="btn btn-secondary w-100">Print</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <!-- Sales Summary Card -->
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5>Gross Sales Profit</h5>
                            <h2>Rs {{ number_format($totalSalesProfit, 0) }}</h2>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Total Revenue:</span>
                                <b>Rs {{ number_format($totalSalesAmount, 0) }}</b>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expenses Summary Card -->
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5>Business Expenses</h5>
                            <h2>Rs {{ number_format($totalExpenseAmount, 0) }}</h2>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Total Returns:</span>
                                <b>Rs {{ number_format($totalReturnAmount, 0) }}</b>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Net Profit Summary Card -->
                <div class="col-md-4">
                    <div class="card {{ $finalNetProfit >= 0 ? 'bg-primary' : 'bg-dark' }} text-white">
                        <div class="card-body">
                            <h5>Net Profit / Loss</h5>
                            <h2>Rs {{ number_format($finalNetProfit, 0) }}</h2>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Status:</span>
                                <b>{{ $finalNetProfit >= 0 ? 'PROFIT' : 'LOSS' }}</b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Detailed Calculation</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr class="table-dark">
                                <th>Category</th>
                                <th class="text-end">Amount</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Gross Item Profit</td>
                                <td class="text-end text-success">+{{ number_format($totalSalesProfit, 0) }}</td>
                                <td>(Sale Price - Cost Price) × Quantity for all items sold</td>
                            </tr>
                            <tr>
                                <td>Labour Charges Collected</td>
                                <td class="text-end text-success">+{{ number_format($totalLabourCharges, 0) }}</td>
                                <td>Additional charges added to bills</td>
                            </tr>
                            <tr>
                                <td>Extra Discounts Given</td>
                                <td class="text-end text-danger">-{{ number_format($totalExtraDiscount, 0) }}</td>
                                <td>Discounts deducted from total bill amounts</td>
                            </tr>
                            <tr>
                                <td>Sales Return Loss</td>
                                <td class="text-end text-danger">-{{ number_format($totalReturnLoss, 0) }}</td>
                                <td>Profit lost due to returned items</td>
                            </tr>
                            <tr class="fw-bold table-info">
                                <td>Net Sales Profit</td>
                                <td class="text-end text-primary">{{ number_format($netSalesProfit, 0) }}</td>
                                <td>Profit before business expenses</td>
                            </tr>
                            <tr>
                                <td>Business Expenses</td>
                                <td class="text-end text-danger">-{{ number_format($totalExpenseAmount, 0) }}</td>
                                <td>Total from Expense Vouchers (Rent, Utility, Salary, etc.)</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <th colspan="1" class="text-uppercase h4">Final Net Result</th>
                                <th class="text-end h4 {{ $finalNetProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rs {{ number_format($finalNetProfit, 0) }}
                                </th>
                                <th class="h5 {{ $finalNetProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $finalNetProfit >= 0 ? 'Overall Profit' : 'Overall Loss' }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Product-Wise Profit Summary</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped text-center">
                                <thead>
                                    <tr class="table-dark">
                                        <th class="text-start">Product Name</th>
                                        <th>Qty Sold (Net)</th>
                                        <th class="text-end">Total Profit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $grandProductProfit = 0; @endphp
                                    @if(isset($productWiseProfit) && count($productWiseProfit) > 0)
                                        @foreach($productWiseProfit as $pname => $data)
                                            @php $grandProductProfit += $data['profit']; @endphp
                                            <tr>
                                                <td class="text-start fw-bold">{{ $pname }}</td>
                                                <td>{{ $data['qty'] }}</td>
                                                <td class="text-end {{ $data['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $data['profit'] >= 0 ? '+' : '' }}{{ number_format($data['profit'], 0) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-muted">No product sales found in this period.</td>
                                        </tr>
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr class="table-active fw-bold">
                                        <td colspan="2" class="text-end">Total Gross Product Profit:</td>
                                        <td class="text-end text-success">+{{ number_format($grandProductProfit, 0) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-center text-muted">
                <p>Report Period: {{ date('d-M-Y', strtotime($startDate)) }} to {{ date('d-M-Y', strtotime($endDate)) }}</p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .main-content { margin: 0; padding: 0 !important; }
        .page-navigation, .rt_nav_header, .footer { display: none !important; }
        .btn, .form-label, select, input { display: none !important; }
        .card { border: 1px solid #000; box-shadow: none !important; }
        .bg-success, .bg-danger, .bg-primary, .bg-dark {
            background-color: transparent !important;
            color: #000 !important;
            border: 2px solid #000 !important;
        }
        .text-white { color: #000 !important; }
        .table-dark { background-color: #eee !important; color: #000 !important; }
    }
</style>
@endsection
