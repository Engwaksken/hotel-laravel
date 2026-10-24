<!doctype html>
<html>
<head><meta charset="utf-8"><title>Receipt</title>
<style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:12px;color:#222}table{width:100%;border-collapse:collapse}td,th{padding:8px;border-bottom:1px solid #ddd;text-align:left}</style>
</head>
<body>
<h2>Payment Receipt</h2>
<table>
<tr><th>Receipt Number</th><td>{{ $record->receipt_number }}</td></tr>
<tr><th>Invoice</th><td>{{ $invoice->invoice_number ?? $invoice->id }}</td></tr>
<tr><th>Amount</th><td>{{ number_format((float)$record->amount, 2) }}</td></tr>
<tr><th>Payment Method</th><td>{{ $record->payment_method }}</td></tr>
<tr><th>Reference</th><td>{{ $record->reference }}</td></tr>
<tr><th>Received At</th><td>{{ $record->received_at }}</td></tr>
<tr><th>Notes</th><td>{{ $record->notes }}</td></tr>
</table>
</body>
</html>
