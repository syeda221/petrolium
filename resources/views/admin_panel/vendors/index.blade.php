@extends('admin_panel.layout.app')

@section('content')

<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">

            <div class="page-header row">
                <div class="page-title col-lg-6">
                    <h4>Vendor List</h4>
                </div>
                <div class="page-btn d-flex justify-content-end col-lg-6">
                    <button class="btn btn-outline-primary mb-2" data-bs-toggle="modal" data-bs-target="#vendorModal" onclick="clearVendor()">Add Vendor</button>
                    <a href="{{ url('vendors-ledger') }}" class="btn btn-sm btn-danger ms-2 mb-2">Ledger</a>
                    <a href="{{ route('vendor.payments') }}" class="btn btn-sm btn-danger ms-2 mb-2">Payments</a>
                    <a href="{{ url('vendor/bilties') }}" class="btn btn-sm btn-danger ms-2 mb-2">Bilty</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success"><strong>Success!</strong> {{ session('success') }}</div>
                    @endif

                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Opening Balance</th>
                                <th>Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendors as $key => $v)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $v->name }}</td>
                                <td>{{ $v->phone }}</td>
                                <td>{{ $v->opening_balance }}</td>
                                <td>{{ $v->address }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-edit-vendor" data-id="{{ $v->id }}">Edit</button>
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

<!-- Modal for Add/Edit Vendor -->
<div class="modal fade" id="vendorModal">
    <div class="modal-dialog">
        <form action="{{ url('vendor/store') }}" method="POST">@csrf
            <input type="hidden" id="vendor_id" name="id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add/Edit Vendor</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <input class="form-control" name="name" id="vname" placeholder="Name" required>
                    </div>
                    <div class="mb-2">
                        <input class="form-control" name="opening_balance" id="opening_balance" placeholder="Opening Balance" required>
                    </div>
                    <div class="mb-2">
                        <input class="form-control" name="phone" id="vphone" placeholder="Phone">
                    </div>
                    <div class="mb-2">
                        <textarea class="form-control" name="address" id="vaddress" placeholder="Address"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('.datanew').DataTable();

        // Clear modal fields
        window.clearVendor = function() {
            $('#vendor_id').val('');
            $('#vname').val('');
            $('#opening_balance').val('').prop('readonly', false);
            $('#vphone').val('');
            $('#vaddress').val('');
        };

        // ✅ Use event delegation for dynamically generated buttons
        $(document).on('click', '.btn-edit-vendor', function() {
            var row = $(this).closest('tr');
            var id = $(this).data('id');
            var name = row.find('td:eq(1)').text().trim();
            var phone = row.find('td:eq(2)').text().trim();
            var balance = row.find('td:eq(3)').text().trim();
            var address = row.find('td:eq(4)').text().trim();

            // Populate modal
            $('#vendor_id').val(id);
            $('#vname').val(name);
            $('#vphone').val(phone);
            $('#opening_balance').val(balance).prop('readonly', true);
            $('#vaddress').val(address);

            // Show modal
            var modal = new bootstrap.Modal(document.getElementById('vendorModal'));
            modal.show();
        });
    });
</script>

@endsection