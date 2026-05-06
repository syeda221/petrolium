<!-- meta tags and other links -->

@extends('admin_panel.layout.app')
@section('content')

    <style>
        .card-header.bg-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }
    </style>

    <!-- navbar-wrapper end -->
    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid">
                <div class="body-wrapper">

                    <div class="bodywrapper__inner">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                            <!-- Left: Page Title -->
                            <h6 class="page-title mb-0">Add Product</h6>

                            <!-- Center: Buttons -->
                            <div class="d-flex justify-content-center flex-wrap gap-2 flex-grow-1">
                                <!-- Category Button -->
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#categoryModal">
                                    <i class="la la-plus-circle"></i> Add Category
                                </button>

                                <!-- Subcategory Button -->
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#subcategoryModal">
                                    <i class="las la-plus"></i> Add Subcategory
                                </button>

                                <button type="button" class="btn btn-sm btn-outline-primary cuModalBtn"
                                    data-modal_title="Add New Brand" data-bs-toggle="modal" data-bs-target="#cuModal">
                                    <i class="las la-plus"></i> Add Brand
                                </button>

                                <a class="btn btn-md btn-outline-primary py-2" href="{{ url('/home') }}">
                                    <i class="la la-tachometer-alt"></i> Go To Dashboard
                                </a>
                            </div>
                            <!-- Right: Back Button -->
                            <div class="d-flex">
                                <a href="{{ route('product') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="la la-undo"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="row mb-none-30">
                            <div class="col-lg-12 col-md-12 mb-30">
                                <div class="card">
                                    <div class="card-body">
                                        @if (session()->has('success'))
                                            <div class="alert alert-success">
                                                <strong>Success!</strong> {{ session('success') }}.
                                            </div>
                                        @endif

                                        <form action="{{ route('store-product') }}" method="POST"
                                            enctype="multipart/form-data" id="productForm">
                                            @csrf

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

                                            <div class="card shadow-sm border-0">
                                                <div class="card-header bg-primary text-white py-2">
                                                    <h6 class="mb-0"><i class="fas fa-box me-2"></i>Product Information</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row g-3">

                                                        <div class="col-sm-4">
                                                            <label class="form-label fw-bold">Product Name <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" name="product_name" class="form-control"
                                                                placeholder="Enter product name" required>
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <label class="form-label fw-bold">Category</label>
                                                            <select id="category-dropdown" name="category_id"
                                                                class="form-select">
                                                                <option value="">Select Category</option>
                                                                @foreach ($categories as $cat)
                                                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <label class="form-label fw-bold">Sub Category</label>
                                                            <select id="subcategory-dropdown" name="sub_category_id"
                                                                class="form-select">
                                                                <option value="">Select Subcategory</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <label class="form-label fw-bold">Unit (UOM) <span
                                                                    class="text-danger">*</span></label>
                                                            <select name="unit" class="form-select" required>
                                                                <option value="" disabled selected>Select One</option>
                                                                <option value="LTR">LITTER (LTR)</option>
                                                                <option value="ML">ML</option>
                                                                <!-- <option value="CTN">Carton (CTN)</option> -->
                                                            </select>
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <label class="form-label fw-bold">Stock</label>
                                                            <input type="number" name="Stock" class="form-control"
                                                                placeholder="0" step="0.01" min="0">
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <label class="form-label fw-bold">Alert Quantity</label>
                                                            <input type="number" name="alert_quantity" class="form-control"
                                                                value="0">
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <label class="form-label fw-bold">Purchase Price</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">Rs</span>
                                                                <input type="number" name="wholesale_price"
                                                                    class="form-control" placeholder="0" step="0.01">
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <label class="form-label fw-bold">Sale Price</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">Rs</span>
                                                                <input type="number" name="retail_price"
                                                                    class="form-control" placeholder="0" step="0.01">
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <label class="form-label fw-bold">Note</label>
                                                            <textarea name="note" class="form-control" rows="1"
                                                                placeholder="Optional notes..."></textarea>
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="card-footer bg-light py-3">
                                                    <button type="submit" id="submitProductBtn"
                                                        class="btn btn-success px-4">
                                                        <i class="fas fa-save me-2"></i>Save Product
                                                    </button>
                                                    <a href="{{ route('product') }}" class="btn btn-secondary px-4 ms-2">
                                                        <i class="fas fa-times me-2"></i>Cancel
                                                    </a>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- bodywrapper__inner end -->
                </div><!-- body-wrapper end -->
            </div>

            {{-- Category Modal --}}
            <div id="categoryModal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><span>Add Category</span></h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <i class="las la-times"></i>
                            </button>
                        </div>
                        <form action="{{ route('manual.category') }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" name="redirect_url" value="{{ route('product') }}">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary h-45 w-100">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Subcategory Modal --}}
            <div id="subcategoryModal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><span>Add Subcategory</span></h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <i class="las la-times"></i>
                            </button>
                        </div>
                        <form action="{{ route('manual.subcategory') }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Category Name</label>
                                    <select name="category_id" class="form-select">
                                        @foreach ($categories as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Sub-Category Name</label>
                                    <input type="text" id="sub_category" name="sub_category" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary h-45 w-100">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Brand Modal --}}
            <div id="cuModal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><span>Add Brand</span></h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <i class="las la-times"></i>
                            </button>
                        </div>
                        <form action="{{ route('manual.Brand') }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary h-45 w-100">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

@endsection


        @section('scripts')
            <script>
                $(document).ready(function () {
                    // Subcategory Fetch on Category Change
                    $('#category-dropdown').on('change', function () {
                        var categoryId = $(this).val();
                        if (categoryId) {
                            $.ajax({
                                url: '/get-subcategories/' + categoryId,
                                type: "GET",
                                dataType: "json",
                                success: function (data) {
                                    $('#subcategory-dropdown').empty();
                                    $('#subcategory-dropdown').append('<option selected disabled>Select Subcategory</option>');
                                    $.each(data, function (key, value) {
                                        $('#subcategory-dropdown').append('<option value="' + value.id + '">' + value.name + '</option>');
                                    });
                                }
                            });
                        } else {
                            $('#subcategory-dropdown').empty();
                            $('#subcategory-dropdown').append('<option value="">Select Subcategory</option>');
                        }
                    });

                    // Prevent Enter key from submitting form
                    const form = document.getElementById('productForm');
                    form.addEventListener('keydown', function (e) {
                        if (e.key !== 'Enter') return;
                        const el = e.target;
                        const tag = el.tagName.toLowerCase();
                        if (tag === 'textarea') return;
                        if (el.classList && el.classList.contains('select2-search__field')) return;
                        e.preventDefault();
                    });
                });
            </script>
        @endsection