<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    use HasFactory;

    protected $fillable = ['salary_sheet_id','user_id','base_amount','commission','deductions','total_paid'];

    public function sheet()
    {
        return $this->belongsTo(SalarySheet::class, 'salary_sheet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
