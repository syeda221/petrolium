@extends('admin_panel.layout.app')
@section('content')

<style>
    div.dataTables_wrapper div.dataTables_length select {
        width: 75px !important
    }
    td.text-center {
        vertical-align: middle;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .card-header.bg-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
</style>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
        <div>
            <h5 class="mb-0 fw-bold"><i class="fas fa-box me-2"></i>Product List</h5>
            <small>Manage all products here</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('store') }}" class="btn btn-light btn-sm">
                <i class="fas fa-plus me-1"></i> Add Product
            </a>
            <a href="{{ url()->previous() }}" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body">
        @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Search -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="productSearch" class="form-control" placeholder="Search product...">
                </div>
            </div>
        </div>

        <!-- Product Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover" id="datatable" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Purchase Price</th>
                        <th>Sale Price</th>
                        <!-- <th>Stock</th> -->
                        <th>Alert Qty</th>
                        <th>Note</th>
                        <th style="width:120px" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="productTable">
                    @foreach ($products as $key => $product)
                    @php 
                        $shopStock = $product->shop_stock ?? ($product->stock->qty ?? 0);
                        $warehouseStock = $product->warehouse_stock ?? 0;
                        $total = ($product->total_stock ?? $shopStock + $warehouseStock);
                        $isLowStock = $total <= ($product->alert_quantity ?? 0);
                    @endphp
                    <tr @if($isLowStock) style="background-color: #ffe6e6; border-left: 5px solid #dc3545;" @endif>
                        <td>{{ $products->firstItem() + $key }}</td>
                        <td class="fw-bold">
                            @if($isLowStock)
                                <span class="badge bg-danger me-2" title="Low Stock Alert">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                            @endif
                            {{ $product->item_name }}</td>
                        <td>
                            <strong>{{ $product->category_relation->name ?? '-' }}</strong><br>
                            <small class="text-muted">{{ $product->sub_category_relation->name ?? '-' }}</small>
                        </td>
                        <td>{{ $product->unit_id ?? '-' }}</td>
                        <td>
                            <span class="text-primary fw-bold">Rs {{ number_format($product->wholesale_price ?? 0) }}</span>
                        </td>
                        <td>
                            @if($product->discountProduct)
                                @php
                                    $discount = $product->discountProduct;
                                    $discountedPrice = $discount->final_price;
                                @endphp
                                <span class="badge bg-danger mb-1">{{ $discount->discount_percentage }}% OFF</span><br>
                                <del class="text-muted small">Rs {{ number_format($product->price) }}</del><br>
                                <strong class="text-success">Rs {{ number_format($discountedPrice) }}</strong>
                            @else
                                <span class="text-success fw-bold">Rs {{ number_format($product->price ?? 0) }}</span>
                            @endif
                        </td>
                        <!-- <td>
                            @php 
                                $shopStock = $product->shop_stock ?? ($product->stock->qty ?? 0);
                                $warehouseStock = $product->warehouse_stock ?? 0;
                                $total = ($product->total_stock ?? $shopStock + $warehouseStock);
                            @endphp
                            @if($total <= ($product->alert_quantity ?? 0))
                                <div style="padding: 8px; background-color: #dc3545; border-radius: 4px; text-align: center;">
                                    <span class="badge bg-danger" style="background-color: #8b0000 !important; font-size: 12px;">
                                        <i class="fas fa-bell me-1"></i>{{ $total }} (Below Limit)
                                    </span>
                                </div>
                                <small class="text-muted d-block mt-1">Shop: {{ $shopStock }} | WH: {{ $warehouseStock }}</small>
                            @else
                                <span class="badge bg-success">{{ $total }}</span>
                                <small class="text-muted d-block mt-1">Shop: {{ $shopStock }} | WH: {{ $warehouseStock }}</small>
                            @endif
                        </td> -->
                        <td>
                            @if($product->alert_quantity)
                                <span class="badge bg-warning text-dark">Min: {{ $product->alert_quantity }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ Str::limit($product->note ?? '-', 30) }}</td>
                        <td class="text-center">
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="py-3" id="paginationLinks">
                {{ $products->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let searchTimer = null;

    // Search with debounce
    $('#productSearch').on('keyup', function() {
        clearTimeout(searchTimer);
        let query = $(this).val();

        searchTimer = setTimeout(() => {
            fetchProducts(query);
        }, 400);
    });

    // Pagination click
    $(document).on('click', '#paginationLinks a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        fetchProducts($('#productSearch').val(), url);
    });

    // Fetch products via AJAX
    function fetchProducts(search = '', url = null) {
        if (!url) {
            url = "{{ route('product') }}";
        }

        $.ajax({
            url: url,
            data: { search: search },
            success: function(res) {
                $('#productTable').html($(res).find('#productTable').html());
                $('#paginationLinks').html($(res).find('#paginationLinks').html());
            }
        });
    }
});
</script>
@endsection