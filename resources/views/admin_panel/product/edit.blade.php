@extends('admin_panel.layout.app')

<style>
    .card-header.bg-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
</style>

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    
                    <!-- Page Header -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                        <h6 class="page-title mb-0">Edit Product</h6>
                        <a href="{{ route('product') }}" class="btn btn-sm btn-outline-primary">
                            <i class="la la-undo"></i> Back to Products
                        </a>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white py-2">
                            <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Product Information</h6>
                        </div>
                        <div class="card-body">
                            
                            @if (session()->has('success'))
                            <div class="alert alert-success">
                                <strong>Success!</strong> {{ session('success') }}.
                            </div>
                            @endif

                            @if ($errors->any())
                            <div class="alert alert-danger py-2 mb-3">
                                <strong>Validation Errors:</strong>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <form action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row g-3">
                                    
                                    <!-- Product Name -->
                                    <div class="col-sm-4">
                                        <label class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                                        <input type="text" name="product_name" class="form-control" value="{{ $product->item_name }}" required>
                                    </div>

                                    <!-- Category -->
                                    <div class="col-sm-4">
                                        <label class="form-label fw-bold">Category</label>
                                        <select name="category_id" id="category-dropdown" class="form-select">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ $category->id == $product->category_id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Sub Category -->
                                    <div class="col-sm-4">
                                        <label class="form-label fw-bold">Sub Category</label>
                                        <select name="sub_category_id" id="subcategory-dropdown" class="form-select">
                                            <option value="">Select Subcategory</option>
                                            @foreach ($subcategories as $subcategory)
                                            <option value="{{ $subcategory->id }}" {{ $subcategory->id == $product->sub_category_id ? 'selected' : '' }}>
                                                {{ $subcategory->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Unit -->
                                    <div class="col-sm-4">
                                        <label class="form-label fw-bold">Unit (UOM) <span class="text-danger">*</span></label>
                                        <select name="unit" class="form-select" required>
                                            <option value="" disabled>Select One</option>
                                            <option value="LITTER Ltr" {{ $product->unit_id == 'Ltr' ? 'selected' : '' }}>LITTER (Ltr)</option>
                                            <option value="ML" {{ $product->unit_id == 'ML' ? 'selected' : '' }}>ML</option>
                                        </select>
                                    </div>

                                    <!-- Stock -->
                                    <div class="col-sm-4">
                                        <label class="form-label fw-bold">Stock</label>
                                        <input type="number" name="Stock" class="form-control" value="{{ $product->initial_stock }}" step="0.01" min="0">
                                    </div>

                                    <!-- Alert Quantity -->
                                    <div class="col-sm-4">
                                        <label class="form-label fw-bold">Alert Quantity</label>
                                        <input type="number" name="alert_quantity" class="form-control" value="{{ $product->alert_quantity }}">
                                    </div>

                                    <!-- Purchase Price -->
                                    <div class="col-sm-4">
                                        <label class="form-label fw-bold">Purchase Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rs</span>
                                            <input type="number" name="wholesale_price" class="form-control" value="{{ $product->wholesale_price }}" step="0.01">
                                        </div>
                                    </div>

                                    <!-- Sale Price -->
                                    <div class="col-sm-4">
                                        <label class="form-label fw-bold">Sale Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rs</span>
                                            <input type="number" name="retail_price" class="form-control" value="{{ $product->price }}" step="0.01">
                                        </div>
                                    </div>

                                    <!-- Note -->
                                    <div class="col-sm-4">
                                        <label class="form-label fw-bold">Note</label>
                                        <textarea name="note" class="form-control" rows="1" placeholder="Optional notes...">{{ $product->note }}</textarea>
                                    </div>

                                </div>

                                <!-- Submit Button -->
                                <div class="card-footer bg-light py-3 mt-4">
                                    <button type="submit" class="btn btn-success px-4">
                                        <i class="fas fa-save me-2"></i>Update Product
                                    </button>
                                    <a href="{{ route('product') }}" class="btn btn-secondary px-4 ms-2">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Subcategory Fetch on Category Change
    $('#category-dropdown').on('change', function() {
        var categoryId = $(this).val();
        if (categoryId) {
            $.ajax({
                url: '/get-subcategories/' + categoryId,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    $('#subcategory-dropdown').empty();
                    $('#subcategory-dropdown').append('<option value="">Select Subcategory</option>');
                    $.each(data, function(key, value) {
                        $('#subcategory-dropdown').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            });
        } else {
            $('#subcategory-dropdown').empty();
            $('#subcategory-dropdown').append('<option value="">Select Subcategory</option>');
        }
    });
});
</script>
@endsection