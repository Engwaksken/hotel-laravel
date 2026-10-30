<!doctype html>
<html><head><meta charset="utf-8"><style>
@page { margin: 90px 34px 55px; }
body { font: 9px DejaVu Sans, sans-serif; color: #23313c; }
header { position: fixed; top: -68px; left: 0; right: 0; border-bottom: 2px solid #0f766e; padding-bottom: 9px; }
header img { max-height: 42px; max-width: 140px; vertical-align: middle; margin-right: 12px; }
header strong { font-size: 16px; color: #134e4a; }
.watermark { position: fixed; top: 34%; left: 25%; width: 50%; text-align: center; opacity: .07; z-index: -1; }
.watermark img { width: 100%; }
footer { position: fixed; bottom: -35px; left: 0; right: 0; text-align: center; color: #697782; border-top: 1px solid #dce4e7; padding-top: 6px; }
h2 { color: #134e4a; font-size: 12px; margin: 18px 0 7px; }
.summary { width: 100%; border-collapse: collapse; }
.summary td { width: 25%; padding: 7px; border: 1px solid #dce4e7; background: #f4f8f7; }
table.data { width: 100%; border-collapse: collapse; }
.data th,.data td { border: 1px solid #dce4e7; padding: 5px; text-align: left; }
.data th { background: #eaf3f1; color: #134e4a; }
tr { page-break-inside: avoid; }
</style></head><body>
<header>@if($logoData)<img src="{{ $logoData }}" alt="">@endif<strong>{{ $hotelName }}</strong><span style="float:right">Financial Report · {{ $to }}</span></header>
@if($logoData)<div class="watermark"><img src="{{ $logoData }}" alt=""></div>@endif
<footer>{{ $hotelName }} · Confidential financial report · Page <script type="text/php">if (isset($pdf)) { $pdf->page_text(740, 570, "{PAGE_NUM}", null, 8, array(0.4,0.4,0.4)); }</script></footer>
<h2>Financial Summary</h2><table class="summary"><tr><td>Income<br><b>UGX {{ number_format($income) }}</b></td><td>Expenses<br><b>UGX {{ number_format($expenses) }}</b></td><td>Net Profit / (Loss)<br><b>UGX {{ number_format($netProfit) }}</b></td><td>Assets<br><b>UGX {{ number_format($assets) }}</b></td></tr><tr><td>Liabilities<br><b>UGX {{ number_format($liabilities) }}</b></td><td>Equity<br><b>UGX {{ number_format($equity) }}</b></td></tr></table>
<h2>Trial Balance</h2><table class="data"><thead><tr><th>Code</th><th>Account</th><th>Type</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead><tbody>@foreach($trial as $row)<tr><td>{{ $row['code'] }}</td><td>{{ $row['name'] }}</td><td>{{ ucfirst($row['type']) }}</td><td>{{ number_format($row['debit']) }}</td><td>{{ number_format($row['credit']) }}</td><td>{{ number_format($row['balance']) }}</td></tr>@endforeach</tbody></table>
<h2>Accounts Receivable</h2><table class="data"><thead><tr><th>Guest</th><th>Folio</th><th>Balance</th></tr></thead><tbody>@forelse($receivables as $folio)<tr><td>{{ $folio->guest?->full_name ?? '—' }}</td><td>{{ $folio->folio_number }}</td><td>UGX {{ number_format($folio->balance) }}</td></tr>@empty<tr><td colspan="3">No outstanding receivables.</td></tr>@endforelse</tbody></table>
<h2>Accounts Payable</h2><table class="data"><thead><tr><th>Supplier</th><th>Invoice</th><th>Outstanding</th></tr></thead><tbody>@forelse($payables as $invoice)<tr><td>{{ $invoice->supplier?->name ?? '—' }}</td><td>{{ $invoice->invoice_number }}</td><td>UGX {{ number_format((float)$invoice->total_amount-(float)$invoice->paid_amount) }}</td></tr>@empty<tr><td colspan="3">No outstanding payables.</td></tr>@endforelse</tbody></table>
</body></html>
