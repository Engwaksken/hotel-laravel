<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\InstallmentReceiptService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceDocumentController extends Controller
{
    public function show(int $invoice): View
    {
        $record = DB::table('invoices')->where('id', $invoice)->first();
        abort_if(! $record, 404);

        $receipts = DB::table('invoice_receipts')
            ->where('invoice_id', $invoice)
            ->orderBy('received_at')
            ->get();

        return view('finance.invoice-preview', compact('record', 'receipts'));
    }

    public function pdf(int $invoice)
    {
        $record = DB::table('invoices')->where('id', $invoice)->first();
        abort_if(! $record, 404);

        $receipts = DB::table('invoice_receipts')
            ->where('invoice_id', $invoice)
            ->orderBy('received_at')
            ->get();

        return Pdf::loadView('finance.invoice-preview', compact('record', 'receipts'))
            ->stream('invoice-'.$invoice.'.pdf');
    }

    public function receipt(Request $request, int $invoice, InstallmentReceiptService $service): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'string', 'max:80'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service->create(
            $invoice,
            (float) $data['amount'],
            $data['payment_method'] ?? null,
            $data['reference'] ?? null,
            $data['notes'] ?? null,
            $request->user()?->id
        );

        return back()->with('success', 'Payment receipt recorded successfully.');
    }

    public function receiptPdf(int $receipt)
    {
        $record = DB::table('invoice_receipts')->where('id', $receipt)->first();
        abort_if(! $record, 404);

        $invoice = DB::table('invoices')->where('id', $record->invoice_id)->first();

        return Pdf::loadView('finance.receipt-preview', compact('record', 'invoice'))
            ->stream('receipt-'.$record->receipt_number.'.pdf');
    }
}
