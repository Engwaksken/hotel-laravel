<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employees')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table): void {
            if (! Schema::hasColumn('employees', 'tax_residency')) {
                $table->string('tax_residency', 20)->default('resident')->after('tin');
            }

            if (! Schema::hasColumn('employees', 'nssf_eligible')) {
                $table->boolean('nssf_eligible')->default(true)->after('tax_residency');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('employees')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table): void {
            $columns = [];
            if (Schema::hasColumn('employees', 'tax_residency')) {
                $columns[] = 'tax_residency';
            }
            if (Schema::hasColumn('employees', 'nssf_eligible')) {
                $columns[] = 'nssf_eligible';
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
