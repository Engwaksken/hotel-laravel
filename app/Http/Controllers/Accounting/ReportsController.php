<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Folio;
use App\Models\SupplierInvoice;
use App\Support\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        return view('accounting.reports', $this->reportData($request));
    }

    public function csv(Request $request): StreamedResponse
    {
        $data = $this->reportData($request);
        $filename = 'financial-report-'.$data['to'].'.csv';

        return response()->streamDownload(function () use ($data): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Financial Report', 'As of '.$data['to']]);
            fputcsv($out, ['Income', $data['income']]);
            fputcsv($out, ['Expenses', $data['expenses']]);
            fputcsv($out, ['Net Profit / (Loss)', $data['netProfit']]);
            fputcsv($out, ['Assets', $data['assets']]);
            fputcsv($out, ['Liabilities', $data['liabilities']]);
            fputcsv($out, ['Equity', $data['equity']]);
            fputcsv($out, []);
            fputcsv($out, ['Trial Balance']);
            fputcsv($out, ['Code', 'Account', 'Type', 'Debit', 'Credit', 'Balance']);
            foreach ($data['trial'] as $row) {
                fputcsv($out, [$row['code'], $row['name'], $row['type'], $row['debit'], $row['credit'], $row['balance']]);
            }
            fputcsv($out, []);
            fputcsv($out, ['Accounts Receivable']);
            fputcsv($out, ['Guest', 'Folio', 'Balance']);
            foreach ($data['receivables'] as $folio) {
                fputcsv($out, [$folio->guest?->full_name, $folio->folio_number, $folio->balance]);
            }
            fputcsv($out, []);
            fputcsv($out, ['Accounts Payable']);
            fputcsv($out, ['Supplier', 'Invoice', 'Outstanding']);
            foreach ($data['payables'] as $invoice) {
                fputcsv($out, [$invoice->supplier?->name, $invoice->invoice_number, (float) $invoice->total_amount - (float) $invoice->paid_amount]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(Request $request)
    {
        $data = $this->reportData($request);
        $data['hotelName'] = SystemSetting::get('system_name', auth()->user()->property?->name ?? config('app.name'));
        $data['logoData'] = $this->logoData();

        return Pdf::loadView('accounting.reports-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download('financial-report-'.$data['to'].'.pdf');
    }

    private function reportData(Request $request): array
    {
        $propertyId = $request->user()->property_id;
        $to = $request->date('to')?->toDateString() ?? now()->toDateString();
        $accounts = Account::where('property_id', $propertyId)
            ->with(['lines' => fn ($query) => $query->whereHas('entry', fn ($entry) => $entry->where('status', 'posted')->whereDate('entry_date', '<=', $to))])
            ->orderBy('code')->get();
        $trial = $accounts->map(function ($account): array {
            $debit = (float) $account->lines->sum('debit');
            $credit = (float) $account->lines->sum('credit');
            return ['code' => $account->code, 'name' => $account->name, 'type' => $account->type, 'debit' => $debit, 'credit' => $credit, 'balance' => $debit - $credit];
        });
        $income = $trial->where('type', 'income')->sum(fn ($row) => -$row['balance']);
        $expenses = $trial->where('type', 'expense')->sum(fn ($row) => $row['balance']);

        return [
            'trial' => $trial, 'to' => $to, 'income' => $income, 'expenses' => $expenses,
            'netProfit' => $income - $expenses,
            'assets' => $trial->where('type', 'asset')->sum(fn ($row) => $row['balance']),
            'liabilities' => $trial->where('type', 'liability')->sum(fn ($row) => -$row['balance']),
            'equity' => $trial->where('type', 'equity')->sum(fn ($row) => -$row['balance']),
            'receivables' => Folio::with('guest')->where('property_id', $propertyId)->where('balance', '>', 0)->orderByDesc('balance')->get(),
            'payables' => SupplierInvoice::with('supplier')->where('property_id', $propertyId)->whereIn('status', ['unpaid', 'part_paid'])->get(),
        ];
    }

    private function logoData(): ?string
    {
        $path = SystemSetting::get('logo_path');
        if (! $path || str_starts_with($path, 'http')) {
            return null;
        }
        $path = preg_replace('#^(public/|storage/)#', '', ltrim($path, '/'));
        $file = storage_path('app/public/'.$path);
        if (! is_file($file)) {
            return null;
        }

        return 'data:'.(mime_content_type($file) ?: 'image/png').';base64,'.base64_encode(file_get_contents($file));
    }
}
