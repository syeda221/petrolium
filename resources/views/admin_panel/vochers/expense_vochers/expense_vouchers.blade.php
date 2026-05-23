@extends('admin_panel.layout.app')
@section('content')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<div class="main-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="fw-bold mt-2">Expense Voucher</h2>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-circle"></i> Add Expense Category
            </button>
        </div>
        <div class="card shadow">
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <form action="{{ route('expense.vochers.store') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-1">
                            <label class="form-label fw-bold">EVID</label>
                            <input type="text" class="form-control" name="evid" value="{{ $nextRvid }}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Entry Date</label>
                            <input type="date" name="entry_date" class="form-control" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Source Account (Cash/Bank)</label>
                            <select name="vendor_id" class="form-select" required>
                                <option disabled selected>Select Source</option>
                                @foreach($SourceAccounts as $acc)
                                    <option value="{{ $acc->id }}" data-code="{{ $acc->account_code }}" data-balance="{{ $acc->opening_balance }}">
                                        {{ $acc->account_code }} - {{ $acc->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Account Code</label>
                            <input type="text" name="tel" id="tel" class="form-control" readonly>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label fw-bold">Balance</label>
                            <input type="text" id="balance" class="form-control text-danger fw-bold" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Remarks</label>
                            <input type="text" name="remarks" class="form-control" id="remarks">
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
                                <tr>
                                    <td>
                                        <select name="row_account_id[]" class="form-select rowAccountSub" required>
                                            <option value="">Select Expense Category</option>
                                            @foreach($ExpenseAccounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->title }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input name="amount[]" type="text" class="form-control text-end amount" placeholder="0.00" required></td>
                                    <td><button type="button" class="btn btn-danger btn-sm removeRow"><i class="bi bi-trash"></i></button></td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th class="text-end">Total:</th>
                                    <th><input type="text" name="total_amount" class="form-control text-end fw-bold" id="totalAmount" readonly></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    {{-- Footer Buttons --}}
                    <div class="d-flex  mt-4">
                        <div>
                            <button class="btn btn-primary">Save</button>
                            <button class="btn btn-outline-secondary">Exit</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('expense.category.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header text-white" style="background: #37a371; border-color: #37a371;">
                    <h5 class="modal-title text-white" id="addCategoryModalLabel">New Expense Category</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Fuel Expense, Tea/Coffee">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn text-white" style="background: #37a371;">Save Category</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).on('change', 'select[name="vendor_id"]', function() {
        let $selected = $(this).find(':selected');
        let code = $selected.data('code');
        let balance = $selected.data('balance');
        $('#tel').val(code || '');
        $('#balance').val(balance !== undefined ? parseFloat(balance).toFixed(2) : '');
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