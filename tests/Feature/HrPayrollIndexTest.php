<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrPayrollIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_payroll_page_loads_with_no_existing_payroll_data(): void
    {
        $property = Property::factory()->create();
        $user = User::factory()->create(['property_id' => $property->id]);

        $this->actingAs($user)
            ->get('/hr/payroll')
            ->assertOk()
            ->assertSee('Generate payroll draft')
            ->assertSee('No payroll runs yet.');
    }
}
