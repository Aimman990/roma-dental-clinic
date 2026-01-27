<?php

namespace Database\Factories;

use App\Models\SalarySheet;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalarySheetFactory extends Factory
{
    protected $model = SalarySheet::class;

    public function definition()
    {
        return [
            'period' => now()->format('Y-m'),
            'total' => 0,
        ];
    }
}
