<?php

namespace App\Services\HR;

final class UgandaPayrollRules
{
    /**
     * Monthly PAYE calculation based on the URA rates verified 24 October 2026.
     * Rates effective from 1 July 2026 use the UGX 335,000 threshold.
     */
    public function paye(float $taxableMonthlyIncome, string $residency = 'resident'): float
    {
        $income = max(0, $taxableMonthlyIncome);

        if ($residency === 'non_resident') {
            $tax = match (true) {
                $income <= 335_000 => $income * 0.10,
                $income <= 410_000 => 33_500 + (($income - 335_000) * 0.20),
                default => 48_500 + (($income - 410_000) * 0.30),
            };
        } else {
            $tax = match (true) {
                $income <= 335_000 => 0,
                $income <= 410_000 => ($income - 335_000) * 0.20,
                $income <= 485_000 => 15_000 + (($income - 410_000) * 0.25),
                default => 33_750 + (($income - 485_000) * 0.30),
            };
        }

        if ($income > 10_000_000) {
            $tax += ($income - 10_000_000) * 0.10;
        }

        return round($tax, 2);
    }

    public function nssfEmployee(float $grossMonthlyWage): float
    {
        return round(max(0, $grossMonthlyWage) * 0.05, 2);
    }

    public function nssfEmployer(float $grossMonthlyWage): float
    {
        return round(max(0, $grossMonthlyWage) * 0.10, 2);
    }
}
