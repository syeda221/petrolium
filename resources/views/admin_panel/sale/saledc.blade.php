<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
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
    </style>
</head>

<body>

    <div class="receipt-container">

        <!-- Header -->
        <div class="center">
            <h2 style="margin:0;font-size:14px;" class="bold">Al-Owais Petroleum Service</h2>
            <p style="margin:0;">Al-Owais Petroleum Service</p>
            <p style="margin:0;">Tower Market Near Ptcl office Hyderabad</p>
            <p style="margin:0;">Phone: 0333-3544684 | 0345-6333940</p>
        </div>

        <div class="line"></div>
        <div class="center bold">DELIVERY CHALLAN</div>
        <div class="line"></div>
        <table>
            <tr>
                <th>DC:</th>
                <td> DC-{{ $sale->id }}</td>
            </tr>
            <tr>
                <th>Reference:</th>
                <td> {{ $sale->reference ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Date:</th>
                <td> {{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y, h:i A') }}</td>
            </tr>
        </table>
        <div class="line"></div>

        <!-- Details -->
        <table>
            <tr>
                <th>Customer:</th>
                <td> {{ $sale->customer_relation->customer_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Mobile:</th>
                <td>{{ $sale->customer_relation->mobile ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Email </th>
                <td> {{ $sale->customer_relation->email_address ?? 'N/A' }}</td>
            </tr>
        </table>

        <div class="line"></div>

        <!-- Items -->
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Code</th>
                    <th>Brand</th>
                    <th>Unit</th>
                    <th style="text-align: right;">Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saleItems as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['item_name'] }}</td>
                        <td>{{ $item['item_code'] }}</td>
                        <td>{{ $item['brand'] }}</td>
                        <td>{{ $item['unit'] }}</td>
                        <td style="text-align: right;">{{ $item['qty'] }}</td>
                    </tr>
                @endforeach
            </tbody>

        </table>


        <!-- Totals -->



        <!-- Footer -->

    </div>

</body>

</html>