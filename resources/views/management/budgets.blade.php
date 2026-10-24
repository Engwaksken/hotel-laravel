@extends('layouts.app')
@section('title', 'Budgets')
@section('page_title', 'Budgets & Forecasting')
@section('content')
    <div class="page-head">
        <div>
            <h1>Budgets &amp; Forecasting</h1>
            <p>Plan branch income and expenditure by account and month.</p>
        </div>
        @if(auth()->user()->canManageBranch())
            <button class="btn primary" type="button" data-modal="create-budget-modal">Create budget</button>
        @endif
    </div>

    @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    @if(auth()->user()->canManageBranch())
        <div class="modal" id="create-budget-modal">
            <div class="modal-box">
                <button class="modal-close" type="button" aria-label="Close">&times;</button>
                <h2>Create a branch budget</h2>
                <form method="POST" action="{{ route('management.budgets.store') }}" class="form-grid">
                    @csrf
                    <label>Budget name<input name="name" value="{{ old('name') }}" maxlength="120" required></label>
                    <label>Fiscal year<input type="number" name="fiscal_year" value="{{ old('fiscal_year', now()->year) }}" min="2020" max="2100" required></label>
                    <div class="actions"><button class="btn primary" type="submit">Create budget</button></div>
                </form>
            </div>
        </div>
    @endif

    @forelse($budgets as $budget)
        <section class="card budget-card">
            <div class="card-head">
                <div>
                    <h2>{{ $budget->name }} · {{ $budget->fiscal_year }}</h2>
                    <span class="badge {{ $budget->status === 'approved' ? 'success' : 'warning' }}">{{ ucfirst($budget->status) }}</span>
                </div>
                <strong>Total planned: UGX {{ number_format((float) $budget->lines->sum('amount'), 2) }}</strong>
            </div>

            @if($budget->lines->isNotEmpty())
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Account</th><th>Month</th><th>Amount</th><th>Notes</th></tr></thead>
                        <tbody>
                            @foreach($budget->lines->sortBy(fn($line) => [$line->month, $line->account?->code]) as $line)
                                <tr>
                                    <td>{{ $line->account?->code }} · {{ $line->account?->name ?? 'Account unavailable' }}</td>
                                    <td>{{ \Carbon\Carbon::create()->month($line->month)->format('F') }}</td>
                                    <td>UGX {{ number_format((float) $line->amount, 2) }}</td>
                                    <td>{{ $line->notes ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="empty">No budget lines yet. Add income or expense allocations below.</p>
            @endif

            @if($budget->status === 'draft' && auth()->user()->canManageBranch())
                @if($accounts->isNotEmpty())
                    <h3>Add or update a monthly line</h3>
                    <form method="POST" action="{{ route('management.budgets.line', $budget) }}" class="form-grid">
                        @csrf
                        <label>Account
                            <select name="account_id" required>
                                <option value="">Select income or expense account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>{{ $account->code }} · {{ $account->name }} ({{ ucfirst($account->type) }})</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Month
                            <select name="month" required>
                                <option value="">Select month</option>
                                @foreach(range(1, 12) as $month)
                                    <option value="{{ $month }}" @selected(old('month') == $month)>{{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Amount (UGX)<input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" required></label>
                        <label class="span-2">Notes<input name="notes" maxlength="1000" value="{{ old('notes') }}"></label>
                        <div class="actions"><button class="btn" type="submit">Save budget line</button></div>
                    </form>
                @else
                    <p class="alert">No active income or expense accounts are available for this branch.</p>
                @endif

                <form method="POST" action="{{ route('management.budgets.approve', $budget) }}" class="budget-approve-form">
                    @csrf
                    <button class="btn primary" type="submit" @disabled($budget->lines->isEmpty())>Approve budget</button>
                    @if($budget->lines->isEmpty())<small>Add at least one budget line before approval.</small>@endif
                </form>
            @endif
        </section>
    @empty
        <div class="card empty">No budgets yet. Create one to start planning.</div>
    @endforelse
@endsection
