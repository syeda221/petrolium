<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Receipt</title>
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            font-family: 'Courier New', monospace;
            font-weight: 800;
            /* 🔥 MAIN FIX */
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background: #fff;
        }

        .bold,
        strong,
        h1,
        h2,
        h3,
        h4 {
            font-weight: 900 !important;
        }

        table,
        th,
        td,
        p,
        span {
            font-weight: 800;
        }

        .receipt-container {
            width: 100%;
            max-width: 340px;
            margin: auto;
            padding: 10px;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 2px 0;
        }

        th {
            text-align: left;
            font-size: 11px;
        }

        td {
            font-size: 11px;
        }

        td:last-child,
        th:last-child {
            text-align: right;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            margin-top: 6px;
            border-top: 1px dashed #000;
            padding-top: 4px;
        }

        @media print {
            body {
                font-weight: 800 !important;
                color: #000 !important;
            }

            th,
            td,
            p,
            span {
                font-weight: 800 !important;
            }

            .bold,
            strong {
                font-weight: 900 !important;
            }
        }
    </style>
</head>

<body>

    <div class="receipt-container">

        <!-- Header -->
        <div class="center">
            <h2 style="margin:0;font-size:14px;" class="bold">Al-Owais Petroleum Service </h2>
            <p style="margin:0;">Al-Owais Petroleum Service</p>
            <p style="margin:0;">Tower Market Near Ptcl office Hyderabad</p>
            <p style="margin:0;">Phone: 0333-3544684 | 0345-6333940</p>
        </div>

        <div class="line"></div>
        <div class="center bold">Return Invoice</div>
        <div class="line"></div>

        <!-- Details -->
        <table>
            <tr>
                <th>Date</th>
                <td>{{ $return->created_at->format('d-m-Y') ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Customer</th>
                <td>{{ $return->sale->customer_relation->customer_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Reference</th>
                <td>{{ $return->reference ?? 'N/A' }}</td>
            </tr>

        </table>

        <div class="line"></div>

        <!-- Items -->
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($returnItems as $item)
                    <tr>
                        <td>{{ $item['item_name'] }}</td>
                        <td>{{ $item['qty'] }}</td>
                        <td>{{ number_format($item['price'], 2) }}</td>
                        <td class="bold">{{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endforeach

            </tbody>
        </table>

        <div class="line"></div>

        <!-- Totals -->
        <table class="totals">
            <tr>
                <th>Total Items</th>
                <td>{{ $return->total_items }}</td>
            </tr>
            <tr>
                <th>Grand Total</th>
                <td>{{ number_format($return->total_bill_amount, 2) }}</td>
            </tr>
            <tr>
                <th>Extra Discount</th>
                <td>{{ number_format($return->total_extradiscount, 2) }}</td>
            </tr>
            <tr>
                <th>Net Amount</th>
                <td class="bold">{{ number_format($return->total_net, 2) }}</td>
            </tr>
        </table>



        <!-- Footer -->
        <div class="footer">

            <p>*** Thank you for the visit
                ***</p>
        </div>
    </div>

    <script>

        window.onload = function () {
            window.print();
        };

        // optional: print ke baad tab close karna ho
        window.onafterprint = function () {
            window.close();
        };

        // window.onload = function () {
        //     window.print();
        //     setTimeout(function () {
        //         window.location.href = "{{ route('sale.index') }}";
        //     }, 800);
        // };
    </script>
</body>

</html>