@extends('admin_panel.layout.app')
@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Dual Parties (Customer & Vendor)</h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDualPartyModal">
                        + Add Customer & Vendor
                    </button>
                </div>
                <p class="text-muted">Parties listed here act as both Customers and Vendors. They won't appear in the regular Customer/Vendor lists.</p>
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Data Table for Listing -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Account Code</th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Address</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dualParties as $party)
                                <tr>
                                    <td>{{ $party->customer_id }}</td>
                                    <td>{{ $party->customer_name }}</td>
                                    <td>{{ $party->mobile }}</td>
                                    <td>{{ $party->address }}</td>
                                    <td>{{ $party->created_at ? $party->created_at->format('d M Y') : 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('dual.party.ledger', $party->id) }}" class="btn btn-sm btn-info">Ledger</a>
                                        <!-- Edit/Delete could be added later -->
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No Dual Parties found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Create Modal -->
                <div class="modal fade" id="createDualPartyModal" tabindex="-1" aria-labelledby="createDualPartyModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form action="{{ route('dual.party.store') }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="createDualPartyModalLabel">Add Customer & Vendor</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label><strong>Account Code:</strong></label>
                                            <input type="text" class="form-control" name="customer_id" readonly value="{{ $latestId }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label><strong>Name (Customer & Vendor):</strong></label>
                                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Mobile#:</label>
                                            <input type="text" class="form-control" name="mobile" value="{{ old('mobile') }}">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label>Opening Balance:</label>
                                            <input type="number" step="any" class="form-control" name="opening_balance" value="{{ old('opening_balance', 0) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Balance Type (Cr/Dr):</label>
                                            <select name="balance_type" class="form-control" required>
                                                <option value="dr" {{ old('balance_type') == 'dr' ? 'selected' : '' }}>Dr (Debit - Unsy lene hain / Customer)</option>
                                                <option value="cr" {{ old('balance_type') == 'cr' ? 'selected' : '' }}>Cr (Credit - Unko dene hain / Vendor)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label>Address:</label>
                                            <textarea rows="1" class="form-control" name="address">{{ old('address') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button class="btn btn-success" type="submit">Save Account</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
