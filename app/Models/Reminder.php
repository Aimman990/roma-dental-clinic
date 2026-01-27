<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = ['appointment_id','send_at','sent'];

    public function appointment(){ return $this->belongsTo(Appointment::class); }
}
