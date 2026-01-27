<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name','last_name','national_id','phone','email','birthdate','gender','notes','created_by'
    ];

    // Accessor for full name
    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function treatmentPlans()
    {
        return $this->hasMany(\App\Models\TreatmentPlan::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
