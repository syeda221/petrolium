@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container">
            <div class="row g-3">
                <!-- Categories -->
                <div class="col-md-3 mt-2">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Categories</h6>
                                <h3 class="mb-0 fw-bold">{{ $categoryCount }}</h3>
                            </div>
                            <div class="icon text-primary">
                                <i class="fas fa-layer-group fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subcategories -->
                <div class="col-md-3 mt-2">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Subcategories</h6>
                                <h3 class="mb-0 fw-bold">{{ $subcategoryCount }}</h3>
                            </div>
                            <div class="icon text-success">
                                <i class="fas fa-sitemap fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div class="col-md-3 mt-2">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Products</h6>
                                <h3 class="mb-0 fw-bold">{{ $productCount }}</h3>
                            </div>
                            <div class="icon text-danger">
                                <i class="fas fa-box-open fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Example for future (e.g. Orders) -->
                <div class="col-md-3 mt-2">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Customers</h6>
                                <h3 class="mb-0 fw-bold">{{ $customerscount }}</h3>
                            </div>
                            <div class="icon text-warning">
                                <i class="fas fa-shopping-cart fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Total Purchases -->
                <div class="col-md-3 mt-2">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Total Purchases</h6>
                                <h5 class="mb-0 fw-bold">Rs {{ number_format($totalPurchases, 2) }}</h5>
                            </div>
                            <div class="icon text-primary">
                                <i class="fas fa-file-invoice-dollar fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Returns -->
                <div class="col-md-3 mt-2">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Purchase Returns</h6>
                                <h5 class="mb-0 fw-bold">Rs {{ number_format($totalPurchaseReturns, 2) }}</h5>
                            </div>
                            <div class="icon text-danger">
                                <i class="fas fa-undo-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Sales -->
                <div class="col-md-3 mt-2">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Total Sales</h6>
                                <h5 class="mb-0 fw-bold">Rs {{ number_format($totalSales, 2) }}</h5>
                            </div>
                            <div class="icon text-success">
                                <i class="fas fa-shopping-cart fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Returns -->
                <div class="col-md-3 mt-2">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">Sales Returns</h6>
                                <h5 class="mb-0 fw-bold">Rs {{ number_format($totalSalesReturns, 2) }}</h5>
                            </div>
                            <div class="icon text-warning">
                                <i class="fas fa-undo fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row mt-4">
                <div class="col-md-12 mb-4">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Sales Report</h6>
                            <label for="salesFilter" class="form-label fw-bold">Sales Report Filter:</label>
                            <select id="salesFilter" class="form-select w-auto">
                                <option value="daily" selected>Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="card-body">
                            <div id="salesReportChart" style="height: 400px;" class="bg-white rounded shadow-sm"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12 mb-4">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Purchase Report</h6>
                            <label for="purchaseFilter" class="form-label fw-bold">Purchase Report Filter:</label>
                            <select id="purchaseFilter" class="form-select w-auto">
                                <option value="daily" selected>Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="card-body">
                            <div id="purchaseReportChart" style="height: 400px;" class="bg-white rounded shadow-sm"></div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- WAREHOUSE STOCK VALUE REPORT -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h6 class="mb-0 fw-bold text-white">
                        <i class="fas fa-warehouse me-2"></i>
                        Warehouse Stock Value Report
                    </h6>
                    <small class="text-white opacity-75">
                        Stock quantity and value (in rupees) per warehouse
                    </small>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 35%">Warehouse Name</th>
                                    <th class="text-center" style="width: 30%">Total Stock Quantity</th>
                                    <th class="text-end" style="width: 30%">Total Value (PKR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $totalQty = 0;
                                    $totalValue = 0;
                                @endphp
                                @foreach($warehouseStockData as $index => $warehouse)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong class="text-primary">{{ $warehouse->warehouse_name }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info fs-6">{{ number_format($warehouse->total_quantity) }} Units</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-success">₨ {{ number_format($warehouse->total_value, 2) }}</span>
                                    </td>
                                </tr>
                                @php
                                    $totalQty += $warehouse->total_quantity;
                                    $totalValue += $warehouse->total_value;
                                @endphp
                                @endforeach
                            </tbody>
                            <tfoot class="table-dark">
                                <tr>
                                    <th colspan="2" class="text-end">Grand Total:</th>
                                    <th class="text-center">
                                        <span class="badge bg-warning text-dark fs-6">{{ number_format($totalQty) }} Units</span>
                                    </th>
                                    <th class="text-end">
                                        <span class="fw-bold text-white">₨ {{ number_format($totalValue, 2) }}</span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Chart -->
                    <div class="mt-4">
                        <h6 class="text-muted mb-3">Visual Comparison</h6>
                        <div id="warehouseValueChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>


            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">
                        Category Wise Product Stock
                    </h6>
                    <small class="text-muted">
                        Stock summary of all products by category
                    </small>
                </div>

                <div class="card-body">
                    <div id="categoryStockChart"></div>
                </div>
            </div>


            <div class="modal fade" id="categoryProductsModal" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 id="modalTitle"></h5>
                            <button class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <!-- 🔍 SEARCH -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <input type="text"
                                        id="productSearch"
                                        class="form-control"
                                        placeholder="Search product..."
                                        onkeyup="searchProducts()">
                                </div>
                            </div>

                            <!-- TABLE -->
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product Name</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody id="productsTableBody"></tbody>
                            </table>

                            <!-- PAGINATION -->
                            <nav>
                                <ul class="pagination justify-content-center" id="pagination"></ul>
                            </nav>

                        </div>
                    </div>
                </div>
            </div>






        </div>
    </div>
</div>
@endsection
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

@section('scripts')
<script>
    // PHP se JS me data pass kar rahe
    const salesChartStats = @json($salesChartStats);

    let chart;

    function renderSalesChart(type = 'daily') {
        const options = {
            chart: {
                type: 'bar',
                height: 400,
                toolbar: {
                    show: true
                },
            },
            series: salesChartStats[type].series,
            xaxis: {
                categories: salesChartStats[type].categories
            },
            dataLabels: {
                enabled: true
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '50%'
                }
            },
            tooltip: {
                y: {
                    formatter: val => '$' + val.toLocaleString()
                }
            },
            colors: ['#0d6efd']
        };

        if (chart) chart.destroy();
        chart = new ApexCharts(document.querySelector("#salesReportChart"), options);
        chart.render();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initial render
        renderSalesChart();

        // Handle filter change
        const filter = document.getElementById('salesFilter');
        if (filter) {
            filter.addEventListener('change', function() {
                renderSalesChart(this.value);
            });
        }
    });

    // PHP data to JS
    const purchaseChartStats = @json($purchaseChartStats);

    let purchaseChart;

    function renderPurchaseChart(type = 'daily') {
        const options = {
            chart: {
                type: 'bar',
                height: 400,
                toolbar: {
                    show: true
                },
            },
            series: purchaseChartStats[type].series,
            xaxis: {
                categories: purchaseChartStats[type].categories
            },
            dataLabels: {
                enabled: true
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '50%'
                }
            },
            tooltip: {
                y: {
                    formatter: val => 'PKR' + parseFloat(val).toLocaleString()
                }
            },
            colors: ['#198754'] // green color for purchase
        };

        if (purchaseChart) purchaseChart.destroy();
        purchaseChart = new ApexCharts(document.querySelector("#purchaseReportChart"), options);
        purchaseChart.render();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initial render
        renderPurchaseChart();

        // Handle filter change
        const filter = document.getElementById('purchaseFilter');
        if (filter) {
            filter.addEventListener('change', function() {
                renderPurchaseChart(this.value);
            });
        }
    });


    const categoryStockData = @json($categoryProductChart);

    document.addEventListener('DOMContentLoaded', function() {

        const options = {
            chart: {
                type: 'bar',
                height: 420,
                toolbar: {
                    show: false
                },
                events: {
                    dataPointSelection: function(event, chartContext, config) {
                        const categoryId = categoryStockData.category_ids[config.dataPointIndex];
                        const categoryName = categoryStockData.categories[config.dataPointIndex];
                        loadCategoryProducts(categoryId, categoryName);
                    }
                }
            },

            series: categoryStockData.series,

            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '50%',
                    borderRadius: 6
                }
            },

            xaxis: {
                categories: categoryStockData.categories,
                labels: {
                    style: {
                        fontSize: '13px',
                        fontWeight: 600
                    }
                }
            },

            yaxis: {
                labels: {
                    formatter: val => val.toLocaleString()
                }
            },

            tooltip: {
                y: {
                    formatter: val => `${val.toLocaleString()} Products`
                }
            },

            colors: ['#0d6efd'],

            grid: {
                borderColor: '#eee'
            }
        };

        new ApexCharts(
            document.querySelector("#categoryStockChart"),
            options
        ).render();
    });

    let activeCategoryId = null;
    let activeCategoryName = '';

    function loadCategoryProducts(categoryId, categoryName, page = 1) {

        activeCategoryId = categoryId;
        activeCategoryName = categoryName;

        $('#modalTitle').text(categoryName + ' – Products');
        $('#categoryProductsModal').modal('show');

        let search = $('#productSearch').val();

        $.get(`/category-products/${categoryId}`, {
            page: page,
            search: search
        }, function(res) {

            let rows = '';
            res.data.forEach((item, index) => {
                rows += `
                <tr>
                    <td>${((res.current_page - 1) * 100) + index + 1}</td>
                    <td>${item.item_name}</td>
                    <td>${item.stock}</td>
                </tr>
            `;
            });

            $('#productsTableBody').html(rows);

            // Pagination
            let pagination = '';
            for (let i = 1; i <= res.last_page; i++) {
                pagination += `
                <li class="page-item ${i === res.current_page ? 'active' : ''}">
                    <a class="page-link"
                       href="#"
                       onclick="loadCategoryProducts(${categoryId}, '${categoryName}', ${i})">
                       ${i}
                    </a>
                </li>
            `;
            }

            $('#pagination').html(pagination);
        });
    }

    // 🔍 SEARCH HANDLER
    function searchProducts() {
        loadCategoryProducts(activeCategoryId, activeCategoryName, 1);
    }

    // ===== WAREHOUSE STOCK VALUE CHART =====
    const warehouseStockChart = @json($warehouseStockChart);

    document.addEventListener('DOMContentLoaded', function() {
        const options = {
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: true
                }
            },
            series: [
                {
                    name: 'Stock Quantity',
                    data: warehouseStockChart.quantities
                },
                {
                    name: 'Stock Value (PKR)',
                    data: warehouseStockChart.values
                }
            ],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 5
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val.toLocaleString();
                }
            },
            xaxis: {
                categories: warehouseStockChart.warehouses,
                labels: {
                    style: {
                        fontSize: '12px',
                        fontWeight: 600
                    }
                }
            },
            yaxis: [
                {
                    title: {
                        text: 'Stock Quantity (Units)'
                    },
                    labels: {
                        formatter: val => val.toLocaleString()
                    }
                },
                {
                    opposite: true,
                    title: {
                        text: 'Value (PKR)'
                    },
                    labels: {
                        formatter: val => 'PKR ' + val.toLocaleString()
                    }
                }
            ],
            tooltip: {
                y: [
                    {
                        formatter: val => val.toLocaleString() + ' Units'
                    },
                    {
                        formatter: val => 'PKR ' + val.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})
                    }
                ]
            },
            colors: ['#4f46e5', '#10b981'],
            legend: {
                position: 'top'
            },
            grid: {
                borderColor: '#e0e0e0'
            }
        };

        new ApexCharts(
            document.querySelector("#warehouseValueChart"),
            options
        ).render();
    });
</script>
@endsection