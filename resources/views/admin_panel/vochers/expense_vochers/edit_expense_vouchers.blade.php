@extends('admin_panel.layout.app')
@section('content')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<div class="main-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="fw-bold mt-2">Edit Expense Voucher</h2>
            <a href="{{ route('all-expense-vochers') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
        <div class="card shadow">
            <div class="card-body">
                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <form action="{{ route('expense-vocher.update', $voucher->id) }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-1">
                            <label class="form-label fw-bold">EVID</label>
                            <input type="text" class="form-control" name="evid" value="{{ $voucher->evid }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Source Account (Cash/Bank)</label>
                            <select name="vendor_id" class="form-select" required>
                                <option disabled>Select Source</option>
                                @foreach($SourceAccounts as $acc)
                                    <option value="{{ $acc->id }}" data-code="{{ $acc->account_code }}" {{ $voucher->party_id == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->account_code }} - {{ $acc->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Account Code</label>
                            <input type="text" name="tel" id="tel" class="form-control" readonly value="{{ $voucher->partyAccount->account_code ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Remarks</label>
                            <input type="text" name="remarks" class="form-control" id="remarks" value="{{ $voucher->remarks }}">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle" id="voucherTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="60%">Expense Account</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $row)
                                <tr>
                                    <td>
                                        <select name="row_account_id[]" class="form-select rowAccountSub" required>
                                            <option value="">Select Expense Category</option>
                                            @foreach($ExpenseAccounts as $acc)
                                                <option value="{{ $acc->id }}" {{ $row['account_id'] == $acc->id ? 'selected' : '' }}>{{ $acc->title }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input name="amount[]" type="text" class="form-control text-end amount" placeholder="0.00" value="{{ $row['amount'] }}" required></td>
                                    <td><button type="button" class="btn btn-danger btn-sm removeRow"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th class="text-end">Total:</th>
                                    <th><input type="text" name="total_amount" class="form-control text-end fw-bold" id="totalAmount" value="{{ $voucher->total_amount }}" readonly></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="d-flex mt-4">
                        <button class="btn btn-success me-2">Update Voucher</button>
                        <a href="{{ route('all-expense-vochers') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        calculateTotals();
    });

    $(document).on('change', 'select[name="vendor_id"]', function() {
        let $selected = $(this).find(':selected');
        let code = $selected.data('code');
        $('#tel').val(code || '');
    });

    function calculateTotals() {
        let total = 0;
        $('#voucherTable tbody tr').each(function() {
            total += parseFloat($(this).find('.amount').val()) || 0;
        });
        $('#totalAmount').val(total.toFixed(2));
    }

    $(document).on('input', '.amount', function() {
        calculateTotals();
    });

    $(document).on('keypress', '.amount', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            addNewRow();
        }
    });

    function addNewRow() {
        let newRow = `<tr>
            <td>
                <select name="row_account_id[]" class="form-select rowAccountSub">
                    <option value="">Select Expense Category</option>
                    @foreach($ExpenseAccounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->title }}</option>
                    @endforeach
                </select>
            </td>
            <td><input name="amount[]" type="text" class="form-control text-end amount" placeholder="0.00"></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow"><i class="bi bi-trash"></i></button></td>
        </tr>`;
        $('#voucherTable tbody').append(newRow);
        $('#voucherTable tbody tr:last .rowAccountSub').focus();
    }

    $(document).on('click', '.removeRow', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });
</script>

@endsection
