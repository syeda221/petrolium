<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Payment In - {{ $payment->id }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f1f5f9; display: flex; justify-content: center; padding: 20px; }
    .voucher { max-width: 400px; width: 100%; background: #fff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); overflow: hidden; }
    .v-body { padding: 28px 28px 20px; }
    .center { text-align: center; }
    .company { font-size: 18px; font-weight: 800; color: #1e293b; }
    .voucher-title { font-size: 13px; font-weight: 700; color: #2563eb; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }
    .divider { border: none; border-top: 2px dashed #d1d5db; margin: 14px 0; }
    .info-table { width: 100%; border-collapse: collapse; }
    .info-table td { padding: 6px 0; font-size: 13px; vertical-align: top; }
    .info-table .lbl { color: #64748b; font-weight: 600; width: 40%; }
    .info-table .val { color: #1e293b; font-weight: 700; text-align: right; }
    .amount-row td { padding: 10px 0 4px; }
    .amount-row .val { font-size: 20px; color: #16a34a; }
    .footer { border-top: 1px solid #e2e8f0; padding: 14px 28px; font-size: 11px; color: #94a3b8; text-align: center; }
    .actions { display: flex; gap: 8px; justify-content: flex-end; padding: 0 28px 16px; }
    .btn { padding: 6px 14px; border: 1px solid #d1d5db; background: #fff; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 600; color: #374151; }
    .btn:hover { background: #f1f5f9; }
    @media print { body { background: #fff; padding: 0; } .voucher { box-shadow: none; border: 1px solid #d1d5db; } .actions { display: none !important; } }
  </style>
</head>
<body>
  <div class="voucher">
    <div class="actions">
      <button class="btn" onclick="window.print()">Print</button>
      <button class="btn" onclick="history.length>1?history.back():window.close()">Back</button>
    </div>
    <div class="v-body">
      <div class="center">
        <div class="company">Al-Owais Petroleum Service</div>
        <div class="voucher-title">Payment In Voucher</div>
      </div>
      <hr class="divider">
      <table class="info-table">
        <tr><td class="lbl">Voucher No</td><td class="val">PIN-{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}</td></tr>
        <tr><td class="lbl">Date</td><td class="val">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-M-Y') }}</td></tr>
        <tr><td class="lbl">Party</td><td class="val">{{ $party->name ?? $party->customer_name ?? '-' }} ({{ $partyType }})</td></tr>
        <tr><td class="lbl">Account</td><td class="val">{{ $payment->account->title ?? '-' }}</td></tr>
        <tr class="amount-row"><td class="lbl">Amount</td><td class="val">Rs. {{ number_format($payment->amount, 2) }}</td></tr>
        <tr><td class="lbl">Remarks</td><td class="val">{{ $payment->note ?? '-' }}</td></tr>
      </table>
      <hr class="divider">
      <div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;">
        <span>Prepared By: {{ auth()->user()->name ?? 'Admin' }}</span>
        <span>Signature: ______________</span>
      </div>
    </div>
    <div class="footer">Printed On: {{ now()->format('d-M-Y h:i A') }}</div>
  </div>
</body>
</html>
