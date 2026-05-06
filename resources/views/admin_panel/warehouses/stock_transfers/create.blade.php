@extends('admin_panel.layout.app')
<style>
    .form-section {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
    }

    .form-section h6 {
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 15px;
    }

    .table th {
        background: #f8fafc;
        font-weight: 600;
    }

    .remove-row {
        padding: 4px 10px;
    }

    .unit-total-box {
        min-width: 90px;
        padding: 6px 8px;
        border-radius: 8px;
        text-align: center;
        display: inline-block;
        flex-shrink: 0;
        /* 🔥 IMPORTANT */
    }

    .unit-total-box .label {
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .unit-total-box input {
        font-weight: bold;
        text-align: center;
        border: none;
        background: rgba(255, 255, 255, 0.9);
        height: 30px;
    }
</style>

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">➕ New Stock Transfer</h5>
        <a href="{{ url()->previous() }}" class="btn btn-danger btn-sm rounded-pill px-3">
            Back
        </a>
    </div>
    <div class="card-body">

        <form action="{{ route('stock_transfers.store') }}" method="POST" novalidate>
            @csrf
            @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif
            <!-- ================= BASIC INFO ================= -->
            <div class="form-section">
                <h6>📄 Transfer Information</h6>

                <div class="row g-4">
                    <div class="col-md-4">
                        <label>Date</label>
                        <input type="date" name="transfer_date" class="form-control"
                            value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="col-md-4">
                        <label>From Warehouse</label>
                        <select name="from_warehouse_id" id="from_warehouse_id" class="form-control select2">
                            <option value="">Select Warehouse</option>
                            <option value="Shop">Shop</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>


            <!-- ================= DESTINATION ================= -->
            <div class="form-section">
                <h6>📦 Transfer Destination</h6>

                <div class="row g-4 align-items-end">

                    <!-- TYPE -->
                    <div class="col-md-4">
                        <label class="d-block mb-2">Transfer To</label>

                        <div class="form-check">
                            <input class="form-check-input transferType" type="radio"
                                name="transfer_to" value="warehouse" id="toWarehouse">
                            <label class="form-check-label" for="toWarehouse">
                                Warehouse
                            </label>
                        </div>

                        <div class="form-check mt-2">
                            <input class="form-check-input transferType" type="radio"
                                name="transfer_to" value="shop" id="toShop">
                            <label class="form-check-label" for="toShop">
                                Shop
                            </label>
                        </div>
                    </div>

                    <!-- TO WAREHOUSE -->
                    <div class="col-md-4 d-none" id="toWarehouseBox">
                        <label>To Warehouse</label>
                        <select name="to_warehouse_id" class="form-control select2">
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">
                                {{ $warehouse->warehouse_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- TO SHOP -->
                    <div class="col-md-4 d-none" id="toShopBox">
                        <label>Shop Name <small class="text-muted">(Optional)</small></label>
                        <input type="text" name="shop_name"
                            class="form-control"
                            placeholder="Auto: Main Shop">
                    </div>

                </div>
            </div>

            <button type="button" class="btn btn-primary btn-sm mt-2 mb-2" id="openProductModal">
                Search Product (F2)
            </button>
            <div class="modal fade" id="productModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Search Product</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <input type="text"
                                id="modalProductSearch"
                                class="form-control mb-2"
                                placeholder="Type product name or scan barcode"
                                autofocus>

                            <ul class="list-group" id="modalSearchResults"
                                style="max-height:300px; overflow-y:auto;"></ul>

                        </div>

                    </div>
                </div>
            </div>

            <div class="form-section">
                <h6>📋 Products to Transfer</h6>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center" id="product_table">
                        <thead>
                            <tr>
                                <th width="30%">Product</th>
                                <th width="10%">Unit</th>
                                <th width="15%">Retail Price</th>
                                <th width="15%">Available Stock</th>
                                <th width="15%">Transfer Qty</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>

                        <tbody id="product_body">
                            <tr class="product_row">
                                <td style="position:relative">
                                    <input type="hidden" name="product_id[]" class="product_id">
                                    <input type="text" class="form-control productSearch" placeholder="Select product from Search (F2)" readonly>
                                </td>
                                <td>
                                    <input type="text" class="form-control unit" readonly>
                                </td>
                                <td>
                                    <input type="number" class="form-control price" readonly>
                                </td>
                                <td>
                                    <input type="number" class="form-control stock" readonly>
                                </td>

                                <td>
                                    <input type="number"
                                        name="quantity[]"
                                        class="form-control quantity"
                                        required
                                        step="any"
                                        inputmode="numeric">
                                </td>

                                <td>
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-row">
                                        ✖
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                        <tfoot>
                            <tr class="bg-light fw-bold">
                                <td colspan="4" class="text-end">Unit Totals</td>
                                <td id="unitTotalsFooter" colspan="2"></td>
                            </tr>
                        </tfoot>

                    </table>
                </div>
            </div>

            <!-- ================= REMARKS ================= -->
            <div class="form-section">
                <h6>📝 Remarks</h6>
                <textarea name="remarks" rows="3" class="form-control"
                    placeholder="Optional remarks..."></textarea>
            </div>

            <!-- ================= ACTION ================= -->
            <div class="text-end">
                <button type="submit" class="btn btn-primary px-5 py-2">
                    🔄 Transfer Stock
                </button>
            </div>

        </form>

    </div>
</div>
@endsection

@section('scripts')
<script>
    function addNewRow() {
        const row = `
<tr class="product_row">
    <td style="position:relative">
        <input type="hidden" name="product_id[]" class="product_id">
        <input type="text" class="form-control productSearch" placeholder="Select product from Search (F2)" readonly>
    </td>
    <td>
        <input type="text" class="form-control unit" readonly>
    </td>
    <td>
        <input type="number" class="form-control price" readonly>
    </td>
    <td>
        <input type="number" class="form-control stock" readonly>
    </td>
    <td>
        <input type="number" name="quantity[]" class="form-control quantity">
    </td>
    <td>
        <button type="button" class="btn btn-outline-danger btn-sm remove-row">✖</button>
    </td>
</tr>`;

        $('#product_body').append(row);

        setTimeout(() => {
            $('#product_body tr:last .productSearch').focus();
        }, 10);
    }

    $(document).ready(function() {


        $('.transferType').on('change', function() {
            let type = $(this).val();

            // hide all first
            $('#toWarehouseBox').addClass('d-none');
            $('#toShopBox').addClass('d-none');

            // clear destination values
            $('select[name="to_warehouse_id"]').val('').trigger('change');
            $('input[name="shop_name"]').val('');

            // Refresh stock for all existing rows with products
            $('#product_body tr').each(function() {
                const $row = $(this);
                const productId = $row.find('.product_id').val();
                
                if (!productId) {
                    $row.find('.stock').val('');
                    $row.find('.quantity').removeAttr('max').val('');
                    return;
                }

                // Get the current from_warehouse value
                const fromWarehouse = $('#from_warehouse_id').val();
                const warehouseIdParam = (fromWarehouse === 'Shop' || !fromWarehouse) ? null : fromWarehouse;
                
                // Fetch fresh stock
                $.ajax({
                    url: "{{ route('warehouse.stock.quantity') }}",
                    type: 'GET',
                    data: {
                        warehouse_id: warehouseIdParam,
                        product_id: productId
                    },
                    timeout: 5000,
                    success: (response) => {
                        $row.find('.stock').val(response.quantity ?? 0);
                    },
                    error: () => {
                        $row.find('.stock').val(0);
                    }
                });
            });

            if (type === 'warehouse') {
                $('#toWarehouseBox').removeClass('d-none');
            }

            if (type === 'shop') {
                $('#toShopBox').removeClass('d-none');
            }
        });

        const unitColors = {
            Yard: 'primary',
            Piece: 'success',
            Meter: 'warning'
        };

        function calculateCreateUnitTotals() {
            let totals = {};

            $('#product_table tbody tr').each(function() {
                let qtyVal = $(this).find('.quantity').val();
                let unitVal = $(this).find('.unit').val();

                if (!qtyVal || !unitVal) return;

                let qty = parseFloat(qtyVal);
                let unit = unitVal.trim();

                if (isNaN(qty) || qty <= 0) return;

                totals[unit] = (totals[unit] || 0) + qty;
            });

            let html = '';

            if (Object.keys(totals).length === 0) {
                html = `<span class="text-muted">No quantities entered</span>`;
            } else {
                html += `<div class="d-flex gap-2 justify-content-end align-items-center">`;

                Object.keys(totals).forEach(unit => {
                    let color = unitColors[unit] || 'secondary';

                    html += `
                <div class="unit-total-box bg-${color} text-${color === 'warning' ? 'dark' : 'white'}">
                    <div class="label">${unit}</div>
                    <input type="text" value="${totals[unit].toFixed(2)}" readonly>
                </div>
            `;
                });

                html += `</div>`;
            }

            $('#unitTotalsFooter').html(html);
        }



        $(document).ready(function() {
            calculateCreateUnitTotals();
        });

        // ---------- Helper: initialize a product-select element with Select2 ----------
        function initProductSelect($sel) {
            // prevent double-init
            if ($sel.data('select2')) return;

            $sel.select2({
                placeholder: 'Select Product',
                allowClear: true,
                width: '100%',
                dropdownParent: $sel.closest('td') // keeps dropdown aligned
            });

            // when product changes — fetch stock for this row
            $sel.off('change.initStock').on('change.initStock', function() {
                var $currentRow = $(this).closest('tr');
                var selectedProduct = $(this).val();
                var fromWarehouse = $('#from_warehouse_id').val();

                // clear stock & qty if no product selected
                if (!selectedProduct) {
                    $currentRow.find('.stock').val('');
                    $currentRow.find('.quantity').removeAttr('max').val('');
                    return;
                }

                if (fromWarehouse) {
                    // WAREHOUSE CASE
                    $.get("{{ route('warehouse.stock.quantity') }}", {
                        warehouse_id: fromWarehouse,
                        product_id: selectedProduct
                    }, function(response) {
                        $currentRow.find('.stock').val(response.quantity ?? 0);
                    });
                } else {
                    // SHOP CASE
                    $.get("{{ route('warehouse.stock.quantity') }}", {
                        warehouse_id: null, // blank ya null bhejna zaruri
                        product_id: selectedProduct
                    }, function(response) {
                        $currentRow.find('.stock').val(response.quantity ?? 0);
                        $row.find('.quantity').removeAttr('max');
                    });
                }

                // auto add new row if last row
                if ($('#product_body tr:last').is($currentRow)) {
                    addNewRow();
                    setTimeout(function() {
                        $('#product_body tr:last').find('.product-select').select2('open');
                    }, 100);
                }
            });
        }

        // initialize existing product-select(s)
        $('#product_body').find('.product-select').each(function() {
            initProductSelect($(this));
        });

        // When 'From Warehouse' changes, refresh stock for all rows with products
        $('#from_warehouse_id').on('change', function() {
            var fromWarehouse = $(this).val();

            $('#product_body tr').each(function() {
                var $row = $(this);
                var productId = $row.find('.product_id').val();

                if (!productId) {
                    $row.find('.stock').val('');
                    $row.find('.quantity').removeAttr('max').val('');
                    return;
                }

                // Prepare warehouse_id parameter
                const warehouseIdParam = (fromWarehouse === 'Shop' || !fromWarehouse) ? null : fromWarehouse;

                // Fetch stock with proper error handling
                $.ajax({
                    url: "{{ route('warehouse.stock.quantity') }}",
                    type: 'GET',
                    data: {
                        warehouse_id: warehouseIdParam,
                        product_id: productId
                    },
                    timeout: 5000,
                    success: (response) => {
                        $row.find('.stock').val(response.quantity ?? 0);
                    },
                    error: () => {
                        $row.find('.stock').val(0);
                    }
                });
            });
        });

        // Enter on quantity opens new row (existing behavior)


        // Validate quantity vs stock
        $(document).on('input', '.quantity', function() {

            const $row = $(this).closest('tr');
            const val = $(this).val();
            if (val === '' || val === '-') {
                return;
            }
            const entered = Number(val);
            if (isNaN(entered)) return;
            if (typeof maxAttr === 'undefined') return;
            const max = Number(maxAttr);
            if (!isNaN(max)) {
                $row.find('.stock').val(max - entered);
            }
        });

        $(document).on('input', '.quantity', function() {
            calculateCreateUnitTotals();
        });


        $(document).on('input', '.quantity', calculateCreateUnitTotals);

        $(document).ready(function() {
            calculateCreateUnitTotals();
        });

        // Remove row (destroy select2 first)
        $(document).on('click', '.remove-row', function() {
            var $row = $(this).closest('tr');
            var $sel = $row.find('.product-select');
            // destroy select2 if initialized
            if ($sel.data('select2')) {
                try {
                    $sel.select2('destroy');
                } catch (e) {}
            }
            $row.remove();
            calculateCreateUnitTotals();

        });

        // If page loaded with only one blank row, ensure it's initialized
        if ($('#product_body tr').length === 1) {
            initProductSelect($('#product_body tr:first').find('.product-select'));
        }

    });

    let IS_SCANNING = false;
    $(document).on('keydown', '.quantity', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();

            let row = $(this).closest('tr');

            if ($('#product_body tr:last').is(row)) {
                addNewRow();
            }

            setTimeout(() => {
                $('#product_body tr:last .productSearch').focus();
            }, 80);
        }
    });

    $(document).on('keydown', function(e) {

        if ($(e.target).is('textarea')) return;

        IS_SCANNING = true;

        if (e.key === 'Enter') {
            e.preventDefault();

            if (scanBuffer.length >= 5) {
                handleTransferBarcode(scanBuffer);
            }

            scanBuffer = '';
            IS_SCANNING = false;
            return;
        }

        if (e.key.length === 1) {
            scanBuffer += e.key;
        }

        clearTimeout(scanTimer);
        scanTimer = setTimeout(() => {
            scanBuffer = '';
            IS_SCANNING = false;
        }, 120);
    });
    $(document).on('focus', '.quantity', function() {
        $(this).removeAttr('max');
    });

    // ------------------- SCANNER BUFFER -------------------
    let scanBuffer = '';
    let scanTimer = null;
    let lastBarcode = null;
    let lastScanTime = 0;
    const SCAN_DELAY = 500; // ms to prevent double scan

    $(document).on('keydown', function(e) {
        // Ignore typing in textareas and search inputs
        if ($(e.target).is('textarea, input')) return;
        // Enter key → process scan
        if (e.key === 'Enter') {
            e.preventDefault();

            if (scanBuffer.length >= 5) {
                handleTransferBarcode(scanBuffer.trim());
            }

            scanBuffer = '';
            return;
        }

        // Append normal key characters
        if (e.key.length === 1) {
            scanBuffer += e.key;
        }

        // Reset buffer if idle too long
        clearTimeout(scanTimer);
        scanTimer = setTimeout(() => {
            scanBuffer = '';
        }, 200); // 200ms safe for scanners
    });

    // ------------------- HANDLE BARCODE -------------------
    function handleTransferBarcode(barcode) {
        const now = Date.now();

        // Prevent duplicate scan
        if (barcode === lastBarcode && (now - lastScanTime) < SCAN_DELAY) {
            console.log('Duplicate barcode blocked:', barcode);
            return;
        }

        lastBarcode = barcode;
        lastScanTime = now;

        let foundRow = null;

        // Check if product already exists
        $('#product_body tr').each(function() {
            const pid = $(this).find('.product_id').data('barcode');
            if (pid && pid === barcode) {
                foundRow = $(this);
                return false;
            }
        });

        if (foundRow) {
            // Increment quantity
            const qtyInput = foundRow.find('.quantity');
            qtyInput
                .val((+qtyInput.val() || 0) + 1)
                .trigger('input');
            foundRow.addClass('table-success');
            setTimeout(() => foundRow.removeClass('table-success'), 200);
            return;
        }

        // Use last row if empty, else add new
        let row = $('#product_body tr:last');
        if (row.find('.product_id').val()) {
            addNewRow();
            row = $('#product_body tr:last');
        }

        // Fetch product details via AJAX
        $.get("{{ route('search-product-by-barcode') }}", {
            barcode
        }, function(res) {
            if (!res || !res.id) {
                alert('Product not found!');
                return;
            }

            row.find('.product_id').val(res.id).data('barcode', barcode);
            row.find('.productSearch').val(res.name).prop('readonly', true);
            row.find('.unit').val(res.unit);
            row.find('.price').val(res.price);
            row.find('.quantity').val(1).trigger('input');
            calculateCreateUnitTotals();
            
            // Fetch stock - use improved AJAX
            const fromWarehouse = $('#from_warehouse_id').val();
            const warehouseIdParam = (fromWarehouse === 'Shop' || !fromWarehouse) ? null : fromWarehouse;
            
            $.ajax({
                url: "{{ route('warehouse.stock.quantity') }}",
                type: 'GET',
                data: {
                    warehouse_id: warehouseIdParam,
                    product_id: res.id
                },
                timeout: 5000,
                success: (stock) => {
                    row.find('.stock').val(stock.quantity ?? 0);
                },
                error: () => {
                    row.find('.stock').val(0);
                }
            });

            addNewRow();

            // ✅ Focus Product of the NEW row
            setTimeout(() => {
                $('#product_body tr:last .productSearch').focus();
            }, 50);
        });
    }



    $(document).ready(function() {
        setTimeout(() => {
            $('#product_body tr:first .productSearch').focus();
        }, 300);
    });


    const SCAN_GAP = 50; // ms (scanner fast)
    const SCAN_TIMEOUT = 80;

    $(document).on('keydown', function(e) {

        if (e.key === 'F2') {
            e.preventDefault();
            e.stopPropagation();
            openProductModal();
            return false;
        }

    });





    $('#openProductModal').on('click', function(e) {
        e.preventDefault();
        openProductModal();
    });

    $(document).on('click', '.modal-product-item', function() {
        $('#productModal').modal('hide');

        // Reset search box
        $('#modalProductSearch').val('');
        $('#modalSearchResults').empty();
    });
    $('#productModal').on('shown.bs.modal', function() {
        const $input = $('#modalProductSearch');
        $input.focus(); // Cursor focus on search input
        activeIndex = 0;
        setActiveItem(activeIndex); // First item active
    });


    function openProductModal() {

        $('#modalProductSearch').val('');
        $('#modalSearchResults').empty();

        $('#productModal').modal('show');

        $('#productModal').one('shown.bs.modal', function() {
            $('#modalProductSearch').focus();
            activeIndex = 0;
        });
    }


    let modalTimer = null;

    $('#modalProductSearch').on('input', function() {
        clearTimeout(modalTimer);
        let q = $(this).val().trim();

        if (q.length < 2) {
            $('#modalSearchResults').empty();
            return;
        }

        modalTimer = setTimeout(() => {
            $.get("{{ route('search-product-name') }}", {
                q
            }, function(res) {

                let html = '';
                res.forEach(p => {

                    let noteText = (p.note && p.note.trim() !== '') ? p.note : '-';

                    html += `
<li class="list-group-item modal-product-item"
    data-id="${p.id}"
    data-name="${p.item_name}"
    data-code="${p.item_code}"
    data-price="${p.price}"
    data-unit="${p.unit_id}"
    data-brand="${p.brand ?? ''}"
    data-note="${noteText}">
    <strong>${p.item_name}</strong>
    <br>
    <small>Rs: ${p.price} | ${p.brand ?? '-'}</small>
    <br>
    <strong class="text-Dark">Note: ${noteText}</strong>

</li>`;
                });

                $('#modalSearchResults').html(html);
                activeIndex = -1;
                setActiveItem(0);
            });
        }, 250);
    });

    $(document).on('mouseenter', '.modal-product-item', function() {
        const index = $(this).index();
        setActiveItem(index);
    });

    $('#modalProductSearch').on('keydown', function(e) {

        const items = $('#modalSearchResults .modal-product-item');

        if (!items.length) return;

        switch (e.key) {

            case 'ArrowDown':
                e.preventDefault();
                setActiveItem(activeIndex + 1);
                break;

            case 'ArrowUp':
                e.preventDefault();
                setActiveItem(activeIndex - 1);
                break;

            case 'Enter':
                e.preventDefault();
                if (activeIndex >= 0) {
                    items.eq(activeIndex).trigger('click');
                }
                break;
        }
    });

    $(document).on('click', '.modal-product-item', function() {
        let row = $('#product_body tr:last');

        if (row.find('.product_id').val()) {
            addNewRow();
            row = $('#product_body tr:last');
        }

        const productId = $(this).data('id');
        row.find('.product_id').val(productId);
        row.find('.productSearch').val($(this).data('name')).prop('readonly', true);
        row.find('.unit').val($(this).data('unit'));
        row.find('.price').val($(this).data('price'));
        row.find('.quantity').val(1).trigger('input');
        calculateCreateUnitTotals();

        // 🔥 IMPROVED: Always fetch stock immediately with better error handling
        const fromWarehouse = $('#from_warehouse_id').val();
        const warehouseIdParam = (fromWarehouse === 'Shop' || !fromWarehouse) ? null : fromWarehouse;
        
        // Use proper scoping with arrow function to keep row reference
        $.ajax({
            url: "{{ route('warehouse.stock.quantity') }}",
            type: 'GET',
            data: {
                warehouse_id: warehouseIdParam,
                product_id: productId
            },
            timeout: 5000,
            success: (stock) => {
                row.find('.stock').val(stock.quantity ?? 0);
            },
            error: (xhr, status, error) => {
                console.warn('Stock fetch failed:', error);
                row.find('.stock').val(0);
            }
        });

        $('#productModal').modal('hide');

        // 🔥 Focus the quantity field
        setTimeout(() => {
            row.find('.quantity').focus().select();
        }, 150);

    });



    let activeIndex = -1;

    function setActiveItem(index) {
        const items = $('#modalSearchResults .modal-product-item');
        items.removeClass('active');

        if (items.length === 0) return;

        if (index < 0) index = 0;
        if (index >= items.length) index = items.length - 1;

        activeIndex = index;

        const activeItem = items.eq(activeIndex);
        activeItem.addClass('active');

        // 🔥 auto scroll into view
        activeItem[0].scrollIntoView({
            block: 'nearest'
        });
    }
</script>


@endsection