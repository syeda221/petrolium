@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header row mb-4 align-items-center">
                <div class="col-lg-6">
                    <h4 class="fw-bold text-dark mb-1">📒 Daily Cash Book</h4>
                    <p class="text-muted small mb-0">Track cash in, cash out, and carry forward balances</p>
                </div>
                <div class="col-lg-6 text-lg-end mt-3 mt-lg-0">
                    <form action="{{ route('report.cash_book') }}" method="GET" class="d-inline-flex gap-2">
                        <input type="date" name="date" class="form-control form-control-sm border-dark shadow-sm" value="{{ $selectedDate }}" onchange="this.form.submit()">
                    </form>
                    <button onclick="window.print()" class="btn btn-sm btn-outline-dark ms-2 shadow-sm">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>

            <!-- Top Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-primary text-white h-100">
                        <div class="card-body py-3">
                            <h6 class="small text-uppercase opacity-75 mb-2">Opening Balance</h6>
                            @if(!$dayClosed)
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-transparent text-white border-white">₨</span>
                                    <input type="number" step="0.01" id="manual_opening" class="form-control bg-transparent text-white border-white fw-bold fs-4" 
                                           style="outline: none; box-shadow: none;" value="{{ $opening }}">
                                </div>
                                <small class="opacity-75">You can edit this manually</small>
                            @else
                                <h3 class="fw-bold mb-0">₨ {{ number_format($opening, 2) }}</h3>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-success text-white h-100">
                        <div class="card-body py-3">
                            <h6 class="small text-uppercase opacity-75 mb-2">Total Receipts (+)</h6>
                            <h3 class="fw-bold mb-0">₨ {{ number_format($totalIn, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-danger text-white h-100">
                        <div class="card-body py-3">
                            <h6 class="small text-uppercase opacity-75 mb-2">Total Payments (-)</h6>
                            <h3 class="fw-bold mb-0">₨ {{ number_format($totalOut, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-dark text-white h-100">
                        <div class="card-body py-3">
                            <h6 class="small text-uppercase opacity-75 mb-2">Closing Balance</h6>
                            <h2 class="fw-bold mb-0">₨ {{ number_format($closing, 2) }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Column 1: Cash In (Receipts) -->
                <div class="col-lg-4">
                    <div class="card border-dark shadow-sm h-100">
                        <div class="card-header bg-success text-white py-3">
                            <h5 class="card-title mb-0 fs-6">🟢 CASH RECEIPTS (IN)</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="font-size: 13px;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($allReceipts as $r)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $r['title'] }}</div>
                                                <div class="text-muted small">{{ $r['ref'] }} | {{ $r['party'] }}</div>
                                            </td>
                                            <td class="text-end fw-bold text-success">
                                                {{ number_format($r['amount'], 2) }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-4 text-muted">No receipts found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-light fw-bold">
                                        <tr>
                                            <td>TOTAL RECEIPTS</td>
                                            <td class="text-end text-success fs-6">₨ {{ number_format($totalIn, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Cash Out (Payments) -->
                <div class="col-lg-4">
                    <div class="card border-dark shadow-sm h-100">
                        <div class="card-header bg-danger text-white py-3">
                            <h5 class="card-title mb-0 fs-6">🔴 CASH PAYMENTS (OUT)</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="font-size: 13px;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($allPayments as $p)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $p['title'] }}</div>
                                                <div class="text-muted small">{{ $p['ref'] }} | {{ $p['party'] }}</div>
                                            </td>
                                            <td class="text-end fw-bold text-danger">
                                                {{ number_format($p['amount'], 2) }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-4 text-muted">No payments found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-light fw-bold">
                                        <tr>
                                            <td>TOTAL PAYMENTS</td>
                                            <td class="text-end text-danger fs-6">₨ {{ number_format($totalOut, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Summary & Closing -->
                <div class="col-lg-4">
                    <div class="card border-dark shadow-sm mb-4">
                        <div class="card-header bg-dark text-white py-3 text-center">
                            <h5 class="card-title mb-0 fs-6">📊 FINAL SUMMARY</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush mb-4">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="fw-bold text-muted">Opening Balance:</span>
                                    <span class="fs-6" id="summary_opening">₨ {{ number_format($opening, 2) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="fw-bold text-success">Total Cash In:</span>
                                    <span class="fs-6">+ ₨ {{ number_format($totalIn, 2) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="fw-bold text-danger">Total Cash Out:</span>
                                    <span class="fs-6">- ₨ {{ number_format($totalOut, 2) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-top-0 pt-3">
                                    <span class="fw-bolder fs-5 text-dark">Closing Cash:</span>
                                    <span class="fw-bolder fs-5 text-dark" id="summary_closing">₨ {{ number_format($closing, 2) }}</span>
                                </li>
                            </ul>

                            @if($dayClosed)
                            <div class="alert alert-success text-center border-2 py-3 mb-0 shadow-sm animate__animated animate__fadeIn">
                                <i class="fas fa-check-circle me-1"></i>
                                <strong>DAY CLOSED</strong><br>
                                <span class="small">The balance has been carried forward to the next day.</span>
                            </div>
                            @else
                            <form action="{{ route('report.cash_book.close') }}" method="POST" id="closeDayForm">
                                @csrf
                                <input type="hidden" name="date" value="{{ $selectedDate }}">
                                <input type="hidden" name="opening" id="form_opening" value="{{ $opening }}">
                                <input type="hidden" name="total_in" value="{{ $totalIn }}">
                                <input type="hidden" name="total_out" value="{{ $totalOut }}">
                                <button type="submit" class="btn btn-dark w-100 py-3 fw-bold text-uppercase shadow pulse-btn" 
                                        onclick="return confirm('Are you sure you want to CLOSE the day? This will carry the balance forward as Opening Balance for tomorrow.')">
                                    <i class="fas fa-lock me-2"></i> Close Day & Carry Forward
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    <div class="card border-info shadow-sm bg-light">
                        <div class="card-body py-3 small">
                            <i class="fas fa-info-circle text-info me-1"></i>
                            <strong>Help:</strong> Closing the day saves the final balance as the <strong>Opening Balance</strong> for the following day. This ensures continuous cash tracking.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const manualOpening = document.getElementById('manual_opening');
        const summaryOpening = document.getElementById('summary_opening');
        const summaryClosing = document.getElementById('summary_closing');
        const formOpening = document.getElementById('form_opening');
        const cardClosing = document.querySelector('.bg-dark h2');
        
        const totalIn = {{ $totalIn }};
        const totalOut = {{ $totalOut }};

        if(manualOpening) {
            manualOpening.addEventListener('input', function() {
                const opening = parseFloat(this.value) || 0;
                const closing = opening + totalIn - totalOut;
                
                // Update Summary Section
                summaryOpening.innerText = '₨ ' + opening.toLocaleString(undefined, {minimumFractionDigits: 2});
                summaryClosing.innerText = '₨ ' + closing.toLocaleString(undefined, {minimumFractionDigits: 2});
                
                // Update Top Cards
                cardClosing.innerText = '₨ ' + closing.toLocaleString(undefined, {minimumFractionDigits: 2});
                
                // Update Hidden Form
                formOpening.value = opening;
            });
        }
    });
</script>

<style>
    @media print {
        .main-content { padding: 0 !important; margin: 0 !important; }
        .btn, .mt-lg-0, .alert-success span, .card-footer { display: none !important; }
        .card { border: 1px solid #000 !important; box-shadow: none !important; }
        .bg-success, .bg-danger, .bg-dark, .bg-primary { background-color: #f8f9fa !important; color: #000 !important; }
        .text-white { color: #000 !important; }
        .page-header h4 { font-size: 24px; text-align: center; width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; }
    }
    
    .pulse-btn {
        animation: pulse-black 2s infinite;
    }

    @keyframes pulse-black {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(0, 0, 0, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(0, 0, 0, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(0, 0, 0, 0); }
    }
</style>
@endsection
