@extends('layouts.app')
@section('title', 'Financial Reports')
@section('page_title', 'Financial Reports')
@section('content')
@include('accounting.partials.tabs', ['active' => 'reports'])
<div class="page-head report-heading"><div><h1>Financial Reports</h1><p>Financial position and balances through {{ \Illuminate\Support\Carbon::parse($to)->format('d M Y') }}.</p></div>
    <div class="report-actions"><a class="btn" href="{{ route('accounting.reports.csv', request()->query()) }}"><i class="fas fa-file-csv"></i> Export CSV</a><a class="btn primary" href="{{ route('accounting.reports.pdf', request()->query()) }}"><i class="fas fa-file-pdf"></i> Export PDF</a></div>
</div>
<form method="GET" class="panel report-filter"><label for="report-to">Report to</label><input id="report-to" type="date" name="to" value="{{ $to }}"><button class="btn">Refresh report</button></form>
<div class="stats compact-stats">
    <div class="stat stat-green"><div class="stat-icon"><i class="fas fa-arrow-trend-up"></i></div><div><span>Income</span><strong class="stat-money">UGX {{ number_format($income) }}</strong></div></div>
    <div class="stat stat-orange"><div class="stat-icon"><i class="fas fa-arrow-trend-down"></i></div><div><span>Expenses</span><strong class="stat-money">UGX {{ number_format($expenses) }}</strong></div></div>
    <div class="stat {{ $netProfit >= 0 ? 'stat-blue' : 'stat-red' }}"><div class="stat-icon"><i class="fas fa-scale-balanced"></i></div><div><span>Net Profit / (Loss)</span><strong class="stat-money">UGX {{ number_format($netProfit) }}</strong></div></div>
    <div class="stat stat-purple"><div class="stat-icon"><i class="fas fa-building-columns"></i></div><div><span>Total Assets</span><strong class="stat-money">UGX {{ number_format($assets) }}</strong></div></div>
</div>
<div class="tab-buttons report-tabs" role="tablist" aria-label="Financial report sections">
    <button class="btn active" type="button" role="tab" aria-selected="true" data-report-tab="income-statement">Income Statement</button>
    <button class="btn" type="button" role="tab" aria-selected="false" data-report-tab="balance-sheet">Balance Sheet Summary</button>
    <button class="btn" type="button" role="tab" aria-selected="false" data-report-tab="trial-balance">Trial Balance</button>
    <button class="btn" type="button" role="tab" aria-selected="false" data-report-tab="receivables">Accounts Receivable</button>
    <button class="btn" type="button" role="tab" aria-selected="false" data-report-tab="payables">Accounts Payable</button>
</div>
<section class="card report-tab-panel" role="tabpanel" data-report-panel="income-statement"><h2>Income Statement</h2><p>Revenue <b>UGX {{ number_format($income) }}</b></p><p>Expenses <b>UGX {{ number_format($expenses) }}</b></p><hr><p>Net Profit / (Loss) <b>UGX {{ number_format($netProfit) }}</b></p></section>
<section class="card report-tab-panel" role="tabpanel" data-report-panel="balance-sheet" hidden><h2>Balance Sheet Summary</h2><p>Assets <b>UGX {{ number_format($assets) }}</b></p><p>Liabilities <b>UGX {{ number_format($liabilities) }}</b></p><p>Equity <b>UGX {{ number_format($equity) }}</b></p></section>
<section class="card report-tab-panel" role="tabpanel" data-report-panel="trial-balance" hidden><h2>Trial Balance</h2><div class="table-wrap"><table><thead><tr><th>Code</th><th>Account</th><th>Type</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead><tbody>@forelse($trial as $row)<tr><td>{{ $row['code'] }}</td><td>{{ $row['name'] }}</td><td>{{ ucfirst($row['type']) }}</td><td>UGX {{ number_format($row['debit']) }}</td><td>UGX {{ number_format($row['credit']) }}</td><td>UGX {{ number_format($row['balance']) }}</td></tr>@empty<tr><td colspan="6" class="empty">No account activity for this date.</td></tr>@endforelse</tbody></table></div></section>
<section class="card report-tab-panel" role="tabpanel" data-report-panel="receivables" hidden><h2>Accounts Receivable</h2><div class="table-wrap"><table><thead><tr><th>Guest</th><th>Folio</th><th>Balance</th></tr></thead><tbody>@forelse($receivables as $folio)<tr><td>{{ $folio->guest?->full_name ?? '—' }}</td><td>{{ $folio->folio_number }}</td><td>UGX {{ number_format($folio->balance) }}</td></tr>@empty<tr><td colspan="3" class="empty">No outstanding receivables.</td></tr>@endforelse</tbody></table></div></section>
<section class="card report-tab-panel" role="tabpanel" data-report-panel="payables" hidden><h2>Accounts Payable</h2><div class="table-wrap"><table><thead><tr><th>Supplier</th><th>Invoice</th><th>Outstanding</th></tr></thead><tbody>@forelse($payables as $invoice)<tr><td>{{ $invoice->supplier?->name ?? '—' }}</td><td>{{ $invoice->invoice_number }}</td><td>UGX {{ number_format((float) $invoice->total_amount - (float) $invoice->paid_amount) }}</td></tr>@empty<tr><td colspan="3" class="empty">No outstanding payables.</td></tr>@endforelse</tbody></table></div></section>
<script>
document.querySelectorAll('[data-report-tab]').forEach((tab) => tab.addEventListener('click', () => {
    document.querySelectorAll('[data-report-tab]').forEach((button) => { button.classList.remove('active'); button.setAttribute('aria-selected', 'false'); });
    document.querySelectorAll('[data-report-panel]').forEach((panel) => { panel.hidden = true; });
    tab.classList.add('active'); tab.setAttribute('aria-selected', 'true');
    document.querySelector(`[data-report-panel="${tab.dataset.reportTab}"]`).hidden = false;
}));
</script>
@endsection
