<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class InstallmentReceiptService
{
    public function create(
        int $invoiceId,
        float $amount,
        ?string $paymentMethod,
        ?string $reference,
        ?string $notes,
        ?int $userId
    ): object {
        return DB::transaction(function () use (
            $invoiceId,
            $amount,
            $paymentMethod,
            $reference,
            $notes,
            $userId
        ) {
            $receiptNumber = 'RCT-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));

            $id = DB::table('invoice_receipts')->insertGetId([
                'invoice_id' => $invoiceId,
                'receipt_number' => $receiptNumber,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'reference' => $reference,
                'notes' => $notes,
                'received_by' => $userId,
                'received_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (Schema::hasTable('invoices')) {
                $paid = (float) DB::table('invoice_receipts')
                    ->where('invoice_id', $invoiceId)
                    ->sum('amount');

                $invoice = DB::table('invoices')->where('id', $invoiceId)->first();

                if ($invoice) {
                    $total = (float) (
                        $invoice->grand_total
                        ?? $invoice->total_amount
                        ?? $invoice->total
                        ?? 0
                    );

                    $status = $paid <= 0
                        ? 'unpaid'
                        : ($paid + 0.00001 >= $total ? 'paid' : 'partial');

                    $update = [];
                    foreach (['paid_amount', 'amount_paid'] as $column) {
                        if (Schema::hasColumn('invoices', $column)) {
                            $update[$column] = $paid;
                            break;
                        }
                    }

                    if (Schema::hasColumn('invoices', 'status')) {
                        $update['status'] = $status;
                    }

                    if ($update !== []) {
                        DB::table('invoices')->where('id', $invoiceId)->update($update);
                    }
                }
            }

            return DB::table('invoice_receipts')->where('id', $id)->first();
        });
    }
}
