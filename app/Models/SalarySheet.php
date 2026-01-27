<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalarySheet extends Model
{
    use HasFactory;

    protected $fillable = ['period','total'];

    public function payments()
    {
        return $this->hasMany(SalaryPayment::class);
    }
}
