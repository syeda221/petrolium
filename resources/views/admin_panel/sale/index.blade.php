@extends('admin_panel.layout.app')
@section('content')

<style>
    /* Clean Modern Table Styling */
    .sales-table {
        font-size: 13px;
    }
    .sales-table thead th {
        background-color: #37a371 !important;
        color: #fff !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border: none !important;
        padding: 12px 10px;
        vertical-align: middle;
    }
    .sales-table tbody td {
        padding: 10px;
        vertical-align: middle;
        border-bottom: 1px solid #eee;
    }
    .sales-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Date/Time Styling */
    .date-time-cell {
        line-height: 1.5;
    }
    .date-time-cell .date {
        font-weight: 600;
        color: #333;
    }
    .date-time-cell .time {
        font-size: 11px;
        color: #888;
    }

    /* Action Dropdown */
    .action-dropdown .dropdown-toggle {
        background: #37a371;
        color: #fff;
        border: none;
        padding: 5px 12px;
        font-size: 12px;
        border-radius: 4px;
    }
    .action-dropdown .dropdown-toggle:hover,
    .action-dropdown .dropdown-toggle:focus {
        background: #37a371;
        box-shadow: none;
    }
    .action-dropdown .dropdown-toggle::after {
        margin-left: 6px;
    }
    .action-dropdown .dropdown-menu {
        min-width: 120px;
        padding: 5px 0;
        border-radius: 6px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        border: none;
    }
    .action-dropdown .dropdown-item {
        font-size: 12px;
        padding: 8px 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .action-dropdown .dropdown-item i {
        width: 16px;
        text-align: center;
    }
    .action-dropdown .dropdown-item:hover {
        background-color: #f0f0f0;
    }
    .action-dropdown .dropdown-item.text-warning { color: #ffc107 !important; }
    .action-dropdown .dropdown-item.text-primary { color: #0d6efd !important; }
    .action-dropdown .dropdown-item.text-info { color: #0dcaf0 !important; }
    .action-dropdown .dropdown-item.text-success { color: #198754 !important; }

    /* Status Badge */
    .status-badge {
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 20px;
    }

    /* Header Buttons */
    .header-actions .btn {
        font-size: 13px;
        padding: 6px 14px;
        border-radius: 5px;
    }

    /* Product List */
    .product-list {
        max-height: 80px;
        overflow-y: auto;
        font-size: 12px;
        line-height: 1.6;
    }
    .product-list::-webkit-scrollbar {
        width: 4px;
    }
    .product-list::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 2px;
    }
</style>

<div class="container-fluid">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-shopping-cart me-2" style="color: #37a371;"></i>Sales
            </h5>
            <div class="header-actions d-flex gap-2">
                <a href="{{ route('sale.add') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> New Sale
                </a>
                <a href="{{ url('bookings') }}" class="btn btn-outline-primary">
                    <i class="fas fa-calendar-check me-1"></i> Bookings
                </a>
                <a href="{{ url('sale-returns') }}" class="btn btn-outline-warning">
                    <i class="fas fa-undo me-1"></i> Returns
                </a>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="productTable" class="table sales-table align-middle nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Products</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                        @php
                            $products = explode(',', $sale->product);
                            $qtys = explode(',', $sale->qty);
                            $totalItems = array_sum(array_map('intval', $qtys));
                        @endphp
                        <tr>
                            <td><strong>{{ $sale->id }}</strong></td>
                            <td>
                                <div class="fw-semibold">{{ optional($sale->customer_relation)->customer_name ?? 'Walk-in Customer' }}</div>
                                @if($sale->reference)
                                <small class="text-muted">Ref: {{ $sale->reference }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="product-list">
                                    @foreach($products as $index => $p)
                                        @php $product = \App\Models\Product::find($p); @endphp
                                        @if($product)
                                        <div>{{ $product->item_name }} <span class="text-muted">({{ $qtys[$index] ?? 1 }})</span></div>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $totalItems }} items</span>
                            </td>
                            <td>
                                <strong style="color: #37a371;">Rs {{ number_format($sale->total_bill_amount, 0) }}</strong>
                            </td>
                            <td class="date-time-cell">
                                <div class="date">{{ \Carbon\Carbon::parse($sale->created_at)->format('d M, Y') }}</div>
                                <div class="time">{{ \Carbon\Carbon::parse($sale->created_at)->format('h:i A') }}</div>
                            </td>
                            <td>
                                @if($sale->sale_status === null)
                                    <span class="status-badge bg-success text-white">Completed</span>
                                @elseif($sale->sale_status == 1)
                                    <span class="status-badge bg-danger text-white">Returned</span>
                                @else
                                    <span class="status-badge bg-secondary text-white">Unknown</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="dropdown action-dropdown dropstart">
                                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-toggle="dropdown" aria-expanded="false" data-boundary="viewport">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('sales.invoice', $sale->id) }}">
                                                <i class="fas fa-print text-danger"></i> Termal Invoice (Black Copper)
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('sales.invoice_a4', $sale->id) }}">
                                                <i class="fas fa-file-pdf text-primary"></i> A4 Invoice
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('sales.edit', $sale->id) }}">
                                                <i class="fas fa-edit text-primary"></i> Edit
                                            </a>
                                        </li>
                                        <!-- <li>
                                            <a class="dropdown-item" href="{{ route('sales.dc', $sale->id) }}">
                                                <i class="fas fa-truck text-success"></i> DC
                                            </a>
                                        </li> -->
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-warning" href="{{ route('sales.return.create', $sale->id) }}">
                                                <i class="fas fa-undo"></i> Return
                                            </a>
                                        </li>
                                    </ul>
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
        $('#productTable').DataTable({
            responsive: true,
            pageLength: 15,
            lengthMenu: [
                [15, 30, 50, -1],
                [15, 30, 50, "All"]
            ],
            order: [
                [0, 'desc'] // Latest ID first
            ],
            language: {
                search: "",
                searchPlaceholder: "Search sales...",
                lengthMenu: "Show _MENU_",
                info: "Showing _START_ to _END_ of _TOTAL_ sales",
            },
            dom: '<"row align-items-center"<"col-md-6"l><"col-md-6"f>>rtip',
        });

        // Style the search input
        $('.dataTables_filter input').addClass('form-control form-control-sm').css('width', '200px');
        $('.dataTables_length select').addClass('form-select form-select-sm').css('width', 'auto');
    });
</script>
@endsection