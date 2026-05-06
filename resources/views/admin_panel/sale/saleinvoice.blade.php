<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THERMAL INVOICE</title>
    <style>
        /* Base Reset */
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            /* MAtch crisp sans-serif look */
            font-size: 12px;
            font-weight: 600;
            line-height: 1.3;
        }

        /* Action Buttons (Hidden on Print) */
        .actions {
            max-width: 80mm;
            margin: 10px auto;
            text-align: center;
        }

        .btn {
            border: 1px solid #000;
            background: #eee;
            padding: 5px 15px;
            font-size: 12px;
            cursor: pointer;
            margin: 2px;
        }

        /* Thermal Container */
        .page-container {
            width: 78mm;
            margin: 0 auto;
            background: #fff;
            padding: 2mm;
        }

        /* Utils */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: 800;
        }

        .uppercase {
            text-transform: uppercase;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .brand-name {
            font-size: 24px;
            font-weight: 950;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 1px;
        }

        .address,
        .phone {
            font-size: 11px;
            margin-bottom: 3px;
            line-height: 1.2;
        }

        .receipt-title {
            border-top: 1.5pt solid #000;
            border-bottom: 1.5pt solid #000;
            padding: 4px 0;
            margin: 8px 0;
            font-size: 15px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Meta Section */
        .meta-table {
            width: 100%;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 10px;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 2px 0;
            vertical-align: middle;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 8px;
        }

        .items-table th {
            border-top: 1.5pt solid #000;
            border-bottom: 1pt dashed #000;
            text-transform: uppercase;
            padding: 6px 0;
            font-weight: 900;
        }

        .items-table td {
            padding: 5px 0;
            border-bottom: 0.5pt dashed #ccc;
            vertical-align: top;
            line-height: 1.2;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        /* Column Widths and Alignment */
        .col-item {
            width: 45%;
            text-align: left;
        }

        .col-qty {
            width: 15%;
            text-align: center;
        }

        .col-rate {
            width: 20%;
            text-align: right;
        }

        .col-total {
            width: 20%;
            text-align: right;
        }

        /* Totals Section */
        .totals-container {
            border-top: 1.5pt solid #000;
            padding-top: 8px;
            margin-top: 5px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 12px;
            font-weight: 700;
        }

        .net-total-row {
            display: flex;
            justify-content: space-between;
            border-top: 1.5pt solid #000;
            border-bottom: 1.5pt solid #000;
            padding: 6px 0;
            margin: 8px 0;
            font-size: 18px;
            font-weight: 950;
        }

        .amount-in-words-box {
            border: 1pt solid #000;
            padding: 6px;
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            margin: 12px 0;
            text-transform: uppercase;
            font-style: italic;
        }

        /* Footer */
        .footer {
            border-top: 1pt dashed #000;
            padding-top: 10px;
            text-align: center;
            font-size: 10px;
            line-height: 1.4;
        }

        .footer-heading {
            font-size: 13px;
            font-weight: 950;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        @media print {
            @page { margin: 0; }
            body {
                margin: 0;
            }

            .actions {
                display: none;
            }

            .page-container {
                width: 100%;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body>

    <div class="actions">
        <button class="btn" onclick="window.print()">Print</button>
        <button class="btn" onclick="window.close()">Close</button>
    </div>

    <div class="page-container">
        <!-- Header -->
        <div class="header">
            <div class="brand-name">Al-Owais Petroleum Service</div>
            <div class="address">Osaka Asia Battery Service Tando Allahyar Sindh postal 70110</div>
            <div class="phone">0333-3544864 | 0333-2836640</div>
        </div>

        <div class="receipt-title">SALES RECEIPT</div>

        <!-- Meta Grid -->
        <table class="meta-table">
            <tr>
                <td style="text-align:left; width: 50%;">Inv: #{{ $sale->invoice_no ?? 'N/A' }}</td>
                <td style="text-align:right; width: 50%;">Date: {{ optional($sale->created_at)->format('d-m-y h:i A') }}
                </td>
            </tr>
            <tr>
                <td style="text-align:left;">Cust:
                    {{ Str::limit($sale->customer_relation->customer_name ?? 'Walk-in', 18) }}
                </td>
                <td style="text-align:right;">Ref: {{ Str::limit($sale->reference ?? '-', 15) }}</td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-item">ITEM NAME</th>
                    <th class="col-qty">QTY</th>
                    <th class="col-rate">RATE</th>
                    <th class="col-total">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saleItems as $item)
                    <tr>
                        <td class="col-item">
                            <div style="font-weight: 900;">{{ $item['item_name'] }}</div>
                            @if(!empty($item['note']))
                                <div style="font-size:9px; font-weight: 400; font-style: italic;">({{ $item['note'] }})</div>
                            @endif
                        </td>
                        <td class="col-qty">
                            {{ $item['qty'] }}
                            @php
                                $unitRaw = is_array($item['unit']) ? ($item['unit'][0] ?? '') : $item['unit'];
                                $unit = strtolower(trim($unitRaw));
                            @endphp
                            <span style="font-size: 9px; font-weight: 400;">{{ $unit }}</span>
                        </td>
                        <td class="col-rate">{{ number_format($item['price'], 0) }}</td>
                        <td class="col-total">{{ number_format($item['total'], 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-container">
            <div class="totals-row">
                <span>Total Items:</span>
                <span>{{ $bill->total_items }}</span>
            </div>
            <div class="totals-row">
                <span>Sub Total:</span>
                <span>{{ number_format($bill->total_bill_amount ?? 0, 0) }}</span>
            </div>
            @if(($bill->total_extradiscount ?? 0) > 0)
                <div class="totals-row">
                    <span>Discount:</span>
                    <span>-{{ number_format($bill->total_extradiscount, 0) }}</span>
                </div>
            @endif
            @if(($bill->labour_charges ?? 0) > 0)
                <div class="totals-row">
                    <span>Labour Charges:</span>
                    <span>+{{ number_format($bill->labour_charges, 0) }}</span>
                </div>
            @endif

            <div class="net-total-row">
                <span>NET TOTAL</span>
                <span>Rs {{ number_format($bill->total_net ?? 0, 0) }}</span>
            </div>

            <div class="totals-row" style="margin-top: 5px;">
                <span>Cash Received:</span>
                <span>{{ number_format($bill->cash ?? 0, 0) }}</span>
            </div>
            <div class="totals-row">
                <span>Change:</span>
                <span>{{ number_format($bill->change ?? 0, 0) }}</span>
            </div>

            @if(!is_null($customerClosingBalance ?? null))
                <div style="border-top: 1.5pt dashed #000; margin-top: 10px; padding-top: 8px;">
                    <div
                        style="display:flex; justify-content:space-between; font-size:14px; font-weight:950; border: 1pt solid #000; padding: 5px 3px;">
                        <span>CLOSING BALANCE:</span>
                        <span>Rs {{ number_format($customerClosingBalance, 0) }}</span>
                    </div>
                    @if($customerClosingBalance > 0)
                        <div style="text-align:center; font-size:10px; color:#000; margin-top:3px; font-weight:700;">
                            (Amount Payable by Customer)
                        </div>
                    @elseif($customerClosingBalance < 0)
                        <div style="text-align:center; font-size:10px; color:#000; margin-top:3px; font-weight:700;">
                            (Advance / Overpaid)
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Amount In Words Box -->
        <div class="amount-in-words-box">
            <span id="amountInWords">Loading...</span>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-heading">THANK YOU FOR VISITING!</div>
            <div>No Return No Exchange Without Bill</div>
            <div style="margin-top: 5px; font-size: 8px;">Powered by Prowave Technologies</div>
            <div style="font-size: 8px;">Phone: 0317-3836223</div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Amount in words
            const amount = parseFloat(`{{ $bill->total_net ?? 0 }}`) || 0;
            const amountInWords = (function numberToWords(num) {
                const ones = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten",
                    "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"];
                const tens = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];

                if (num === 0) return "Zero";

                function convert_hundreds(n) {
                    let str = "";
                    if (n > 99) { str += ones[Math.floor(n / 100)] + " Hundred "; n %= 100; }
                    if (n > 19) { str += tens[Math.floor(n / 10)] + " " + ones[n % 10]; }
                    else { str += ones[n]; }
                    return str.trim();
                }

                let crore = Math.floor(num / 10000000);
                let lakh = Math.floor((num % 10000000) / 100000);
                let thousand = Math.floor((num % 100000) / 1000);
                let hundred = num % 1000;
                let result = "";

                if (crore) result += convert_hundreds(crore) + " Crore ";
                if (lakh) result += convert_hundreds(lakh) + " Lakh ";
                if (thousand) result += convert_hundreds(thousand) + " Thousand ";
                if (hundred) result += convert_hundreds(hundred);

                return result.trim();
            })(Math.floor(amount));
            document.getElementById("amountInWords").innerText = amountInWords + " Rupees Only";

            // Autoprint support
            const query = new URLSearchParams(window.location.search);
            if (query.get('autoprint') === '1') {
                setTimeout(() => {
                    window.print();
                    setTimeout(() => {
                        const ret = query.get('return_to');
                        if (ret) window.location.href = ret;
                        else history.back();
                    }, 1000);
                }, 500);
            }
        });
    </script>
</body>

</html>