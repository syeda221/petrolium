@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <div class="page-header row align-items-center mb-3">
                <div class="col-lg-6">
                    <h4>Dual Party Unified Ledger</h4>
                    <h6>{{ $customer->customer_name }} ({{ $customer->customer_id }})</h6>
                </div>
                <div class="col-lg-6 text-end">
                    <button onclick="window.print()" class="btn btn-secondary">Print Ledger</button>
                    <a href="{{ route('dual.party.create') }}" class="btn btn-primary">Back</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <span class="text-muted d-block">Starting Balance</span>
                                <strong class="fs-4 {{ $opening_balance < 0 ? 'text-danger' : 'text-success' }}">
                                    Rs. {{ number_format(abs($opening_balance), 2) }} 
                                    ({{ $opening_balance < 0 ? 'Cr' : 'Dr' }})
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <span class="text-muted d-block">Current Balance</span>
                                <strong class="fs-4 {{ $closing_balance < 0 ? 'text-danger' : 'text-success' }}">
                                    Rs. {{ number_format(abs($closing_balance), 2) }} 
                                    ({{ $closing_balance < 0 ? 'Cr - You Owe Them' : 'Dr - They Owe You' }})
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Ref/Invoice</th>
                                    <th>Description</th>
                                    <th class="text-end">Dr (They Owe Us)</th>
                                    <th class="text-end">Cr (We Owe Them)</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-light">
                                    <td colspan="3"><strong>Opening Balance</strong></td>
                                    <td class="text-end">{{ $opening_balance > 0 ? number_format($opening_balance, 2) : '-' }}</td>
                                    <td class="text-end">{{ $opening_balance < 0 ? number_format(abs($opening_balance), 2) : '-' }}</td>
                                    <td class="text-end fw-bold {{ $opening_balance < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format(abs($opening_balance), 2) }} {{ $opening_balance < 0 ? 'Cr' : 'Dr' }}
                                    </td>
                                </tr>
                                @foreach($transactions as $t)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($t['date'])->format('d-m-Y H:i A') }}</td>
                                    <td>{{ $t['invoice'] ?? '-' }}</td>
                                    <td>{{ $t['description'] }}</td>
                                    <td class="text-end">{{ $t['debit'] > 0 ? number_format($t['debit'], 2) : '-' }}</td>
                                    <td class="text-end">{{ $t['credit'] > 0 ? number_format($t['credit'], 2) : '-' }}</td>
                                    <td class="text-end fw-bold {{ $t['balance'] < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format(abs($t['balance']), 2) }} {{ $t['balance'] < 0 ? 'Cr' : 'Dr' }}
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
@endsection
