@extends('admin_panel.layout.app')
<style>
    .return-cell {
        max-width: 180px;
        max-height: 80px;
        overflow-y: auto;
        overflow-x: hidden;
        white-space: normal;
        font-size: 12px;
        line-height: 1.4;
        background: #fafafa;
        border-radius: 4px;
        padding: 4px;
        scrollbar-width: thin;
    }

    .return-cell::-webkit-scrollbar {
        width: 5px;
    }

    .return-cell::-webkit-scrollbar-thumb {
        background-color: #ccc;
        border-radius: 3px;
    }
</style>

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <div class="page-header row mb-3">
                <div class="page-title col-lg-6">
                    <h4>Sale Report</h4>
                    <h6>View Sales by date range with details</h6>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form id="SaleFilterForm" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-primary w-100">Search</button>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" id="btnExportCsv" class="btn btn-danger">Export CSV</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="loader" style="display:none;text-align:center;margin-bottom:10px;">
                        <div class="spinner-border" role="status"></div>
                    </div>

                    <div class="table-responsive">
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered" id="saleReport">
                                <thead class="bg-gray">
                                    <tr>
                                        <th>#</th>
                                        <th style="width:160px!important;">Date | Time</th>
                                        <th>Invoice</th>
                                        <th>Customer</th>
                                        <th>Reference</th>
                                        <th>Products</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Net</th>
                                        <th>Returns</th>
                                    </tr>
                                </thead>
                                <tbody id="saleBody"></tbody>
                            </table>
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
    $(document).on('click', '#btnSearch', function() {
        let start = $('#start_date').val();
        let end = $('#end_date').val();

        $("#loader").show();
        $.ajax({
            url: "{{ route('report.sale.fetch') }}",
            type: "GET",
            data: {
                start_date: start,
                end_date: end
            },
            success: function(res) {
                $("#loader").hide();
                let html = "";

                // helpers
                function num(v) {
                    if (v === null || v === undefined) return 0;
                    if (typeof v === 'number') return v;
                    // remove any non numeric except dot and minus
                    v = String(v).replace(/[^0-9.\-]/g, '');
                    const f = parseFloat(v);
                    return isNaN(f) ? 0 : f;
                }

                function sumArray(arr) {
                    return arr.reduce((a, b) => a + num(b), 0);
                }

                function formatDate(dateString) {
                    if (!dateString) return '-';

                    const d = new Date(dateString);
                    if (isNaN(d)) return dateString;

                    const day = String(d.getDate()).padStart(2, '0');
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const year = d.getFullYear();

                    let hours = d.getHours();
                    const minutes = String(d.getMinutes()).padStart(2, '0');
                    const ampm = hours >= 12 ? 'PM' : 'AM';

                    hours = hours % 12;
                    hours = hours ? hours : 12; // 0 ko 12 bana do
                    hours = String(hours).padStart(2, '0');

                    return `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;
                }
                let grandQty = 0,
                    grandTotal = 0,
                    grandNet = 0,
                    grandReturnQty = 0,
                    grandReturnAmount = 0;

                (res || []).forEach((s, i) => {
                    // Products: if product_names field present (comma separated) else '-' 
                    let products = '-';
                    if (s.product_names) {
                        // keep line breaks for CSV/HTML view
                        products = s.product_names.split(',').map(p => p.trim()).join('<br>');
                    }

                    // Parse qty array safely (qty may be comma-separated string)
                    const qtyArrRaw = (s.qty || '').toString().trim();
                    const qtyArr = qtyArrRaw.length ? qtyArrRaw.split(',').map(x => x.trim()) : [];
                    const qtyArrNums = qtyArr.map(num);
                    const rowQty = sumArray(qtyArrNums);
                    grandQty += rowQty;

                    // Price display (per item). Keep each item on new line for UI
                    const priceDisplay = (s.per_price || '').toString().trim().length ?
                        s.per_price.toString().split(',').map(p => p.trim()).join('<br>') : '-';

                    // Total per item (could be comma-separated)
                    const perTotalRaw = (s.per_total || '').toString().trim();
                    const perTotalArr = perTotalRaw.length ? perTotalRaw.split(',').map(x => x.trim()) : [];
                    const perTotalNums = perTotalArr.map(num);
                    const rowTotal = sumArray(perTotalNums);
                    grandTotal += rowTotal;

                    // net (row-level) - use s.total_net if provided, else fallback to rowTotal
                    const rowNet = num(s.total_net) || rowTotal;
                    grandNet += rowNet;

                    // Returns: handle multiple shapes
                    let returnHtml = '';
                    let returnQtyTotal = 0;
                    let returnAmountTotal = 0;

                    if (s.returns) {
                        // If it's already an array of objects
                        if (Array.isArray(s.returns)) {
                            const lines = s.returns.map(r => {
                                const rQty = num(r.qty);
                                const rAmt = num(r.per_total || r.amount || r.total);
                                returnQtyTotal += rQty;
                                returnAmountTotal += rAmt;
                                return `${(r.product || '').toString().trim()} (${rQty}) - ${rAmt.toFixed(2)}`;
                            });
                            returnHtml = lines.join('<br>');
                        } else {
                            // maybe string: try JSON parse, else comma-separated lines like "prod|qty|amt;..."
                            let parsed = null;
                            try {
                                parsed = JSON.parse(s.returns);
                            } catch (e) {
                                parsed = null;
                            }

                            if (Array.isArray(parsed)) {
                                const lines = parsed.map(r => {
                                    const rQty = num(r.qty);
                                    const rAmt = num(r.per_total || r.amount || r.total);
                                    returnQtyTotal += rQty;
                                    returnAmountTotal += rAmt;
                                    return `${(r.product || '').toString().trim()} (${rQty}) - ${rAmt.toFixed(2)}`;
                                });
                                returnHtml = lines.join('<br>');
                            } else {
                                // fallback: show raw returns string but try to extract numbers
                                const raw = s.returns.toString();
                                // Try to split by newline or semicolon
                                const candidates = raw.split(/\r?\n|;|\|/).map(r => r.trim()).filter(Boolean);
                                const lines = candidates.map(c => {
                                    // try extract product, qty, amount using regex
                                    const m = c.match(/(.+?)\s*\(?(\d+\.?\d*)\)?\s*[-:]\s*([0-9.,]+)/);
                                    if (m) {
                                        const prod = m[1].trim();
                                        const rQty = num(m[2]);
                                        const rAmt = num(m[3]);
                                        returnQtyTotal += rQty;
                                        returnAmountTotal += rAmt;
                                        return `${prod} (${rQty}) - ${rAmt.toFixed(2)}`;
                                    } else {
                                        // if cannot parse, just show the chunk
                                        return c;
                                    }
                                });
                                returnHtml = lines.join('<br>');
                            }
                        }
                    }

                    grandReturnQty += returnQtyTotal;
                    grandReturnAmount += returnAmountTotal;

                    // Prepare HTML for this row
                    const createdAt = s.created_at || s.date || s.sale_date || s.created || '';

                    // Price/Total columns will show items on separate lines (if multiple)
                    const totalDisplay = perTotalArr.length ? perTotalArr.map(x => {
                        const v = num(x);
                        return v ? v.toFixed(2) : '0.00';
                    }).join('<br>') : (num(s.per_total) ? num(s.per_total).toFixed(2) : '-');

                    html += `<tr>
                        <td>${i+1}</td>
                        <td>${formatDate(createdAt)}</td>
                        <td>INVSLE-${s.id ?? ''}</td>
                        <td>${s.customer_name ?? '-'}</td>
                        <td>${s.reference ?? '-'}</td>
                        <td>${products}</td>
                        <td>${qtyArr.length ? qtyArr.map(x => (num(x) ? num(x).toFixed(2) : '0.00')).join('<br>') : '-'}</td>
                        <td>${priceDisplay}</td>
                        <td>${totalDisplay}</td>
                        <td>${rowNet.toFixed(2)}</td>
                        <td><div class="return-cell">${returnHtml || '-'}</div></td>
                    </tr>`;
                });

                // Grand total row
                html += `<tr class="fw-bold">
                    <td colspan="6" class="text-end">Grand Total:</td>
                    <td>${grandQty.toFixed(2)}</td>
                    <td>-</td>
                    <td>${grandTotal.toFixed(2)}</td>
                    <td>${grandNet.toFixed(2)}</td>
                    <td>Qty: ${grandReturnQty.toFixed(2)}<br>ReturnAmt: ${grandReturnAmount.toFixed(2)}</td>
                </tr>`;

                $('#saleBody').html(html);
            },
            error: function() {
                $("#loader").hide();
                alert('Failed to fetch report. Please try again.');
            }
        });
    });

    // Ensure DOM is loaded
    $(document).ready(function() {
        // CSV export
        $(document).on('click', '#btnExportCsv', function() {
            // build CSV from currently rendered table
            let csv = [];
            $("#saleReport tr").each(function() {
                let row = [];
                $(this).find('th,td').each(function() {
                    let cellHtml = $(this).html();

                    // <br> ko " | " se replace kardo
                    let cellText = cellHtml
                        .replace(/<br\s*\/?>/gi, " | ")
                        .replace(/&nbsp;/gi, " ")
                        .replace(/<[^>]*>/g, "")
                        .trim();

                    row.push('"' + cellText.replace(/"/g, '""') + '"');
                });
                csv.push(row.join(","));
            });

            let csvString = csv.join("\n");
            let blob = new Blob([csvString], {
                type: 'text/csv;charset=utf-8;'
            });

            let link = document.createElement("a");
            if (link.download !== undefined) {
                let url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", "sale_report.csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } else {
                alert('CSV download not supported in this browser.');
            }
        });
    });
</script>


@endsection