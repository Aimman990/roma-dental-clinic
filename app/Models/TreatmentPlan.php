<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentPlan extends Model
{
    protected $guarded = [];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function procedures()
    {
        return $this->hasMany(TreatmentPlanProcedure::class);
    }
}
