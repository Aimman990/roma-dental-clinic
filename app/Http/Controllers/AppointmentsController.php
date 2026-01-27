<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Models\Reminder;
use Carbon\Carbon;

class AppointmentsController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $doctorId = $request->query('doctor_id');
        $patientId = $request->query('patient_id');
        $status = $request->query('status');
        $date = $request->query('date');
        $sort = $request->query('sort', 'start_at');
        $order = $request->query('order', 'asc');

        $query = Appointment::with('patient','doctor')
            ->when($date, function($q) use ($date){
                $q->whereDate('start_at', $date);
            })
            ->when($doctorId, function($q) use ($doctorId){
                $q->where('doctor_id', $doctorId);
            })
            ->when($patientId, function($q) use ($patientId){
                $q->where('patient_id', $patientId);
            })
            ->when($status, function($q) use ($status){
                $q->where('status', $status);
            })
            ->when($q, function($builder) use ($q) {
                $builder->where(function($sub) use ($q) {
                    $sub->whereHas('patient', function($p) use ($q) {
                        $p->whereRaw("CONCAT(first_name, ' ', COALESCE(last_name,'')) LIKE ?", ["%{$q}%"]);
                    })->orWhereHas('doctor', function($d) use ($q) {
                        $d->where('name', 'like', "%{$q}%");
                    })->orWhere('notes', 'like', "%{$q}%");
                });
            })
            ->orderBy($sort, $order);

        $appointments = $query->paginate(20);

        return response()->json($appointments);
    }

    public function store(StoreAppointmentRequest $request)
    {
        $appointment = Appointment::create($request->validated());

        // create a reminder 24 hours before appointment (if appointment start_at provided)
        if ($appointment->start_at) {
            try {
                $sendAt = Carbon::parse($appointment->start_at)->subDay();
                Reminder::create(['appointment_id' => $appointment->id, 'send_at' => $sendAt, 'sent' => false]);
            } catch (\Exception $e) {
                // ignore invalid date parsing
            }
        }
        return response()->json($appointment, 201);
    }

    public function show(Appointment $appointment)
    {
        return response()->json($appointment->load('patient','doctor','medicalRecord'));
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $appointment->update($request->validated());

        // update or create reminder based on new start_at
        if ($appointment->start_at) {
            try {
                $sendAt = Carbon::parse($appointment->start_at)->subDay();
                $rem = $appointment->reminders()->first();
                if ($rem) {
                    $rem->update(['send_at' => $sendAt, 'sent' => false]);
                } else {
                    Reminder::create(['appointment_id' => $appointment->id, 'send_at' => $sendAt, 'sent' => false]);
                }
            } catch (\Exception $e) {
                // ignore parse errors
            }
        }
        return response()->json($appointment);
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return response()->json(['message'=>'deleted']);
    }
}
