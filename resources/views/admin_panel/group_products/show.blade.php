@extends('admin_panel.layout.app')
@section('content')

<style>
    .detail-card {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
    }
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .detail-row:last-child {
        border-bottom: none;
    }
    .detail-label {
        font-weight: 600;
        color: #555;
    }
    .detail-value {
        color: #333;
    }
    .component-table {
        font-size: 13px;
    }
    .component-table thead {
        background: #37a371;
        color: #fff;
    }
</style>

<div class="container-fluid">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-boxes me-2" style="color: #37a371;"></i>Group Product Details
            </h5>
            <a href="{{ route('group-products.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <div class="card-body">
            <div class="row">
                <!-- Product Information -->
                <div class="col-md-6">
                    <div class="detail-card">
                        <h6 class="fw-bold mb-3 text-success">Product Information</h6>
                        <div class="detail-row">
                            <span class="detail-label">Product Name:</span>
                            <span class="detail-value">{{ $groupProduct->product_name }}</span>
                        </div>
                        @if($groupProduct->description)
                        <div class="detail-row">
                            <span class="detail-label">Description:</span>
                            <span class="detail-value">{{ $groupProduct->description }}</span>
                        </div>
                        @endif
                        @if($groupProduct->product)
                        <div class="detail-row">
                            <span class="detail-label">Product Code (for Sales):</span>
                            <span class="detail-value text-success fw-bold">{{ optional($groupProduct->product)->item_code }}</span>
                        </div>
                        @endif
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value">
                                <span class="badge {{ $groupProduct->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $groupProduct->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Created By:</span>
                            <span class="detail-value">{{ optional($groupProduct->creator)->name ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Created At:</span>
                            <span class="detail-value">{{ $groupProduct->created_at->format('d M, Y h:i A') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Stock & Pricing Information -->
                <div class="col-md-6">
                    <div class="detail-card">
                        <h6 class="fw-bold mb-3 text-success">Stock & Pricing</h6>
                        <div class="detail-row">
                            <span class="detail-label">Quantity Produced:</span>
                            <span class="detail-value fw-bold">{{ $groupProduct->quantity_produced }} bags</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Current Stock:</span>
                            <span class="detail-value">
                                <span class="badge {{ $groupProduct->current_stock > 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $groupProduct->current_stock }} bags
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Cost:</span>
                            <span class="detail-value text-danger fw-bold">Rs {{ number_format($groupProduct->total_cost, 2) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Cost Per Unit:</span>
                            <span class="detail-value">Rs {{ number_format($groupProduct->cost_per_unit, 2) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Sale Price:</span>
                            <span class="detail-value text-success fw-bold">Rs {{ number_format($groupProduct->sale_price, 2) }}/unit</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Profit Per Unit:</span>
                            @php
                                $profit = $groupProduct->sale_price - $groupProduct->cost_per_unit;
                            @endphp
                            <span class="detail-value {{ $profit >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                Rs {{ number_format($profit, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Components Used -->
            <div class="detail-card mt-3">
                <h6 class="fw-bold mb-3 text-success">Components Used</h6>
                <div class="table-responsive">
                    <table class="table component-table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th class="text-center">Quantity Used</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupProduct->components as $index => $component)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ optional($component->product)->item_name ?? 'N/A' }}</td>
                                <td class="text-center">{{ $component->quantity_used }}</td>
                                <td class="text-end">Rs {{ number_format($component->unit_cost, 2) }}</td>
                                <td class="text-end fw-bold">Rs {{ number_format($component->total_cost, 2) }}</td>
                            </tr>
                            @endforeach
                            <tr class="table-active">
                                <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                <td class="text-end fw-bold text-success">Rs {{ number_format($groupProduct->total_cost, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
