@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 mt-5">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title">Expense Report</h4>
                            <form action="{{ route('report.expense') }}" method="GET" class="mb-4">
                                <div class="row align-items-end">
                                    <div class="col-md-3">
                                        <label>Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="{{ $start }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label>End Date</label>
                                        <input type="date" name="end_date" class="form-control" value="{{ $end }}">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <button type="button" class="btn btn-success" onclick="window.print()">Print Report</button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered text-center">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Voucher #</th>
                                            <th>Source Account (Paid From)</th>
                                            <th>Expense Details (Categories)</th>
                                            <th>Total Amount</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $total = 0; @endphp
                                        @forelse($expenses as $v)
                                            <tr>
                                                <td>{{ $v->entry_date }}</td>
                                                <td>{{ $v->evid }}</td>
                                                <td>{{ $v->source_account }}</td>
                                                <td class="text-left"><small>{{ $v->category_details }}</small></td>
                                                <td class="font-weight-bold">Rs. {{ number_format($v->total_amount, 2) }}</td>
                                                <td>{{ $v->remarks }}</td>
                                            </tr>
                                            @php $total += $v->total_amount; @endphp
                                        @empty
                                            <tr>
                                                <td colspan="6">No expenses found for the selected period.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-dark text-white font-weight-bold">
                                        <tr>
                                            <td colspan="4" class="text-right">Grand Total:</td>
                                            <td>Rs. {{ number_format($total, 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .btn, form, header, .sidebar, .footer { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; }
        .card { border: none !important; }
        .table { width: 100% !important; border: 1px solid #000 !important; }
        th, td { border: 1px solid #000 !important; }
        @page { margin: 1cm; }
    }
</style>
@endsection
