<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payroll_components') || ! Schema::hasTable('properties')) {
            return;
        }

        foreach (DB::table('properties')->pluck('id') as $propertyId) {
            $now = now();

            foreach ([
                ['PAYE', 'PAYE (URA progressive)', 'deduction', 'fixed', 0],
                ['PENSION_EE', 'NSSF employee contribution', 'deduction', 'percentage', 5],
                ['PENSION_ER', 'NSSF employer contribution', 'employer_cost', 'percentage', 10],
            ] as [$code, $name, $type, $calculation, $value]) {
                $component = [
                    'name' => $name,
                    'type' => $type,
                    'calculation' => $calculation,
                    'default_value' => $value,
                    'taxable' => false,
                    'statutory' => true,
                    'is_active' => true,
                    'updated_at' => $now,
                ];
                $exists = DB::table('payroll_components')
                    ->where('property_id', $propertyId)
                    ->where('code', $code)
                    ->exists();

                if ($exists) {
                    DB::table('payroll_components')
                        ->where('property_id', $propertyId)
                        ->where('code', $code)
                        ->update($component);
                } else {
                    DB::table('payroll_components')->insert($component + [
                        'property_id' => $propertyId,
                        'code' => $code,
                        'created_at' => $now,
                    ]);
                }
            }

            DB::table('payroll_components')
                ->where('property_id', $propertyId)
                ->whereIn('code', ['TRANSPORT', 'HOUSING'])
                ->update(['taxable' => true, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        // The updated statutory setup replaces zero-value defaults and is not reversible safely.
    }
};
