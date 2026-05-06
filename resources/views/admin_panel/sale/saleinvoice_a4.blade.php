<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SALES INVOICE - A4</title>
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
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            /* Standard text for A4 */
            line-height: 1.4;
        }

        /* Action Buttons */
        .actions {
            max-width: 210mm;
            margin: 20px auto;
            text-align: right;
            padding-right: 10px;
        }

        .btn {
            border: 1px solid #000;
            background: #f5f5f5;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 4px;
            margin-left: 5px;
        }

        .btn:hover {
            background: #e0e0e0;
        }

        /* A4 Container */
        .page-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 20mm;
            position: relative;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 3px solid #115e6e;
            padding-bottom: 20px;
        }

        .brand-section {
            display: flex;
            flex-direction: column;
        }

        .brand-name {
            font-size: 28px;
            font-weight: 800;
            color: #115e6e;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .brand-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .invoice-title-block {
            text-align: right;
        }

        .invoice-label {
            font-size: 32px;
            font-weight: 800;
            color: #115e6e;
            text-transform: uppercase;
        }

        .invoice-meta {
            margin-top: 10px;
            font-size: 12px;
            line-height: 1.6;
        }

        .invoice-meta span {
            display: block;
        }

        /* Billing & Info Grid */
        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 20px;
        }

        .billing-to {
            width: 60%;
        }

        .billing-label {
            font-weight: 700;
            color: #999;
            text-transform: uppercase;
            font-size: 11px;
            margin-bottom: 5px;
        }

        .billing-name {
            font-size: 16px;
            font-weight: 700;
            color: #000;
            margin-bottom: 5px;
        }

        .billing-details {
            font-size: 13px;
            color: #444;
            line-height: 1.5;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th,
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        th {
            background-color: #115e6e;
            color: #fff;
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 600;
            text-align: left;
        }

        th:last-child {
            text-align: right;
        }

        td:last-child {
            text-align: right;
        }

        .col-sl {
            width: 5%;
            text-align: center;
        }

        .col-desc {
            width: 45%;
        }

        .col-qty {
            width: 10%;
            text-align: center;
        }

        .col-price {
            width: 15%;
            text-align: right;
        }

        .col-amount {
            width: 20%;
            text-align: right;
            font-weight: 700;
        }

        tr:nth-child(even) {
            background-color: #fcfcfc;
        }

        /* Summary Section */
        .summary-section {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .payment-info {
            width: 50%;
            font-size: 12px;
            color: #666;
            padding-right: 20px;
        }

        .payment-label {
            font-weight: 700;
            color: #115e6e;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .totals-table {
            width: 40%;
            margin: 0;
            border: none;
        }

        .totals-table td {
            border: none;
            padding: 5px 0;
        }

        .totals-table .t-label {
            text-align: right;
            padding-right: 15px;
            color: #666;
            font-size: 13px;
        }

        .totals-table .t-value {
            text-align: right;
            font-weight: 600;
            font-size: 14px;
        }

        .totals-table .t-total-label {
            text-align: right;
            padding-right: 15px;
            color: #115e6e;
            font-weight: 800;
            font-size: 16px;
            padding-top: 10px;
            border-top: 2px solid #115e6e;
        }

        .totals-table .t-total-value {
            text-align: right;
            font-weight: 800;
            font-size: 18px;
            color: #115e6e;
            padding-top: 10px;
            border-top: 2px solid #115e6e;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #888;
            font-size: 11px;
            position: absolute;
            bottom: 20mm;
            width: calc(100% - 40mm);
        }

        .footer p {
            margin: 3px 0;
        }

        .amount-words {
            font-style: italic;
            color: #555;
            margin-bottom: 15px;
            font-size: 12px;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }

        /* Print Settings */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                margin: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .actions {
                display: none !important;
            }

            .page-container {
                width: 100%;
                min-height: 100vh;
                margin: 0;
                border: none;
                box-shadow: none;
                padding: 10mm 15mm;
            }

            .footer {
                position: fixed;
                bottom: 10mm;
                width: calc(100% - 30mm);
            }
        }
    </style>
</head>

<body>

    <!-- Action Buttons -->
    <div class="actions">
        <button class="btn" id="btnBack" type="button">Back</button>
        <button class="btn" id="btnPrint" type="button">Print A4</button>
    </div>

    <div class="page-container">
        <!-- Header -->
        <div class="header">
            <div class="brand-section">
                <div class="brand-name">Al-Owais Petroleum Service</div>
                <div class="brand-subtitle">Quality & Trust</div>
                <div class="invoice-meta" style="color:#555;">
                    Osaka Asia Battery Service Tando Allahyar Sindh Pastal Code 70110<br>
                    0333-3544684 | 0345-6333940
                </div>
            </div>
            <div class="invoice-title-block">
                <div class="invoice-label">INVOICE</div>
                <div class="invoice-meta">
                    <span>Invoice No: <strong>#{{ $sale->invoice_no ?? 'N/A' }}</strong></span>
                    <span>Date: <strong>{{ optional($sale->created_at)->format('d/m/Y') }}</strong></span>
                    <span>Time: <strong>{{ optional($sale->created_at)->format('h:i A') }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Billing Info -->
        <div class="info-grid">
            <div class="billing-to">
                <div class="billing-label">Billing To</div>
                <div class="billing-name">{{ $sale->customer_relation->customer_name ?? 'Walk-in Customer' }}</div>
                <div class="billing-details">
                    @if($sale->customer != 'Walk-in Customer')
                        {{ $sale->customer_relation->mobile ?? '' }}<br>
                        {{ $sale->customer_relation->address ?? '' }}
                    @endif
                </div>
            </div>
            <div style="text-align: right; width: 35%;">
                <div class="billing-label">Reference</div>
                <div style="font-size: 14px; font-weight: 600;">{{ $sale->reference ?? '-' }}</div>
            </div>
        </div>

        <!-- Item Table -->
        <table>
            <thead>
                <tr>
                    <th class="col-sl">SL</th>
                    <th class="col-desc">ITEM DESCRIPTION</th>
                    <th class="col-qty">QTY</th>
                    <th class="col-price">PRICE</th>
                    <th class="col-amount">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saleItems as $index => $item)
                    <tr>
                        <td class="col-sl">{{ $index + 1 }}</td>
                        <td class="col-desc">
                            {{ $item['item_name'] }}
                            @if($item['discount'] > 0)
                                <br><small style="color:#888;">Disc: {{ number_format($item['discount'], 2) }}</small>
                            @endif
                            @if(!empty($item['note']))
                                <div style="font-size: 11px; color: #666;">Note: {{ $item['note'] }}</div>
                            @endif
                        </td>
                        <td class="col-qty">
                            @php
                                $unitRaw = is_array($item['unit']) ? ($item['unit'][0] ?? '') : $item['unit'];
                                $unit = strtolower(trim($unitRaw));
                                $unitShort = (in_array($unit, ['pcs', 'piece', 'pieces'])) ? 'Pcs' :
                                    ((in_array($unit, ['mtr', 'meter', 'metre'])) ? 'Mtr' :
                                        ((in_array($unit, ['yd', 'yard', 'yards'])) ? 'Yd' : $unitRaw));
                            @endphp
                            {{ $item['qty'] }} {{ $unitShort }}
                        </td>
                        <td class="col-price">{{ number_format($item['price'], 2) }}</td>
                        <td class="col-amount">{{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary & Totals -->
        <div class="summary-section">
            <div class="payment-info">
                <div class="amount-words">Amount in words: <span id="amountInWords"
                        style="font-weight: 700; color: #115e6e;">Loading...</span></div>

                <div class="payment-label">Payment Method</div>
                <div>Cash / Card</div>

                <div class="payment-label" style="margin-top: 15px;">Terms and Conditions</div>
                <ul style="margin: 0; padding-left: 20px; font-size: 12px; color: #555;">
                    <li>Goods once sold will not be returned or exchanged after 3 days.</li>
                </ul>
            </div>

            <table class="totals-table">
                <tr>
                    <td class="t-label">Subtotal:</td>
                    <td class="t-value">{{ number_format($bill->total_bill_amount ?? 0, 2) }}</td>
                </tr>
                @if(($bill->total_extradiscount ?? 0) > 0)
                    <tr>
                        <td class="t-label">Extra Discount:</td>
                        <td class="t-value">-{{ number_format($bill->total_extradiscount, 2) }}</td>
                    </tr>
                @endif
                @if(($bill->labour_charges ?? 0) > 0)
                    <tr>
                        <td class="t-label">Labour Charges:</td>
                        <td class="t-value">+{{ number_format($bill->labour_charges, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="t-total-label">Total:</td>
                    <td class="t-total-value">{{ number_format($bill->total_net ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="t-label" style="padding-top: 10px;">Cash Paid:</td>
                    <td class="t-value" style="padding-top: 10px;">{{ number_format($bill->cash ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="t-label">Change:</td>
                    <td class="t-value">{{ number_format($bill->change ?? 0, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Developed By: Prowave Technologies | +92 317 3836 223</p>
        </div>
    </div>

    <!-- Script -->
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

            // Buttons
            document.getElementById('btnPrint').addEventListener('click', () => window.print());
            document.getElementById('btnBack').addEventListener('click', () => {
                if (history.length > 1) history.back(); else window.close();
            });
        });
    </script>
</body>

</html>