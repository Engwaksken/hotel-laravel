<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\PayrollComponent;
use App\Models\PayrollRun;
use App\Services\HR\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PayrollController extends Controller
{
    public function index()
    {
        $propertyId = auth()->user()->property_id;

        return view('hr.payroll', [
            'runs' => PayrollRun::with('items')
                ->where('property_id', $propertyId)
                ->latest()
                ->paginate(20),
            'components' => PayrollComponent::where('property_id', $propertyId)
                ->orderBy('statutory', 'desc')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function component(Request $request)
    {
        $propertyId = (int) $request->user()->property_id;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_]+$/'],
            'type' => ['required', 'in:earning,deduction,employer_cost'],
            'calculation' => ['required', 'in:fixed,percentage'],
            'default_value' => ['required', 'numeric', 'min:0'],
            'taxable' => ['nullable', 'boolean'],
        ]);
        $data['code'] = strtoupper($data['code']);
        if (in_array($data['code'], ['PAYE', 'PENSION_EE', 'PENSION_ER', 'NSSF_EE', 'NSSF_ER'], true)) {
            throw ValidationException::withMessages([
                'code' => 'Statutory PAYE and NSSF components are managed automatically.',
            ]);
        }
        $data['taxable'] = $request->boolean('taxable');

        PayrollComponent::updateOrCreate(
            ['property_id' => $propertyId, 'code' => $data['code']],
            $data + ['property_id' => $propertyId],
        );

        return back()->with('success', 'Payroll component saved.');
    }

    public function generate(Request $request, PayrollService $service)
    {
        $data = $request->validate([
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ]);

        $service->generate(
            (int) $request->user()->property_id,
            $data['period_start'],
            $data['period_end'],
            (int) $request->user()->id,
        );

        return back()->with('success', 'Draft payroll generated. Review the run before approving.');
    }

    public function approve(PayrollRun $run, PayrollService $service)
    {
        abort_unless($run->property_id === auth()->user()->property_id, 403);
        $service->approve($run, (int) auth()->id());

        return back()->with('success', 'Payroll approved and posted to accounting.');
    }

    public function pay(Request $request, PayrollRun $run, PayrollService $service)
    {
        abort_unless($run->property_id === $request->user()->property_id, 403);
        $data = $request->validate(['method' => ['required', 'in:cash,bank_transfer,mtn_momo,airtel_money']]);
        $service->markPaid($run, $data['method'], (int) $request->user()->id);

        return back()->with('success', 'Payroll marked paid and cash/bank entry posted.');
    }
}
