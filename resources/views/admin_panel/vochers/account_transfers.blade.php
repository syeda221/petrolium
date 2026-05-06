@extends('admin_panel.layout.app')
@section('content')
<style>
    .card-header {
        background-color: #37a371;
        color: white;
    }
    .btn-submit {
        background-color: #37a371;
        color: white;
        font-weight: bold;
    }
    .btn-submit:hover {
        background-color: #2c8c5c;
        color: white;
    }
</style>

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-10 offset-md-1 grid-margin stretch-card">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 text-white">Create Account Transfer Payment</h4>
                    <a href="{{ route('account-transfers.all') }}" class="btn btn-sm btn-light">View All Transfers</a>
                </div>
                <div class="card-body">
                    <p class="text-muted">Transfer funds between internal accounts (e.g. MCB Bank to Cash, Cash to HBL, etc.)</p>
                    
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('account-transfers.store') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Transfer Date</label>
                                <input type="date" name="transfer_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label>Voucher ID</label>
                                <input type="text" class="form-control" value="{{ $nextAtvid }}" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Transfer From Account <span class="text-danger">*</span></label>
                                <select name="from_account_id" class="form-control" required>
                                    <option value="">-- Select Source Account --</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->title }} ({{ $acc->account_code }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Funds will be deducted from this account</small>
                            </div>
                            <div class="col-md-6">
                                <label>Transfer To Account <span class="text-danger">*</span></label>
                                <select name="to_account_id" class="form-control" required>
                                    <option value="">-- Select Target Account --</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->title }} ({{ $acc->account_code }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Funds will be added to this account</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="1" required placeholder="Enter transferred amount">
                            </div>
                            <div class="col-md-6">
                                <label>Remarks</label>
                                <input type="text" name="remarks" class="form-control" placeholder="Any additional notes...">
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-submit px-5 py-2">Transfer Funds</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
