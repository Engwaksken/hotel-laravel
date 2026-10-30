<nav class="module-tabs module-tabs-links accounting-tabs" aria-label="Accounting navigation">
    <a class="module-tab {{ ($active ?? '') === 'overview' ? 'active' : '' }}" href="{{ route('accounting.dashboard') }}"><i class="fas fa-calculator"></i> Overview</a>
    <a class="module-tab {{ ($active ?? '') === 'ledger' ? 'active' : '' }}" href="{{ route('accounting.journals.index') }}"><i class="fas fa-book"></i> General Ledger</a>
    <a class="module-tab {{ ($active ?? '') === 'audit' ? 'active' : '' }}" href="{{ route('accounting.night-audit.index') }}"><i class="fas fa-moon"></i> Night Audit</a>
    <a class="module-tab {{ ($active ?? '') === 'reconciliation' ? 'active' : '' }}" href="{{ route('accounting.reconciliations.index') }}"><i class="fas fa-scale-balanced"></i> Reconciliation</a>
    <a class="module-tab {{ ($active ?? '') === 'reports' ? 'active' : '' }}" href="{{ route('accounting.reports.index') }}"><i class="fas fa-chart-column"></i> Reports</a>
</nav>
