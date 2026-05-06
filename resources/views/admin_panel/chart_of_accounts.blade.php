@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid mt-4">

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif


    <div class="mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">➕ Add New Account</button>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addHeadModal">➕ Add Head</button>
        <button class="btn btn-warning" type="button" data-toggle="collapse" data-target="#manageHeadsSection">⚙️ Manage Heads</button>
    </div>

    <div class="collapse mb-4" id="manageHeadsSection">
        <div class="card card-body">
            <h5>Manage Account Heads</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-secondary">
                        <tr>
                            <th>#</th>
                            <th>Head Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($heads as $key => $head)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ $head->name }}</td>
                            <td>
                                <button class="btn btn-sm btn-info edit-head-btn" data-id="{{ $head->id }}" data-name="{{ $head->name }}">✏️ Edit</button>
                                <a href="{{ route('coa.head.destroy', $head->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? This will fail if accounts are linked.')">🗑️ Delete</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Account Code</th>
                    <th>Expense Head</th>
                    <th>Account Title</th>
                    <th>Type</th>
                    <th>closing Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $key => $account)
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{ $account->account_code }}</td>
                    <td>{{ $account->head->name }}</td>
                    <td>{{ $account->title }}</td>
                    <td>{{ $account->type }}</td>
                    <td><strong class="text-danger"> {{ $account->opening_balance  }} </strong></td> <!-- Display debit amount -->
                    <td>
                        @if($account->status)
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info edit-account-btn" 
                                data-id="{{ $account->id }}"
                                data-head_id="{{ $account->head_id }}"
                                data-account_code="{{ $account->account_code }}"
                                data-title="{{ $account->title }}"
                                data-type="{{ $account->type }}"
                                data-opening_balance="{{ $account->opening_balance }}"
                                data-status="{{ $account->status }}">
                            ✏️ Edit
                        </button>
                        <a href="{{ route('coa.account.destroy', $account->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">🗑️ Delete</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('coa.account.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add New Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Select Head</label>
                    <select name="head_id" class="form-control" required>
                        <option value="">Select Head</option>
                        @foreach($heads as $head)
                        <option value="{{ $head->id }}">{{ $head->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Account Code</label>
                    <input type="text" name="account_code" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Account Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Account Type</label>
                    <select name="type" class="form-control" required>
                        <option value="Debit">Debit</option>
                        <option value="Credit">Credit</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Opening Balance</label>
                    <input type="number" step="0.01" name="opening_balance" class="form-control" value="0.00">
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" name="status" type="checkbox" value="on" checked>
                    <label class="form-check-label">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Add Account</button>
            </div>
        </form>
    </div>
</div>




<!-- Add Head Modal -->
<div class="modal fade" id="addHeadModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('coa.head.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Head</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Head Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-secondary">Add Head</button>
            </div>
        </form>
    </div>
</div>


<!-- Edit Account Modal -->
<div class="modal fade" id="editAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editAccountForm" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Edit Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Select Head</label>
                    <select name="head_id" id="edit_head_id" class="form-control" required>
                        <option value="">Select Head</option>
                        @foreach($heads as $head)
                        <option value="{{ $head->id }}">{{ $head->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Account Code</label>
                    <input type="text" name="account_code" id="edit_account_code" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Account Title</label>
                    <input type="text" name="title" id="edit_title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Account Type</label>
                    <select name="type" id="edit_type" class="form-control" required>
                        <option value="Debit">Debit</option>
                        <option value="Credit">Credit</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Opening Balance</label>
                    <input type="number" step="0.01" name="opening_balance" id="edit_opening_balance" class="form-control">
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" name="status" type="checkbox" id="edit_status" value="on">
                    <label class="form-check-label">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update Account</button>
            </div>
        </form>
    </div>
</div>




<!-- Edit Head Modal -->
<div class="modal fade" id="editHeadModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editHeadForm" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Edit Account Head</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Head Name</label>
                    <input type="text" name="name" id="edit_head_name" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update Head</button>
            </div>
        </form>
    </div>
</div>



@section('scripts')
<script>
    $(document).ready(function() {
        // Edit Account
        $('.edit-account-btn').click(function() {
            const id = $(this).data('id');
            const head_id = $(this).data('head_id');
            const account_code = $(this).data('account_code');
            const title = $(this).data('title');
            const type = $(this).data('type');
            const opening_balance = $(this).data('opening_balance');
            const status = $(this).data('status');

            $('#editAccountForm').attr('action', `/coa/account/update/${id}`);
            $('#edit_head_id').val(head_id);
            $('#edit_account_code').val(account_code);
            $('#edit_title').val(title);
            $('#edit_type').val(type);
            $('#edit_opening_balance').val(opening_balance);
            
            if (status == 1) {
                $('#edit_status').prop('checked', true);
            } else {
                $('#edit_status').prop('checked', false);
            }

            $('#editAccountModal').modal('show');
        });

        // Edit Head
        $('.edit-head-btn').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            $('#editHeadForm').attr('action', `/coa/head/update/${id}`);
            $('#edit_head_name').val(name);

            $('#editHeadModal').modal('show');
        });
    });
</script>
@endsection

@endsection