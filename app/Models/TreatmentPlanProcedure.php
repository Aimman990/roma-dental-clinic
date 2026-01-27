<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentPlanProcedure extends Model
{
    protected $guarded = [];

    public function plan()
    {
        return $this->belongsTo(TreatmentPlan::class, 'treatment_plan_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
