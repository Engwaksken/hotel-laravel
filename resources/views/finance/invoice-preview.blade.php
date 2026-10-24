<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice Preview</title>
<style>
body{font-family:DejaVu Sans,Arial,sans-serif;color:#222;font-size:12px}
.header{display:flex;justify-content:space-between;margin-bottom:18px}
table{width:100%;border-collapse:collapse}
th,td{padding:8px;border-bottom:1px solid #ddd;text-align:left}
.actions{margin-bottom:16px}
@media print{.actions{display:none}}
</style>
</head>
<body>
<div class="actions">
    <button onclick="window.print()">Print</button>
</div>
<h2>Invoice #{{ $record->invoice_number ?? $record->id }}</h2>
<table>
    @foreach((array)$record as $key => $value)
        <tr><th>{{ ucwords(str_replace('_',' ',$key)) }}</th><td>{{ $value }}</td></tr>
    @endforeach
</table>

<h3>Receipts / Instalments</h3>
<table>
    <thead><tr><th>Receipt</th><th>Amount</th><th>Method</th><th>Reference</th><th>Date</th></tr></thead>
    <tbody>
    @foreach($receipts as $receipt)
        <tr>
            <td>{{ $receipt->receipt_number }}</td>
            <td>{{ number_format((float)$receipt->amount, 2) }}</td>
            <td>{{ $receipt->payment_method }}</td>
            <td>{{ $receipt->reference }}</td>
            <td>{{ $receipt->received_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
