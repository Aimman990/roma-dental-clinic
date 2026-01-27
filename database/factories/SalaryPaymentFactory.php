<?php

namespace Database\Factories;

use App\Models\SalaryPayment;
use App\Models\SalarySheet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalaryPaymentFactory extends Factory
{
    protected $model = SalaryPayment::class;

    public function definition()
    {
        $base = $this->faker->randomFloat(2, 200, 3000);
        $commission = $this->faker->randomFloat(2, 0, 1000);
        $deductions = $this->faker->randomFloat(2, 0, 200);
        return [
            'salary_sheet_id' => SalarySheet::factory(),
            'user_id' => User::factory(),
            'base_amount' => $base,
            'commission' => $commission,
            'deductions' => $deductions,
            'total_paid' => $base + $commission - $deductions,
        ];
    }
}
