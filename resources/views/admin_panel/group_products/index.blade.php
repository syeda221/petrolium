@extends('admin_panel.layout.app')
@section('content')

<style>
    .group-products-table {
        font-size: 13px;
    }
    .group-products-table thead th {
        background-color: #37a371 !important;
        color: #fff !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        padding: 12px 10px;
    }
    .group-products-table tbody td {
        padding: 10px;
        vertical-align: middle;
        border-bottom: 1px solid #eee;
    }
    .group-products-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .status-badge {
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 20px;
    }
    .component-list {
        font-size: 12px;
        line-height: 1.6;
    }
</style>

<div class="container-fluid">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-boxes me-2" style="color: #37a371;"></i>Group Products
            </h5>
            <div class="d-flex gap-2">
                <a href="{{ route('group-products.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> Create Group Product
                </a>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="groupProductsTable" class="table group-products-table align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>Components</th>
                            <th>Quantity Produced</th>
                            <th>Current Stock</th>
                            <th>Total Cost</th>
                            <th>Sale Price</th>
                            <th>Profit/Unit</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groupProducts as $gp)
                        <tr>
                            <td><strong>{{ $gp->id }}</strong></td>
                            <td>
                                <div class="fw-semibold">{{ $gp->product_name }}</div>
                                @if($gp->description)
                                <small class="text-muted">{{ Str::limit($gp->description, 50) }}</small><br>
                                @endif
                                @if($gp->product)
                                <small class="text-success"><i class="fas fa-box"></i> Code: {{ optional($gp->product)->item_code }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="component-list">
                                    @foreach($gp->components as $comp)
                                        <div>• {{ optional($comp->product)->item_name }} 
                                            <span class="text-muted">({{ $comp->quantity_used }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>{{ $gp->quantity_produced }} bags</td>
                            <td>
                                <span class="badge {{ $gp->current_stock > 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $gp->current_stock }}
                                </span>
                            </td>
                            <td>Rs {{ number_format($gp->total_cost, 0) }}</td>
                            <td>Rs {{ number_format($gp->sale_price, 0) }}/unit</td>
                            <td>
                                @php
                                    $profit = $gp->sale_price - $gp->cost_per_unit;
                                @endphp
                                <span class="{{ $profit >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                                    Rs {{ number_format($profit, 0) }}
                                </span>
                            </td>
                            <td>
                                @if($gp->is_active)
                                    <span class="status-badge bg-success text-white">Active</span>
                                @else
                                    <span class="status-badge bg-secondary text-white">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('group-products.show', $gp->id) }}" class="btn btn-info btn-sm" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('group-products.toggle-status', $gp->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-warning btn-sm" title="Toggle Status">
                                            <i class="fas fa-{{ $gp->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    @if($gp->current_stock == 0)
                                    <form action="{{ route('group-products.destroy', $gp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this group product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#groupProductsTable').DataTable({
            responsive: true,
            pageLength: 15,
            order: [[0, 'desc']],
            language: {
                search: "",
                searchPlaceholder: "Search group products...",
            }
        });

        $('.dataTables_filter input').addClass('form-control form-control-sm');
        $('.dataTables_length select').addClass('form-select form-select-sm');
    });
</script>
@endsection
