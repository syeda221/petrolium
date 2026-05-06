@extends('admin_panel.layout.app')

@section('content')
<style>
    /* ERP Style Variables */
    :root {
        --erp-primary: #37a371; /* Theme Green */
        --erp-secondary: #6c757d;
        --erp-bg: #f8f9fa;
        --erp-card-bg: #ffffff;
        --erp-border: #dee2e6;
        --erp-text: #495057;
        --erp-label: #212529;
    }

    .main-content {
        background-color: var(--erp-bg);
        min-height: 100vh;
        padding: 20px;
        color: var(--erp-text);
    }

    /* Page Titles */
    .erp-page-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--erp-label);
        margin-bottom: 20px;
        border-bottom: 2px solid var(--erp-primary);
        display: inline-block;
        padding-bottom: 5px;
    }

    /* Professional Card */
    .erp-card {
        background: var(--erp-card-bg);
        border: 1px solid var(--erp-border);
        border-radius: 4px; /* Sharper ERP look */
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }

    .erp-card-header {
        background: #fcfcfc;
        padding: 12px 20px;
        border-bottom: 1px solid var(--erp-border);
        font-weight: 600;
        color: var(--erp-label);
        font-size: 0.95rem;
    }

    .erp-card-body {
        padding: 20px;
    }

    /* ERP Form Controls */
    .form-label {
        font-weight: 600;
        color: var(--erp-label);
        font-size: 0.85rem;
        margin-bottom: 5px;
    }

    .form-control, .form-select {
        border-radius: 2px;
        border: 1px solid var(--erp-border);
        padding: 8px 12px;
        font-size: 0.9rem;
        height: auto;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--erp-primary);
        box-shadow: none;
        background-color: #fff;
    }

    /* Compact ERP Table */
    .table-erp {
        width: 100%;
        margin-bottom: 0;
        border: 1px solid var(--erp-border);
    }

    .table-erp thead th {
        background: #f1f3f5;
        color: #495057;
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        padding: 10px;
        border-bottom: 2px solid var(--erp-border);
        vertical-align: middle;
    }

    .table-erp tbody td {
        padding: 8px;
        border-bottom: 1px solid var(--erp-border);
        vertical-align: middle;
    }

    .table-erp .form-control {
        padding: 5px 8px;
        font-size: 0.85rem;
    }

    /* Product Search Dropdown */
    .product-dropdown {
        position: absolute;
        width: 100%;
        z-index: 1050;
        background: white;
        border: 1px solid var(--erp-border);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        display: none;
        max-height: 200px;
        overflow-y: auto;
    }

    .product-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        font-size: 0.85rem;
    }

    .product-item:hover {
        background-color: #f8f9fa;
        color: var(--erp-primary);
    }

    /* Summary Section */
    .summary-box {
        background: #f1f3f5;
        padding: 15px;
        border-radius: 4px;
        border: 1px solid var(--erp-border);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .summary-row.total {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid var(--erp-border);
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--erp-primary);
    }

    /* Buttons */
    .btn-erp {
        border-radius: 2px;
        padding: 8px 20px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .btn-erp-primary {
        background-color: var(--erp-primary);
        border-color: var(--erp-primary);
        color: white;
    }

    .btn-erp-primary:hover {
        background-color: #2d8a5e;
        border-color: #2d8a5e;
        color: white;
    }

    .btn-erp-outline {
        border: 1px solid var(--erp-border);
        background: white;
        color: var(--erp-secondary);
    }

    .btn-erp-outline:hover {
        background: #f8f9fa;
        color: var(--erp-text);
    }

    /* Hidden elements */
    .d-none { display: none !important; }

    /* Select2 Custom ERP Styling */
    .select2-container--default .select2-selection--single {
        border-radius: 2px !important;
        border: 1px solid var(--erp-border) !important;
        height: 38px !important;
        padding-top: 5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

</style>

<div class="main-content">
    <div class="container-fluid">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="erp-page-title">New Purchase Entry</h1>
            <a href="{{ route('Purchase.home') }}" class="btn btn-erp btn-erp-outline mb-3">
                <i class="fas fa-chevron-left me-1"></i> Back
            </a>
        </div>

        <form action="{{ route('store.Purchase') }}" method="POST" id="purchaseForm">
            @csrf
            
            <div class="row">
                <!-- Main Form Section -->
                <div class="col-lg-9">
                    
                    <!-- Header Info Card -->
                    <div class="erp-card">
                        <div class="erp-card-header">Reference & Logistics</div>
                        <div class="erp-card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-danger">Vendor *</label>
                                    <select name="vendor_id" id="vendorSelect" class="form-select" required>
                                        <option value=""></option>
                                        @foreach($Vendor as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Reference / Inv #</label>
                                    <input name="purchase_order_no" type="text" class="form-control" placeholder="Inv-001">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Date</label>
                                    <input name="purchase_date" value="{{ date('Y-m-d') }}" type="date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Delivery Destination</label>
                                    <div class="d-flex gap-3 mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input purchaseType" type="radio" name="purchase_to" value="warehouse" id="typeWH">
                                            <label class="form-check-label" for="typeWH">Warehouse</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input purchaseType" type="radio" name="purchase_to" value="shop" id="typeShop" checked>
                                            <label class="form-check-label" for="typeShop">Shop</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-4 d-none" id="warehouseBox">
                                    <label class="form-label text-danger">Target Warehouse *</label>
                                    <select name="warehouse_id" class="form-select">
                                        <option disabled selected>Select Warehouse</option>
                                        @foreach($Warehouse as $item)
                                            <option value="{{ $item->id }}">{{ $item->warehouse_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Transport Details</label>
                                    <input name="job_description" type="text" class="form-control" placeholder="Truck #, Transport Co.">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">General Remarks</label>
                                    <input name="note" type="text" class="form-control" placeholder="Internal notes...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table Card -->
                    <div class="erp-card">
                        <div class="erp-card-header">Inventory Items</div>
                        <div class="erp-card-body p-0">
                            <table class="table-erp">
                                <thead>
                                    <tr>
                                        <th style="width: 35%">Product Search</th>
                                        <th style="width: 10%">Unit</th>
                                        <th style="width: 15%">Rate</th>
                                        <th style="width: 15%">Discount (Rs)</th>
                                        <th style="width: 10%">Quantity</th>
                                        <th style="width: 15%">Subtotal</th>
                                        <th style="width: 40px"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemRows">
                                    <tr>
                                        <td>
                                            <div class="position-relative">
                                                <input type="hidden" name="product_id[]" class="product_id">
                                                <input type="text" class="form-control productSearch" placeholder="Type name or code..." autocomplete="off">
                                                <div class="product-dropdown"></div>
                                                <input type="hidden" name="item_code[]">
                                                <input type="hidden" name="brand[]">
                                            </div>
                                        </td>
                                        <td><input type="text" name="unit[]" class="form-control bg-light" readonly></td>
                                        <td><input type="number" step="0.01" name="price[]" class="form-control text-end price"></td>
                                        <td>
                                            <div class="d-flex">
                                                <input type="number" step="0.01" name="item_disc[]" class="form-control text-end item_disc" placeholder="PKR">
                                                <input type="hidden" name="item_disc_pct[]">
                                            </div>
                                        </td>
                                        <td><input type="number" name="qty[]" class="form-control text-center quantity"></td>
                                        <td><input type="text" name="total[]" class="form-control text-end bg-light fw-bold row-total" readonly value="0.00"></td>
                                        <td><button type="button" class="btn btn-sm text-danger remove-row"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="p-2 border-top bg-light text-center">
                                <button type="button" class="btn btn-sm btn-erp-outline" id="addRowBtn">
                                    <i class="fas fa-plus me-1"></i> Add Row (F2 or Enter on Qty)
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Sidebar: Totals -->
                <div class="col-lg-3">
                    <div class="erp-card">
                        <div class="erp-card-header">Payment Summary</div>
                        <div class="erp-card-body">
                            <div class="summary-box">
                                <div class="summary-row">
                                    <span>Total Items:</span>
                                    <span id="itemsCount">1</span>
                                </div>
                                <div class="summary-row">
                                    <span>Total Qty:</span>
                                    <span id="totalQty">0</span>
                                </div>
                                <div class="summary-row">
                                    <span>Gross Total:</span>
                                    <span id="grossTotal">0.00</span>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label">Extra Cost</label>
                                    <input type="number" step="0.01" id="extraCost" name="extra_cost" class="form-control text-end" value="0">
                                </div>
                                <div class="mt-2">
                                    <label class="form-label">Fixed Discount</label>
                                    <input type="number" step="0.01" id="overallDiscount" name="discount" class="form-control text-end" value="0">
                                </div>
                                <div class="summary-row total">
                                    <span>Net Payable:</span>
                                    <span id="netAmountDisp">0.00</span>
                                </div>
                                <input type="hidden" name="subtotal" id="subtotalInput">
                                <input type="hidden" name="net_amount" id="netAmountInput">
                            </div>

                            <div class="mt-4 d-grid gap-2">
                                <button type="submit" class="btn btn-erp btn-erp-primary py-2" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> Save Bill (Alt + S)
                                </button>
                                <button type="button" class="btn btn-erp btn-erp-outline" id="resetBtn">
                                    Reset Form
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let productCache = [];

    $(document).ready(function() {
        // Load products
        $.get("{{ route('search-product-name') }}", { q: '' }, function(res) {
            productCache = res;
        });

        // Initialize Select2 for Vendor
        $('#vendorSelect').select2({
            placeholder: "Select or Search Vendor",
            allowClear: true,
            width: '100%'
        });

        // Prevention
        $('#purchaseForm').on('keydown', function(e) {
            if (e.key === 'Enter' && !$(e.target).hasClass('quantity')) { e.preventDefault(); }
            if (e.altKey && e.key === 's') { e.preventDefault(); $('#submitBtn').click(); }
            if (e.key === 'F2') { e.preventDefault(); $('#addRowBtn').click(); }
        });

        $('.purchaseType').change(function() {
            if ($('#typeWH').is(':checked')) {
                $('#warehouseBox').removeClass('d-none');
                $('select[name="warehouse_id"]').prop('required', true);
            } else {
                $('#warehouseBox').addClass('d-none');
                $('select[name="warehouse_id"]').prop('required', false);
            }
        });
    });

    // --- Search ---
    let activeDropdownIndex = -1;

    $(document).on('input', '.productSearch', function() {
        let input = $(this);
        let dropdown = input.siblings('.product-dropdown');
        let query = input.val().toLowerCase();
        
        dropdown.empty().hide();
        activeDropdownIndex = -1;
        if (query.length < 1) return;

        let res = productCache.filter(p => 
            (p.item_name && p.item_name.toLowerCase().includes(query)) ||
            (p.item_code && p.item_code.toLowerCase().includes(query))
        ).slice(0, 10);

        if (res.length > 0) {
            res.forEach(p => {
                dropdown.append(`<div class="product-item" data-p='${JSON.stringify(p)}'>${p.item_name} (${p.item_code})</div>`);
            });
            dropdown.show();
        }
    });

    $(document).on('keydown', '.productSearch', function(e) {
        const $dropdown = $(this).siblings('.product-dropdown');
        const $items = $dropdown.find('.product-item');
        
        if (!$dropdown.is(':visible') || $items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeDropdownIndex = (activeDropdownIndex + 1) % $items.length;
            highlightItem($items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeDropdownIndex = (activeDropdownIndex - 1 + $items.length) % $items.length;
            highlightItem($items);
        } else if (e.key === 'Enter' || e.key === 'Tab') {
            if (activeDropdownIndex > -1) {
                e.preventDefault();
                $items.eq(activeDropdownIndex).click();
                activeDropdownIndex = -1;
            }
        }
    });

    function highlightItem($items) {
        $items.css('background', 'white').css('color', 'inherit');
        if (activeDropdownIndex > -1) {
            const $active = $items.eq(activeDropdownIndex);
            $active.css('background', '#37a371').css('color', 'white');
            $active[0].scrollIntoView({ block: 'nearest' });
        }
    }

    $(document).on('click', '.product-item', function() {
        let p = $(this).data('p');
        let row = $(this).closest('tr');
        
        row.find('.product_id').val(p.id);
        row.find('.productSearch').val(p.item_name);
        row.find('[name="item_code[]"]').val(p.item_code);
        row.find('[name="brand[]"]').val(p.brand);
        row.find('[name="unit[]"]').val(p.unit_id);
        row.find('.price').val(p.wholesale_price || 0);
        row.find('.quantity').val(1);
        
        $(this).parent().hide();
        recalcRow(row);
        recalcTotals();
        row.find('.quantity').focus().select();
    });

    $('#addRowBtn').click(function() {
        let html = `
        <tr>
            <td>
                <div class="position-relative">
                    <input type="hidden" name="product_id[]" class="product_id">
                    <input type="text" class="form-control productSearch" placeholder="Type name..." autocomplete="off">
                    <div class="product-dropdown"></div>
                    <input type="hidden" name="item_code[]">
                    <input type="hidden" name="brand[]">
                </div>
            </td>
            <td><input type="text" name="unit[]" class="form-control bg-light" readonly></td>
            <td><input type="number" step="0.01" name="price[]" class="form-control text-end price"></td>
            <td><input type="number" step="0.01" name="item_disc[]" class="form-control text-end item_disc"></td>
            <td><input type="number" name="qty[]" class="form-control text-center quantity"></td>
            <td><input type="text" name="total[]" class="form-control text-end bg-light fw-bold row-total" readonly value="0.00"></td>
            <td><button type="button" class="btn btn-sm text-danger remove-row"><i class="fas fa-times"></i></button></td>
        </tr>`;
        $('#itemRows').append(html);
        $('#itemRows tr:last .productSearch').focus();
        updateCount();
    });

    $(document).on('input', '.price, .quantity, .item_disc, #extraCost, #overallDiscount', function() {
        let row = $(this).closest('tr');
        if (row.length) recalcRow(row);
        recalcTotals();
    });

    $(document).on('keydown', '.quantity', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#addRowBtn').click();
        }
    });

    $(document).on('click', '.remove-row', function() {
        if ($('#itemRows tr').length > 1) {
            $(this).closest('tr').remove();
            recalcTotals();
            updateCount();
        }
    });

    function recalcRow(row) {
        let qty = parseFloat(row.find('.quantity').val()) || 0;
        let prc = parseFloat(row.find('.price').val()) || 0;
        let dsc = parseFloat(row.find('.item_disc').val()) || 0;
        let tot = (qty * prc) - (qty * dsc);
        row.find('.row-total').val(tot.toFixed(2));
    }

    function recalcTotals() {
        let stot = 0, tqty = 0;
        $('#itemRows tr').each(function() {
            tqty += parseFloat($(this).find('.quantity').val()) || 0;
            stot += parseFloat($(this).find('.row-total').val()) || 0;
        });

        let xcost = parseFloat($('#extraCost').val()) || 0;
        let disc = parseFloat($('#overallDiscount').val()) || 0;
        let net = stot + xcost - disc;

        $('#totalQty').text(tqty);
        $('#grossTotal').text(stot.toFixed(2));
        $('#netAmountDisp').text(net.toFixed(2));
        $('#subtotalInput').val(stot.toFixed(2));
        $('#netAmountInput').val(net.toFixed(2));
    }

    function updateCount() {
        $('#itemsCount').text($('#itemRows tr').length);
    }

    $('#resetBtn').click(function() {
        if (confirm("Clear all bill data?")) location.reload();
    });

</script>
@endsection
