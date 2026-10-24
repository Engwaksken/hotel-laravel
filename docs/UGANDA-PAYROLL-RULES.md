# Uganda payroll statutory rules

The HR payroll engine is configured for Uganda, checked against the official sources on **24 October 2026**.

## PAYE (monthly chargeable income)

Resident PAYE uses the progressive monthly bands shown by the Uganda Revenue Authority (URA):

- UGX 0–335,000: nil
- UGX 335,001–410,000: 20% of the amount above UGX 335,000
- UGX 410,001–485,000: UGX 15,000 plus 25% above UGX 410,000
- UGX 485,001–10,000,000: UGX 33,750 plus 30% above UGX 485,000
- Above UGX 10,000,000: the previous band calculation plus 10% of the amount above UGX 10,000,000

Non-resident PAYE uses the URA non-resident bands: 10% up to UGX 335,000; UGX 33,500 plus 20% from UGX 335,000 to 410,000; UGX 48,500 plus 30% from UGX 410,000 to UGX 10,000,000; and an additional 10% on income above UGX 10,000,000.

PAYE is computed on basic salary plus earning components marked taxable. The employee record's `tax_residency` selects resident or non-resident rates.

## NSSF

The National Social Security Fund's published contribution rate is 5% employee contribution plus 10% employer contribution, calculated on gross monthly wages. This system calculates the employee share as a deduction and the employer share as an employer cost. `nssf_eligible` can be disabled per employee where the employer has verified a lawful exemption.

## Operational checks before live payroll

- Confirm each employee's tax-residency status and NSSF eligibility.
- Mark all taxable allowances and benefits correctly; seeded transport and housing allowances are marked taxable.
- Confirm salary inputs and payroll periods represent a monthly payroll.
- Review generated employee-level breakdowns before approval and filing.
- Check the current URA/NSSF instructions and consult a qualified payroll adviser for special cases or legal amendments before production payroll.

Sources:

- [URA 2026 PAYE amendment notice](https://ura.go.ug/en/changes-to-paye-return-form-following-the-income-tax-amendment-act-2026/)
- [URA PAYE rates](https://ura.go.ug/en/domestic-taxes/paye-rates/)
- [NSSF membership and contributions](https://www.nssfug.org/about-us/membership/)
