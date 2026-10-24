<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table): void {
                $table->id();
                $table->string('key')->unique();
                $table->longText('value')->nullable();
                $table->string('group', 80)->default('general')->index();
                $table->boolean('is_public')->default(false);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('phone', 50)->nullable()->after('email');
            });
        }

        if (Schema::hasTable('guests') && ! Schema::hasColumn('guests', 'qr_token')) {
            Schema::table('guests', function (Blueprint $table): void {
                $table->uuid('qr_token')->nullable()->unique()->after('id');
            });
        }

        if (! Schema::hasTable('invoice_receipts')) {
            Schema::create('invoice_receipts', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->string('receipt_number', 80)->unique();
                $table->decimal('amount', 18, 2);
                $table->string('payment_method', 80)->nullable();
                $table->string('reference', 120)->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('received_by')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamps();

                $table->index(['invoice_id', 'received_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_receipts');

        if (Schema::hasTable('guests') && Schema::hasColumn('guests', 'qr_token')) {
            Schema::table('guests', function (Blueprint $table): void {
                $table->dropColumn('qr_token');
            });
        }
    }
};
