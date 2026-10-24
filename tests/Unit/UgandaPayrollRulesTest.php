<?php

namespace Tests\Unit;

use App\Services\HR\UgandaPayrollRules;
use PHPUnit\Framework\TestCase;

class UgandaPayrollRulesTest extends TestCase
{
    public function test_resident_paye_uses_current_progressive_monthly_bands_and_top_surcharge(): void
    {
        $rules = new UgandaPayrollRules();

        self::assertSame(0.0, $rules->paye(335_000));
        self::assertSame(15_000.0, $rules->paye(410_000));
        self::assertSame(33_750.0, $rules->paye(485_000));
        self::assertSame(2_888_250.0, $rules->paye(10_000_000));
        self::assertSame(3_288_250.0, $rules->paye(11_000_000));
    }

    public function test_non_resident_paye_uses_non_resident_bands(): void
    {
        $rules = new UgandaPayrollRules();

        self::assertSame(33_500.0, $rules->paye(335_000, 'non_resident'));
        self::assertSame(48_500.0, $rules->paye(410_000, 'non_resident'));
        self::assertSame(2_925_500.0, $rules->paye(10_000_000, 'non_resident'));
    }

    public function test_nssf_contributions_are_five_percent_employee_and_ten_percent_employer(): void
    {
        $rules = new UgandaPayrollRules();

        self::assertSame(50_000.0, $rules->nssfEmployee(1_000_000));
        self::assertSame(100_000.0, $rules->nssfEmployer(1_000_000));
    }
}
