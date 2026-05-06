@extends('admin_panel.layout.app')
@section('content')
    <style>
        body {
            padding-bottom: 110px;
        }

        /* ===== SIMPLE SEARCH DROPDOWN ===== */
        .product-search-wrapper {
            position: relative;
        }

        .product-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 9999;
            max-height: 250px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 6px 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: none;
        }

        .product-dropdown-item {
            padding: 10px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.15s;
        }

        .product-dropdown-item:hover,
        .product-dropdown-item.active {
            background: #007bff;
            color: #fff;
        }

        .product-dropdown-item:last-child {
            border-bottom: none;
        }

        .product-dropdown-item .product-name {
            font-weight: 600;
            font-size: 14px;
        }

        .product-dropdown-item .product-meta {
            font-size: 12px;
            opacity: 0.8;
        }

        .product-dropdown-item:hover .product-meta,
        .product-dropdown-item.active .product-meta {
            opacity: 1;
        }

        /* ===== TABLE STYLING ===== */
        .table thead th {
            font-weight: 700;
            font-size: 14px;
            background-color: #f8f9fa;
            color: #000;
            vertical-align: middle;
            text-align: center;
        }

        .table tbody input.form-control,
        .table tbody textarea.form-control {
            font-size: 14px;
            font-weight: 500;
            color: #000;
        }

        .product-col {
            width: 25% !important;
            min-width: 250px;
        }

        /* ===== CUSTOMER BALANCE INFO BAR ===== */
        .customer-balance-bar {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 8px;
            padding: 8px 16px;
            display: none;
            border: 1px solid #0f3460;
        }

        .customer-balance-bar.show {
            display: flex;
        }

        .balance-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-right: 20px;
            padding-right: 20px;
            border-right: 1px solid rgba(255, 255, 255, 0.15);
        }

        .balance-item:last-child {
            border-right: none;
            margin-right: 0;
        }

        .balance-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #a0aec0;
        }

        .balance-value {
            font-size: 18px;
            font-weight: 900;
            color: #fff;
        }

        .balance-value.warning {
            color: #f6ad55;
        }

        .balance-value.danger {
            color: #fc8181;
        }

        .balance-value.success {
            color: #68d391;
        }

        .cust-name-display {
            font-size: 13px;
            font-weight: 700;
            color: #e2e8f0;
        }

        /* ===== BOTTOM SUMMARY BAR ===== */
        .fixed-summary-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 0 !important;
            z-index: 9999;
            background: #ffffff;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.2);
            border-top: 3px solid #198754;
        }

        .fixed-summary-bar th {
            background: #f1fdf4;
            font-size: 12px;
            font-weight: 700;
        }

        .fixed-summary-bar input {
            height: 30px !important;
            font-size: 16px !important;
            padding: 2px 4px !important;
        }

        .invoice-summary-table {
            table-layout: fixed;
            width: 100%;
            white-space: nowrap;
        }

        .invoice-summary-table th,
        .invoice-summary-table td {
            padding: 3px !important;
            font-size: 11px !important;
            text-align: center;
            vertical-align: middle;
        }

        .big-change-input {
            font-size: 20px !important;
            font-weight: 900 !important;
            background-color: #e9f7ef;
            color: #00b460 !important;
        }

        .sale-btn {
            font-size: 1.5rem;
            font-weight: 600;
            padding: 5px 40px;
            box-shadow: 0 0 10px rgba(0, 128, 0, 0.4);
            transition: all 0.2s ease-in-out;
        }

        .sale-btn:hover {
            transform: scale(1.05);
            background-color: #28a745 !important;
            box-shadow: 0 0 14px rgba(40, 167, 69, 0.6);
        }

        .total-pieces-box {
            font-size: 18px;
            font-weight: 800;
        }

        .total-pieces-box span {
            font-size: 20px;
            font-weight: 900;
            color: #198754;
            margin-left: 6px;
        }

        /* Row highlight on scan */
        .qty-highlight {
            background-color: #e9fbe9 !important;
            animation: flashRow 0.8s ease-out;
        }

        @keyframes flashRow {
            from {
                background-color: #b6f2b6;
            }

            to {
                background-color: transparent;
            }
        }

        .qty-pulse {
            animation: pulseQty 0.6s ease-out;
        }

        @keyframes pulseQty {
            0% {
                box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.9);
            }

            100% {
                box-shadow: 0 0 0 10px rgba(25, 135, 84, 0);
            }
        }

        /* Brand filter badges */
        .brand-filter-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
            padding: 4px 0;
        }

        .brand-badge {
            cursor: pointer;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
            border: 2px solid #dee2e6;
            background: #fff;
            color: #495057;
            transition: all 0.18s;
            user-select: none;
        }

        .brand-badge:hover,
        .brand-badge.active {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        .brand-badge.all-badge.active {
            background: #198754;
            border-color: #198754;
            color: #fff;
        }

        /* Labour charges highlight */
        .labour-input {
            background: #fff8e1 !important;
            border-color: #ffc107 !important;
            font-weight: 700 !important;
        }

        /* Closing balance highlight in summary */
        .closing-bal-box {
            background: #fff3cd;
            border-radius: 6px;
            padding: 2px 8px;
            font-weight: 800;
            font-size: 13px;
            color: #856404;
        }

        /* Loading spinner for customer balance */
        .bal-loading {
            display: none;
            color: #aaa;
            font-size: 12px;
        }

        /* ===== COMPACT MULTI-ACCOUNT PAYMENT ===== */
        #paymentAccountsContainer,
        #paymentAmountsContainer {
            max-height: 90px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        #paymentAccountsContainer::-webkit-scrollbar,
        #paymentAmountsContainer::-webkit-scrollbar {
            width: 0;
            display: none;
        }

        /* hide scrollbar to avoid misalignment */
        .payment-row {
            margin-bottom: 4px !important;
        }

        .payment-row .pay-account-select {
            font-size: 13px !important;
            height: 28px !important;
            padding: 0 6px !important;
        }

        .payment-row .pay-amount-input {
            font-size: 14px !important;
            font-weight: 700;
            height: 28px !important;
            padding: 0 8px !important;
        }

        .payment-row .remove-payment-row {
            height: 28px !important;
            font-size: 12px !important;
            padding: 0 8px !important;
            line-height: 1;
        }

        #totalPaidLabel {
            font-size: 11px;
            display: block;
            margin-top: 3px;
        }

        #addPaymentRowBtn {
            font-size: 11px !important;
            padding: 2px 6px !important;
            height: 22px !important;
            line-height: 1;
            vertical-align: middle;
        }
    </style>

    <div class="container-fluid">
        <div class="card shadow-sm border-0 p-0 m-0">
            <form id="salesForm" action="{{ route('sales.update', $sale->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card-body pb-2">
                    {{-- Top Form --}}
                    <div class="row mb-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-1">Customer:</label>
                            <select name="customer" id="customerSelect" class="form-control form-control-sm" required>
                                <option value="Walk-in Customer" {{ $sale->customer == 'Walk-in Customer' ? 'selected' : '' }}>Walk-in Customer</option>
                                @foreach ($Customer as $c)
                                    <option value="{{ $c->id }}" {{ $sale->customer == $c->id ? 'selected' : '' }}>{{ $c->customer_name }}</option>
                                @endforeach
                            </select>
                            <span class="bal-loading" id="balLoading"><i class="fas fa-spinner fa-spin"></i>
                                Loading...</span>
                        </div>
                        <div class="col-md-3">
                            {{-- Customer Balance Info Panel --}}
                            <label class="form-label fw-bold mb-1">Remarks (Optional):</label>
                            <textarea name="remarks" class="form-control form-control-sm" rows="1"
                                placeholder="Enter remarks...">{{ $sale->remarks }}</textarea>
                        </div>
                        <div class="col-md-7">
                            <div class="customer-balance-bar" id="customerBalanceBar">
                            <div class="balance-item">
                                <span class="balance-label">👤 Customer</span>
                                <span class="cust-name-display" id="custNameDisplay">-</span>
                            </div>
                            <div class="balance-item">
                                <span class="balance-label">📊 Previous Balance</span>
                                <span class="balance-value warning" id="prevBalDisplay">Rs 0</span>
                            </div>
                            <div class="balance-item">
                                <span class="balance-label">🧾 Current Bill</span>
                                <span class="balance-value" id="currentBillDisplay">Rs 0</span>
                            </div>
                            <div class="balance-item">
                                <span class="balance-label">✅ Paid Now</span>
                                <span class="balance-value success" id="paidNowDisplay">Rs 0</span>
                            </div>
                            <div class="balance-item">
                                <span class="balance-label">🔴 Closing Balance</span>
                                <span class="balance-value danger" id="closingBalDisplay">Rs 0</span>
                            </div>
                        </div>
                        </div>
                    </div>

                    {{-- Hidden fields --}}
                    <input type="hidden" name="advance_payment" id="advance_payment" value="{{ $sale->advance_payment ?? 0 }}">
                    <input type="hidden" name="reference" value="{{ $sale->reference }}">

                    {{-- Products Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle text-center">
                            <thead>
                                <tr>
                                    <th class="product-col text-start">Product (Type to search)</th>
                                    <th>Code</th>
                                    <th style="display:none;">Brand</th>
                                    <th>Unit</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th style="width:80px;">Qty</th>
                                    <th>Total</th>
                                    <th>X</th>
                                </tr>
                            </thead>
                            <tbody id="purchaseItems">
                                @foreach($saleItems as $item)
                                <tr>
                                    <td class="product-col">
                                        <div class="product-search-wrapper">
                                            <input type="hidden" name="product_id[]" class="product_id" value="{{ $item['product_id'] }}">
                                            <input type="text" class="form-control productSearch" value="{{ $item['item_name'] }}"
                                                placeholder="Type product name..." autocomplete="off">
                                            <div class="product-dropdown"></div>
                                        </div>
                                    </td>
                                    <td><input type="text" name="item_code[]" class="form-control form-control-sm" value="{{ $item['item_code'] }}" readonly></td>
                                    <td style="display:none;"><input type="text" name="uom[]" class="form-control form-control-sm" value="{{ $item['brand'] }}" readonly></td>
                                    <td><input type="text" name="unit[]" class="form-control form-control-sm" value="{{ $item['unit'] }}" readonly></td>
                                    <td><input type="number" step="0.01" name="price[]" class="form-control form-control-sm price" value="{{ $item['price'] }}"></td>
                                    <td><input type="text" name="item_disc[]" class="form-control form-control-sm item_disc" value="{{ $item['discount'] }}" placeholder="0"></td>
                                    <td><input type="number" name="qty[]" class="form-control form-control-sm quantity" value="{{ $item['qty'] }}" min="0.01" step="0.01"></td>
                                    <td><input type="text" name="total[]" class="form-control form-control-sm row-total" value="{{ $item['total'] }}" readonly></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-row">X</button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Fixed Summary Bar --}}
                    <div class="fixed-summary-bar">
                        <table class="table table-bordered table-sm mb-0 invoice-summary-table">
                            <tr>
                                <th>BILL</th>
                                <th>ITEM DISC</th>
                                <th>EX. DISC</th>
                                <th>LABOUR</th>
                                <th>NET AMT</th>
                                <th>PREV BAL</th>
                                <th>CLOSING</th>
                                <th style="min-width:140px;">ACCOUNT</th>
                                <th style="min-width:140px;">AMOUNT <button type="button" id="addPaymentRowBtn"
                                        class="btn btn-xs btn-success ms-1 px-1 py-0" title="Add another account"><i
                                            class="las la-plus"></i></button></th>
                                <th>Change</th>
                            </tr>
                            <tr class="align-middle">
                                <td><input type="text" id="billAmount" name="total_subtotal"
                                        class="form-control form-control-sm text-center" value="{{ $sale->total_bill_amount }}" readonly></td>
                                <td><input type="text" id="itemDiscount" name="total_discount"
                                        class="form-control form-control-sm text-center" value="{{ $sale->total_items_discount ?? 0 }}" readonly></td>
                                <td><input type="number" id="extraDiscount" name="total_extra_cost"
                                        class="form-control form-control-sm text-center" value="{{ $sale->total_extradiscount }}"></td>
                                <td><input type="number" id="labourCharges" name="labour_charges"
                                        class="form-control form-control-sm text-center labour-input" value="{{ $sale->labour_charges ?? 0 }}" min="0"
                                        step="0.01" placeholder="0"></td>
                                <td><input type="text" id="netAmount" name="total_net"
                                        class="form-control form-control-sm text-center" value="{{ $sale->total_net }}" readonly></td>
                                <td><input type="text" id="prevBalBar" class="form-control form-control-sm text-center"
                                        readonly style="background:#fff8e1;font-weight:700;color:#856404;"></td>
                                <td><input type="text" id="closingBalBar" class="form-control form-control-sm text-center"
                                        readonly
                                        style="background:#fff0f0;font-weight:900;color:#c0392b;font-size:15px!important;">
                                </td>
                                <td class="align-top">
                                    <div id="paymentAccountsContainer">
                                        <div class="payment-row mb-1" data-row="0">
                                            <select name="pay_account_id[]"
                                                class="form-select form-select-sm pay-account-select w-100">
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}" {{ ($sale->account_id == $account->id || ($loop->first && !$sale->account_id)) ? 'selected' : '' }}>
                                                        {{ $account->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-top">
                                    <div id="paymentAmountsContainer">
                                        <div class="payment-row d-flex gap-1 mb-1 align-items-center" data-row="0">
                                            <input type="number" name="pay_amount[]"
                                                class="form-control form-control-sm pay-amount-input text-center w-100"
                                                placeholder="0" value="{{ $sale->cash + $sale->card }}" min="0" step="0.01">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger remove-payment-row px-1 py-0"
                                                title="Remove" style="display:none;"><i class="las la-times"></i></button>
                                        </div>
                                    </div>
                                    <input type="hidden" id="cash" name="cash" value="{{ $sale->cash }}">
                                    <input type="hidden" id="card" name="card" value="{{ $sale->card }}">
                                    <small class="text-muted d-block mt-1" id="totalPaidLabel">Total Paid: <strong>Rs
                                            {{ number_format($sale->cash + $sale->card, 2) }}</strong></small>
                                </td>
                                <td><input type="text" id="change" name="change"
                                        class="form-control big-change-input text-center" value="{{ $sale->change }}" readonly></td>
                            </tr>
                        </table>
                        <div class="d-flex justify-content-between align-items-center px-3 py-1 border-top">
                            <div class="fw-bold d-flex align-items-center gap-2">
                                <span>Amount In Words:</span>
                                <span id="amountWordsText" class="text-muted">{{ $sale->total_amount_Words }}</span>
                                <input type="hidden" name="total_amount_Words" id="amountWordsInput" value="{{ $sale->total_amount_Words }}">
                                <input type="hidden" name="total_items" id="totalItemsInput" value="{{ $sale->total_items }}">
                                <input type="hidden" name="total_pieces" value="{{ $sale->total_pieces ?? 0 }}">
                                <input type="hidden" name="total_yard" value="{{ $sale->total_yard ?? 0 }}">
                                <input type="hidden" name="total_meter" value="{{ $sale->total_meter ?? 0 }}">
                            </div>
                            <div class="total-pieces-box text-center">
                                <div>TOTAL UNITS: <span id="totalUnits">{{ $sale->total_items }}</span></div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" name="action" value="booking" class="btn btn-warning">Book</button>
                                <button type="submit" name="action" value="sale"
                                    class="btn btn-success sale-btn">Update Sale</button>
                                <a href="{{ route('sale.index') }}" class="btn btn-secondary">Close</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking - Advance Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Net Amount</label>
                        <input type="text" id="modalNetAmount" class="form-control" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Advance Payment</label>
                        <input type="number" step="0.01" id="modalAdvance" class="form-control" min="0" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="confirmBookingBtn" class="btn btn-warning">Confirm Booking</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Ledger Modal -->
    <div class="modal fade" id="customerLedgerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg,#1a1a2e,#16213e); color:#fff;">
                    <h5 class="modal-title">📒 Customer Ledger</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="ledgerModalContent" class="p-3 text-center text-muted">Select a customer to view ledger.</div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        // ===== CACHED PRODUCTS - LOAD ONCE ON PAGE LOAD =====
        let allProducts = [];
        let productsLoaded = false;
        let activeBrandFilter = 'all';
        let customerPreviousBalance = 0;

        // Load all products once on page load for instant search
        function loadAllProducts() {
            $.get("{{ route('search-product-name') }}", { q: '' }, function (res) {
                allProducts = res;
                productsLoaded = true;
                console.log('✅ Products loaded:', allProducts.length);
            });
        }

        // Quick local search from cached products with brand filter
        function searchProducts(query) {
            if (!query || query.length < 1) return [];
            const q = query.toLowerCase();

            return allProducts.filter(p => {
                const matchesQuery = (p.item_name && p.item_name.toLowerCase().includes(q)) ||
                    (p.item_code && p.item_code.toLowerCase().includes(q)) ||
                    (p.brand && p.brand.toLowerCase().includes(q));

                // apply brand filter
                if (activeBrandFilter !== 'all') {
                    return matchesQuery && p.brand && p.brand.toLowerCase() === activeBrandFilter;
                }
                return matchesQuery;
            }).slice(0, 15);
        }

        // ===== HELPER FUNCTIONS =====
        function num(n) {
            return isNaN(parseFloat(n)) ? 0 : parseFloat(n);
        }

        function formatRs(val) {
            return 'Rs ' + parseFloat(val || 0).toLocaleString('en-PK', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        function formatDrCrRs(val) {
            let num = parseFloat(val || 0);
            let absVal = Math.abs(num);
            let str = 'Rs ' + absVal.toLocaleString('en-PK', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            let suffix = num > 0 ? ' Dr' : (num < 0 ? ' Cr' : '');
            return str + suffix;
        }

        function formatDrCrNum(val) {
            let num = parseFloat(val || 0);
            let absVal = Math.abs(num);
            let str = absVal.toFixed(2);
            let suffix = num > 0 ? ' Dr' : (num < 0 ? ' Cr' : '');
            return str + suffix;
        }

        function numberToWords(num) {
            const a = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine",
                "Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen",
                "Eighteen", "Nineteen"];
            const b = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];

            if (!num || isNaN(num)) return '';
            num = Math.floor(num);
            if (num > 999999999) return "Overflow";

            const n = ("000000000" + num).slice(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{3})$/);
            if (!n) return '';

            let str = "";
            str += n[1] != 0 ? (a[n[1]] || b[n[1][0]] + " " + a[n[1][1]]) + " Crore " : "";
            str += n[2] != 0 ? (a[n[2]] || b[n[2][0]] + " " + a[n[2][1]]) + " Lakh " : "";
            str += n[3] != 0 ? (a[n[3]] || b[n[3][0]] + " " + a[n[3][1]]) + " Thousand " : "";
            str += n[4] != 0 ? (a[n[4]] || b[n[4][0]] + " " + a[n[4][1]]) : "";

            return str.trim() + " Rupees Only";
        }

        function recalcRow($row) {
            const qty = num($row.find('.quantity').val());
            const price = num($row.find('.price').val());

            let discRaw = ($row.find('.item_disc').val() || '').toString().trim();
            let totalDiscount = 0;

            if (discRaw.endsWith('%')) {
                const percent = parseFloat(discRaw);
                if (!isNaN(percent)) totalDiscount = (price * percent / 100) * qty;
            } else {
                const perQtyDisc = parseFloat(discRaw);
                if (!isNaN(perQtyDisc)) totalDiscount = perQtyDisc * qty;
            }

            let total = (qty * price) - totalDiscount;
            if (total < 0) total = 0;

            $row.find('.row-total').val(total.toFixed(2));
            $row.data('row-discount', totalDiscount);
        }

        function recalcSummary() {
            let billAmount = 0, itemDiscount = 0;
            let totalUnits = 0;

            $('#purchaseItems tr').each(function () {
                const qty = num($(this).find('.quantity').val());
                totalUnits += qty;
                billAmount += num($(this).find('.row-total').val());
                itemDiscount += num($(this).data('row-discount'));
            });

            const extraDiscount = num($('#extraDiscount').val());
            const labourCharges = num($('#labourCharges').val());
            // Sum all payment rows
            let paid = 0;
            $('.pay-amount-input').each(function () { paid += num($(this).val()); });
            const net = billAmount - extraDiscount + labourCharges;
            const change = paid - net;
            const closingBal = customerPreviousBalance + net - paid;
            // Sync hidden cash field for controller
            $('#cash').val(paid.toFixed(2));

            $('#billAmount').val(billAmount.toFixed(2));
            $('#itemDiscount').val(itemDiscount.toFixed(2));
            $('#netAmount').val(net.toFixed(2));
            $('#change').val(change.toFixed(2));

            // Update summary bar ledger columns
            $('#prevBalBar').val(customerPreviousBalance === 0 ? "0.00" : formatDrCrNum(customerPreviousBalance));
            $('#closingBalBar').val(closingBal === 0 ? "0.00" : formatDrCrNum(closingBal));

            // Update top balance panel (if visible)
            $('#currentBillDisplay').text(formatRs(net));
            $('#paidNowDisplay').text(formatRs(paid));
            $('#closingBalDisplay').text(closingBal === 0 ? "Rs 0" : formatDrCrRs(closingBal));
            if (closingBal > 0) {
                $('#closingBalDisplay').removeClass('success warning').addClass('danger');
            } else if (closingBal < 0) {
                $('#closingBalDisplay').removeClass('danger warning').addClass('success');
            } else {
                $('#closingBalDisplay').removeClass('danger success').addClass('warning');
            }

            $('#totalUnits').text(totalUnits);
            $('#totalItemsInput').val(totalUnits);

            const words = numberToWords(Math.round(net));
            $('#amountWordsText').text(words);
            $('#amountWordsInput').val(words);
        }

        // ===== LOAD CUSTOMER BALANCE =====
        function loadCustomerBalance(customerId) {
            if (!customerId || customerId === 'Walk-in Customer') {
                customerPreviousBalance = 0;
                $('#customerBalanceBar').removeClass('show');
                recalcSummary();
                return;
            }

            $('#balLoading').show();
            $.get('/sale/customer-balance/' + customerId, function (data) {
                customerPreviousBalance = parseFloat(data.closing_balance || 0);
                // Since this is EDIT, we need to adjust the previous balance to what it would be without THIS sale
                // But the backend returns CURRENT closing balance.
                // For edit, we might want to subtract this sale's current net and add back what was paid?
                // Actually, let's keep it simple as the user asked for "same as sale screen".
                const custName = $('#customerSelect option:selected').text();
                $('#custNameDisplay').text(custName);
                $('#prevBalDisplay').text(customerPreviousBalance === 0 ? "Rs 0" : formatDrCrRs(customerPreviousBalance));
                if (customerPreviousBalance > 0) {
                    $('#prevBalDisplay').removeClass('success warning').addClass('danger');
                } else {
                    $('#prevBalDisplay').removeClass('danger warning').addClass('success');
                }
                $('#customerBalanceBar').addClass('show');
                recalcSummary();
                $('#balLoading').hide();
            }).fail(function () {
                customerPreviousBalance = 0;
                $('#customerBalanceBar').removeClass('show');
                recalcSummary();
                $('#balLoading').hide();
            });
        }

        // ===== NEW ROW =====
        function appendBlankRow() {
            const newRow = `
        <tr>
            <td class="product-col">
                <div class="product-search-wrapper">
                    <input type="hidden" name="product_id[]" class="product_id">
                    <input type="text" class="form-control productSearch" placeholder="Type product name..." autocomplete="off">
                    <div class="product-dropdown"></div>
                </div>
            </td>
            <td><input type="text" name="item_code[]" class="form-control form-control-sm" readonly></td>
            <td style="display:none;"><input type="text" name="uom[]" class="form-control form-control-sm" readonly></td>
            <td><input type="text" name="unit[]" class="form-control form-control-sm" readonly></td>
            <td><input type="number" step="0.01" name="price[]" class="form-control form-control-sm price"></td>
            <td><input type="text" name="item_disc[]" class="form-control form-control-sm item_disc" placeholder="0"></td>
            <td><input type="number" name="qty[]" class="form-control form-control-sm quantity" min="1" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');"></td>
            <td><input type="text" name="total[]" class="form-control form-control-sm row-total" readonly></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-row">X</button></td>
        </tr>`;
            $('#purchaseItems').append(newRow);
            setTimeout(() => { $('#purchaseItems tr:last .productSearch').focus(); }, 10);
        }

        // ===== DOCUMENT READY =====
        $(document).ready(function () {
            // Load products on page load
            loadAllProducts();

            // Load initial customer balance
            const initialCustomerId = $('#customerSelect').val();
            loadCustomerBalance(initialCustomerId);

            // ===== CUSTOMER SELECT CHANGE =====
            $('#customerSelect').on('change', function () {
                const val = $(this).val();
                loadCustomerBalance(val);
            });

            // ===== INSTANT PRODUCT SEARCH =====
            $(document).on('input', '.productSearch', function () {
                const $input = $(this);
                const $dropdown = $input.siblings('.product-dropdown');
                const query = $input.val().trim();
                const $row = $input.closest('tr');

                // Already selected? Don't search
                if ($row.find('.product_id').val() && $input.prop('readonly')) return;

                if (query.length < 1) {
                    $dropdown.hide().empty();
                    return;
                }

                // Use cached products for instant search
                const results = searchProducts(query);

                if (results.length === 0) {
                    positionDropdown($input, $dropdown);
                    $dropdown.html('<div class="product-dropdown-item text-muted">No products found</div>').show();
                    return;
                }

                let html = '';
                results.forEach((p, index) => {
                    const priceDisplay = p.has_discount
                        ? `<span style="text-decoration:line-through">Rs ${p.original_price}</span> <span class="text-danger fw-bold">Rs ${p.price}</span>`
                        : `Rs ${p.price}`;

                    html += `
                <div class="product-dropdown-item ${index === 0 ? 'active' : ''}"
                     data-id="${p.id}"
                     data-name="${p.item_name || ''}"
                     data-code="${p.item_code || ''}"
                     data-price="${p.price}"
                     data-unit="${p.unit_id || ''}"
                     data-brand="${p.brand || ''}"
                     data-note="${p.note || ''}">
                    <div class="product-name">${p.item_name || ''}</div>
                    <div class="product-meta">${priceDisplay} | ${p.brand || '-'} | ${p.item_code || ''}</div>
                </div>`;
                });

                positionDropdown($input, $dropdown);
                $dropdown.html(html).show();
            });

            // Helper to position dropdown fixed
            function positionDropdown($input, $dropdown) {
                const rect = $input[0].getBoundingClientRect();
                $dropdown.css({
                    'position': 'fixed',
                    'top': (rect.bottom) + 'px',
                    'left': rect.left + 'px',
                    'width': rect.width + 'px',
                    'z-index': 999999,
                    'max-height': '300px',
                    'display': 'block'
                });
            }

            // Update position on scroll
            $(window).on('scroll resize', function () {
                $('.product-dropdown:visible').each(function () {
                    const $input = $(this).siblings('.productSearch');
                    if ($input.length) positionDropdown($input, $(this));
                });
            });

            // ===== SELECT PRODUCT FROM DROPDOWN =====
            $(document).on('mousedown', '.product-dropdown-item', function () {
                const $item = $(this);
                const $row = $item.closest('tr');
                const $dropdown = $item.closest('.product-dropdown');

                // Fill row data
                $row.find('.product_id').val($item.data('id'));
                $row.find('.productSearch').val($item.data('name')).prop('readonly', true).addClass('bg-light');
                $row.find('[name="item_code[]"]').val($item.data('code'));
                $row.find('[name="uom[]"]').val($item.data('brand'));
                $row.find('[name="unit[]"]').val($item.data('unit'));
                $row.find('.price').val($item.data('price'));
                $row.find('.quantity').val(1);
                $row.find('.item_disc').val(0);

                recalcRow($row);
                recalcSummary();

                $dropdown.hide().empty();
                $row.find('.quantity').focus().select();
            });

            // ===== KEYBOARD NAVIGATION =====
            $(document).on('keydown', '.productSearch', function (e) {
                const $dropdown = $(this).siblings('.product-dropdown');
                const $items = $dropdown.find('.product-dropdown-item');

                if (!$items.length) return;

                let $active = $items.filter('.active');
                let index = $items.index($active);

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    index = (index + 1) % $items.length;
                    $items.removeClass('active').eq(index).addClass('active');
                    $items.eq(index)[0].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    index = (index - 1 + $items.length) % $items.length;
                    $items.removeClass('active').eq(index).addClass('active');
                    $items.eq(index)[0].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if ($active.length) $active.trigger('mousedown');
                } else if (e.key === 'Escape') {
                    $dropdown.hide().empty();
                }
            });

            // Hide dropdown on blur
            $(document).on('blur', '.productSearch', function () {
                setTimeout(() => { $(this).siblings('.product-dropdown').hide(); }, 200);
            });

            // ===== QUANTITY ENTER -> NEW ROW =====
            $(document).on('keydown', '.quantity', function (e) {
                if (e.key !== 'Enter') return;
                e.preventDefault();

                const $row = $(this).closest('tr');
                if (!$row.find('.product_id').val()) {
                    alert('Please select a product first');
                    return;
                }

                // Check if empty row exists
                let emptyRow = $('#purchaseItems tr').filter(function () {
                    return !$(this).find('.product_id').val();
                }).first();

                if (emptyRow.length) {
                    emptyRow.find('.productSearch').focus();
                } else {
                    appendBlankRow();
                }
            });

            // ===== ROW CALCULATIONS =====
            $(document).on('input', '.quantity, .price, .item_disc, #extraDiscount, #labourCharges', function () {
                const $row = $(this).closest('tr');
                if ($row.length && $row.find('.quantity').length) recalcRow($row);
                recalcSummary();
            });

            // ===== ADD PAYMENT ROW =====
            const accountOptions = `@foreach($accounts as $account)<option value="{{ $account->id }}">{{ $account->title }}</option>@endforeach`;
            let paymentRowId = 1;

            $('#addPaymentRowBtn').on('click', function () {
                const rowId = paymentRowId++;
                const accRow = `
            <div class="payment-row mb-1" data-row="${rowId}">
                <select name="pay_account_id[]" class="form-select form-select-sm pay-account-select w-100">${accountOptions}</select>
            </div>`;
                const amtRow = `
            <div class="payment-row d-flex gap-1 mb-1 align-items-center" data-row="${rowId}">
                <input type="number" name="pay_amount[]" class="form-control form-control-sm pay-amount-input text-center w-100" placeholder="0" value="0" min="0" step="0.01">
                <button type="button" class="btn btn-sm btn-outline-danger remove-payment-row px-1 py-0" title="Remove"><i class="las la-times"></i></button>
            </div>`;
                $('#paymentAccountsContainer').append(accRow);
                $('#paymentAmountsContainer').append(amtRow);

                updateRemoveBtnVisibility();
                recalcSummary();
            });

            // ===== REMOVE PAYMENT ROW =====
            $(document).on('click', '.remove-payment-row', function () {
                if ($('#paymentAmountsContainer .payment-row').length > 1) {
                    const rowId = $(this).closest('.payment-row').data('row');
                    $('#paymentAccountsContainer .payment-row[data-row="' + rowId + '"]').remove();
                    $('#paymentAmountsContainer .payment-row[data-row="' + rowId + '"]').remove();
                    updateRemoveBtnVisibility();
                    recalcSummary();
                }
            });

            // ===== PAYMENT AMOUNT INPUT =====
            $(document).on('input', '.pay-amount-input', function () {
                recalcSummary();
            });

            function updateRemoveBtnVisibility() {
                const rows = $('#paymentAmountsContainer .payment-row');
                if (rows.length <= 1) {
                    rows.find('.remove-payment-row').hide();
                } else {
                    rows.find('.remove-payment-row').show();
                }
            }

            // Sync totalPaidLabel
            const origRecalc = recalcSummary;
            recalcSummary = function () {
                origRecalc();
                let paid = 0;
                $('.pay-amount-input').each(function () { paid += num($(this).val()); });
                $('#totalPaidLabel strong').text('Rs ' + paid.toLocaleString('en-PK', { minimumFractionDigits: 0, maximumFractionDigits: 2 }));
            };

            // ===== REMOVE ROW =====
            $(document).on('click', '.remove-row', function () {
                const $tbody = $('#purchaseItems');
                if ($tbody.find('tr').length > 1) {
                    $(this).closest('tr').remove();
                    recalcSummary();
                } else {
                    const $row = $(this).closest('tr');
                    $row.find('input, textarea').val('');
                    $row.find('.product_id').val('');
                    $row.find('.productSearch').prop('readonly', false).removeClass('bg-light').focus();
                    recalcSummary();
                }
            });

            // Initial calculation for all rows
            $('#purchaseItems tr').each(function(){
                recalcRow($(this));
            });
            recalcSummary();

            // ===== BOOKING MODAL =====
            $(document).on('click', 'button[name="action"][value="booking"]', function (e) {
                e.preventDefault();
                const bill = parseFloat($('#billAmount').val()) || 0;
                const extra = parseFloat($('#extraDiscount').val()) || 0;
                const labour = parseFloat($('#labourCharges').val()) || 0;
                const net = bill - extra + labour;
                $('#modalNetAmount').val(net.toFixed(2));
                $('#modalAdvance').val($('#advance_payment').val());
                var bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
                bookingModal.show();
            });

            $('#confirmBookingBtn').on('click', function () {
                const net = parseFloat($('#modalNetAmount').val()) || 0;
                let advance = parseFloat($('#modalAdvance').val()) || 0;

                if (advance < 0) { alert('Advance cannot be negative'); return; }
                if (advance > net && !confirm('Advance is more than Net amount. Continue?')) return;

                $('#advance_payment').val(advance.toFixed(2));

                const $form = $('#salesForm');
                if ($form.find('input[name="action"]').length === 0) {
                    $('<input>').attr({ type: 'hidden', name: 'action', value: 'booking' }).appendTo($form);
                } else {
                    $form.find('input[name="action"]').val('booking');
                }
                $form.submit();
            });

            // ===== FORM SUBMISSION VALIDATION =====
            $('#salesForm').on('submit', function (e) {
                let action = $(document.activeElement).val();
                if (!action) action = $('input[name="action"]').val();

                const customer = $('#customerSelect').val();

                if (action === 'sale' && customer === 'Walk-in Customer') {
                    const net = parseFloat($('#netAmount').val()) || 0;
                    let paid = 0;
                    $('.pay-amount-input').each(function () { paid += num($(this).val()); });

                    if (paid < net - 0.01) {
                        e.preventDefault();
                        alert('For Walk-in Customer, full payment is required!\nNet Amount: Rs ' + net.toFixed(2) + '\nPaid Amount: Rs ' + paid.toFixed(2));
                        $('.pay-amount-input:first').focus().select();
                        return false;
                    }
                }
            });
        });
    </script>
@endsection