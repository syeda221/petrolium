@extends('admin_panel.layout.app')
@section('content')

<style>
    .component-row {
        background: #f8f9fa;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 8px;
        border-left: 3px solid #37a371;
    }
    .remove-component {
        cursor: pointer;
        color: #dc3545;
    }
    .stock-info {
        font-size: 12px;
        color: #6c757d;
    }
</style>

<div class="container-fluid">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-boxes me-2" style="color: #37a371;"></i>Create Group Product
            </h5>
            <a href="{{ route('group-products.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('group-products.store') }}" method="POST" id="groupProductForm">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="product_name" class="form-control" value="{{ old('product_name') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Quantity Produced (Bags) <span class="text-danger">*</span></label>
                        <input type="number" name="quantity_produced" id="quantityProduced" class="form-control" value="{{ old('quantity_produced') }}" min="1" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Sale Price (Per Unit) <span class="text-danger">*</span></label>
                        <input type="number" name="sale_price" class="form-control" value="{{ old('sale_price') }}" min="0" step="0.01" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Total Cost</label>
                        <input type="text" id="totalCostDisplay" class="form-control" readonly value="Rs 0">
                        <small class="stock-info">Auto-calculated from components</small>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">Components (Products to Combine)</h6>
                    <button type="button" class="btn btn-sm btn-success" id="addComponent">
                        <i class="fas fa-plus me-1"></i> Add Component
                    </button>
                </div>

                <div id="componentsContainer">
                    <!-- Component rows will be added here -->
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Create Group Product
                    </button>
                    <a href="{{ route('group-products.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let componentIndex = 0;
    let products = @json($products);

    $(document).ready(function() {
        // Add first component by default
        addComponentRow();

        $('#addComponent').click(function() {
            addComponentRow();
        });

        // Calculate total cost when quantity changes
        $(document).on('change', '.component-select, .component-quantity', function() {
            calculateTotalCost();
        });
    });

    function addComponentRow() {
        const html = `
            <div class="component-row" data-index="${componentIndex}">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select name="components[${componentIndex}][product_id]" class="form-select component-select" required>
                            <option value="">Select Product</option>
                            ${products.map(p => `
                                <option value="${p.id}" data-price="${p.price || 0}" data-stock="${p.calculated_stock}">
                                    ${p.item_name} (Stock: ${p.calculated_stock})
                                </option>
                            `).join('')}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="components[${componentIndex}][quantity]" class="form-control component-quantity" min="1" required>
                        <small class="stock-info">Available: <span class="available-stock">-</span></small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Component Cost</label>
                        <input type="text" class="form-control component-cost-display" readonly value="Rs 0">
                    </div>
                    <div class="col-md-1 text-end">
                        ${componentIndex > 0 ? `<i class="fas fa-trash remove-component" onclick="removeComponent(${componentIndex})"></i>` : ''}
                    </div>
                </div>
            </div>
        `;

        $('#componentsContainer').append(html);
        componentIndex++;

        // Update available stock on product selection
        $(document).on('change', '.component-select', function() {
            const stock = $(this).find(':selected').data('stock');
            $(this).closest('.component-row').find('.available-stock').text(stock || 0);
        });
    }

    function removeComponent(index) {
        $(`.component-row[data-index="${index}"]`).remove();
        calculateTotalCost();
    }

    function calculateTotalCost() {
        let totalCost = 0;

        $('.component-row').each(function() {
            const select = $(this).find('.component-select');
            const quantity = $(this).find('.component-quantity').val() || 0;
            const price = select.find(':selected').data('price') || 0;
            const componentCost = price * quantity;

            $(this).find('.component-cost-display').val('Rs ' + componentCost.toFixed(0));
            totalCost += componentCost;
        });

        $('#totalCostDisplay').val('Rs ' + totalCost.toFixed(0));
    }

    // Validate stock before submission
    $('#groupProductForm').on('submit', function(e) {
        let valid = true;
        let errors = [];

        $('.component-row').each(function() {
            const select = $(this).find('.component-select');
            const quantity = parseInt($(this).find('.component-quantity').val()) || 0;
            const stock = parseInt(select.find(':selected').data('stock')) || 0;
            const productName = select.find(':selected').text().split('(')[0].trim();

            if (quantity > stock) {
                valid = false;
                errors.push(`Insufficient stock for ${productName}. Available: ${stock}, Requested: ${quantity}`);
            }
        });

        if (!valid) {
            e.preventDefault();
            alert('Stock Validation Failed:\n\n' + errors.join('\n'));
        }
    });
</script>
@endsection
