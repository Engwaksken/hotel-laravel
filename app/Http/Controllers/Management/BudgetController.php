<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\{Account, ManagementBudget, ManagementBudgetLine};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    private function propertyId(Request $request): int
    {
        abort_unless($request->user()->property_id, 403, 'Your account is not assigned to a hotel branch.');
        return (int) $request->user()->property_id;
    }

    public function index(Request $request)
    {
        $pid = $this->propertyId($request);

        return view('management.budgets', [
            'budgets' => ManagementBudget::where('property_id', $pid)
                ->latest()
                ->with('lines.account')
                ->get(),
            'accounts' => Account::where('property_id', $pid)
                ->whereIn('type', ['income', 'expense'])
                ->where('is_active', true)
                ->orderBy('code')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canManageBranch(), 403);
        $pid = $this->propertyId($request);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'fiscal_year' => [
                'required', 'integer', 'min:2020', 'max:2100',
                Rule::unique('management_budgets', 'fiscal_year')
                    ->where(fn ($query) => $query->where('property_id', $pid)->where('name', $request->input('name'))),
            ],
        ]);

        ManagementBudget::create($data + [
            'property_id' => $pid,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Branch budget created.');
    }

    public function line(Request $request, ManagementBudget $budget)
    {
        $pid = $this->propertyId($request);
        abort_unless((int) $budget->property_id === $pid, 404);
        abort_unless($request->user()->canManageBranch(), 403);

        $data = $request->validate([
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where(fn ($query) => $query
                    ->where('property_id', $pid)
                    ->where('is_active', true)
                    ->whereIn('type', ['income', 'expense'])),
            ],
            'month' => 'required|integer|min:1|max:12',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        abort_unless($budget->status === 'draft', 422, 'Approved budgets are locked.');

        ManagementBudgetLine::updateOrCreate(
            [
                'management_budget_id' => $budget->id,
                'account_id' => $data['account_id'],
                'month' => $data['month'],
            ],
            ['amount' => $data['amount'], 'notes' => $data['notes'] ?? null]
        );

        return back()->with('success', 'Budget line updated.');
    }

    public function approve(Request $request, ManagementBudget $budget)
    {
        $pid = $this->propertyId($request);
        abort_unless((int) $budget->property_id === $pid, 404);
        abort_unless($request->user()->canManageBranch(), 403);
        abort_unless($budget->status === 'draft', 422, 'This budget has already been approved.');
        abort_if($budget->lines()->count() === 0, 422, 'Add at least one budget line before approving.');

        $budget->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Branch budget approved.');
    }
}
