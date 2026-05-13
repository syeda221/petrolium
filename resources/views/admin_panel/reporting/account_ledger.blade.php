@extends('admin_panel.layout.app')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 text-white">Account Ledger: {{ $account->title }}</h4>
                    <div>
                        <button onclick="window.print()" class="btn btn-sm btn-light no-print">🖨️ Print / Download PDF</button>
                        <a href="{{ route('view_all') }}" class="btn btn-sm btn-outline-light no-print">Back to COA</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light">
                                <h6>Account Details</h6>
                                <p class="mb-1"><strong>Code:</strong> {{ $account->account_code }}</p>
                                <p class="mb-1"><strong>Head:</strong> {{ $account->head->name ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Type:</strong> {{ $account->type }}</p>
                            </div>
                        </div>
                        <div class="col-md-8 text-end">
                             <div class="d-inline-block p-3 border rounded bg-success text-white">
                                <h5 class="mb-0">Current Balance</h5>
                                <h3 class="mb-0">{{ number_format($account->opening_balance, 2) }}</h3>
                             </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="ledgerTable">
                            <thead class="table-secondary text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th class="text-success">Money IN (+)</th>
                                    <th class="text-danger">Money OUT (-)</th>
                                    <th>Running Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $runningBalance = 0; 
                                    $totalIn = 0;
                                    $totalOut = 0;
                                @endphp
                                @foreach($transactions as $index => $t)
                                    @php
                                        $in = (float)($t['in'] ?? 0);
                                        $out = (float)($t['out'] ?? 0);
                                        $runningBalance = $runningBalance + $in - $out;
                                        $totalIn += $in;
                                        $totalOut += $out;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($t['date'])->format('d-m-Y') }}</td>
                                        <td>{{ $t['description'] }}</td>
                                        <td class="text-center text-success fw-bold">{{ $in > 0 ? number_format($in, 2) : '-' }}</td>
                                        <td class="text-center text-danger fw-bold">{{ $out > 0 ? number_format($out, 2) : '-' }}</td>
                                        <td class="text-center fw-bold">{{ number_format($runningBalance, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-dark">
                                <tr>
                                    <th colspan="3" class="text-end">TOTALS</th>
                                    <th class="text-center text-success">{{ number_format($totalIn, 2) }}</th>
                                    <th class="text-center text-danger">{{ number_format($totalOut, 2) }}</th>
                                    <th class="text-center">{{ number_format($runningBalance, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print {
            display: none !important;
        }
        .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
        }
        .card {
            border: none !important;
        }
    }
</style>
@endsection
