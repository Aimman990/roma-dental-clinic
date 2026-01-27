<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = ['category','title','description','amount','incurred_on','receipt_path','recorded_by'];

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
