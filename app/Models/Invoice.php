<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_number','patient_id','appointment_id','doctor_id','subtotal','discount','tax','total','remaining','payment_method','status'];

    public function doctor()
    {
        return $this->belongsTo(\App\Models\User::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function adjustments()
    {
        return $this->hasMany(\App\Models\Adjustment::class);
    }
}
