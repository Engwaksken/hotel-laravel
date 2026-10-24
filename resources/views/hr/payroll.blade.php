@extends('layouts.app')
@section('title', 'Payroll')
@section('page_title', 'Payroll')
@section('content')
    <div class="alert alert-warning">
        <strong>Uganda statutory rules configured — checked 24 Oct 2026.</strong>
        PAYE is calculated progressively from employee tax residency and taxable monthly pay.
        NSSF uses 5% employee and 10% employer contributions on gross monthly wages.
        Confirm each employee's tax residency, NSSF eligibility and taxable allowances before live payroll.
    </div>

    <div class="grid-2">
        <div class="card">
            <h3>Generate payroll draft</h3>
            <form method="POST" action="{{ route('hr.payroll.generate') }}">
                @csrf
                <label>Period start<input type="date" name="period_start" value="{{ old('period_start') }}" required></label>
                <label>Period end<input type="date" name="period_end" value="{{ old('period_end') }}" required></label>
                <button class="btn primary" type="submit">Generate draft</button>
            </form>
        </div>

        <div class="card">
            <h3>Additional payroll component</h3>
            <p>Statutory PAYE and NSSF components are calculated automatically and cannot be overridden here.</p>
            <form method="POST" action="{{ route('hr.payroll.component') }}" class="form-grid">
                @csrf
                <label>Name<input name="name" maxlength="120" required></label>
                <label>Code<input name="code" maxlength="40" pattern="[A-Z0-9_]+" required></label>
                <label>Type<select name="type"><option value="earning">Earning</option><option value="deduction">Deduction</option><option value="employer_cost">Employer cost</option></select></label>
                <label>Calculation<select name="calculation"><option value="fixed">Fixed amount</option><option value="percentage">Percentage of basic salary</option></select></label>
                <label>Default value<input type="number" step="0.0001" min="0" name="default_value" value="0" required></label>
                <label class="check-row"><input type="checkbox" name="taxable" value="1"> Taxable earning</label>
                <button class="btn" type="submit">Save component</button>
            </form>
        </div>
    </div>

    <div class="card table-wrap">
        <h3>Active payroll components</h3>
        <table>
            <thead><tr><th>Component</th><th>Code</th><th>Type</th><th>Rule / default</th><th>Tax treatment</th></tr></thead>
            <tbody>
                @forelse($components as $component)
                    <tr>
                        <td>{{ $component->name }}</td>
                        <td>{{ $component->code }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($component->type)) }}</td>
                        <td>
                            @if($component->code === 'PAYE')
                                URA progressive table
                            @elseif(in_array($component->code, ['PENSION_EE', 'NSSF_EE'], true))
                                5% of gross monthly wages
                            @elseif(in_array($component->code, ['PENSION_ER', 'NSSF_ER'], true))
                                10% of gross monthly wages
                            @else
                                {{ $component->calculation === 'percentage' ? number_format((float) $component->default_value, 4).'%' : 'UGX '.number_format((float) $component->default_value, 2) }}
                            @endif
                        </td>
                        <td>{{ $component->taxable ? 'Taxable' : 'Not taxable' }}@if($component->statutory) · Statutory@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Payroll components have not been seeded for this property. Run the HR seeder.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card table-wrap">
        <h3>Payroll runs</h3>
        <table>
            <thead><tr><th>Reference</th><th>Period</th><th>Employees</th><th>Gross</th><th>Deductions</th><th>Employer costs</th><th>Net</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                @forelse($runs as $run)
                    <tr>
                        <td>{{ $run->reference }}</td>
                        <td>{{ $run->period_start->format('d M Y') }} – {{ $run->period_end->format('d M Y') }}</td>
                        <td>{{ $run->items->count() }}</td>
                        <td>UGX {{ number_format((float) $run->gross_total, 2) }}</td>
                        <td>UGX {{ number_format((float) $run->deduction_total, 2) }}</td>
                        <td>UGX {{ number_format((float) $run->employer_cost_total, 2) }}</td>
                        <td>UGX {{ number_format((float) $run->net_total, 2) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $run->status)) }}</td>
                        <td>
                            @if($run->status === 'draft')
                                <form method="POST" action="{{ route('hr.payroll.approve', $run) }}">
                                    @csrf<button class="btn small" type="submit">Approve</button>
                                </form>
                            @elseif($run->status === 'approved')
                                <form method="POST" action="{{ route('hr.payroll.pay', $run) }}">
                                    @csrf
                                    <select name="method" aria-label="Payment method">
                                        <option value="bank_transfer">Bank transfer</option><option value="mtn_momo">MTN MoMo</option>
                                        <option value="airtel_money">Airtel Money</option><option value="cash">Cash</option>
                                    </select>
                                    <button class="btn small" type="submit">Mark paid</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="empty">No payroll runs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $runs->links() }}
    </div>

    <div class="card">
        <h3>Uganda PAYE bands · monthly chargeable income (UGX)</h3>
        <p><b>Resident:</b> 0–335,000 nil; 335,001–410,000 at 20%; 410,001–485,000 at 25% after UGX 15,000 base tax; 485,001–10,000,000 at 30% after UGX 33,750 base tax; above UGX 10,000,000 adds 10% on the excess.</p>
        <p><b>Non-resident:</b> 10% up to UGX 335,000; 20% to UGX 410,000; 30% to UGX 10,000,000; above that adds 10% on the excess.</p>
        <small>Sources: <a href="https://ura.go.ug/en/changes-to-paye-return-form-following-the-income-tax-amendment-act-2026/" target="_blank" rel="noopener">URA 2026 PAYE amendment notice</a>, <a href="https://ura.go.ug/en/domestic-taxes/paye-rates/" target="_blank" rel="noopener">URA PAYE rates</a>, and <a href="https://www.nssfug.org/about-us/membership/" target="_blank" rel="noopener">NSSF membership contributions</a>. Consult URA/NSSF or a qualified payroll adviser for individual exemptions and tax-year changes.</small>
    </div>
@endsection
