<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientsController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $patients = Patient::with('creator')->when($q, function ($query, $q) {
            $query->where('first_name', 'like', "%$q%")
                ->orWhere('last_name', 'like', "%$q%")
                ->orWhere('phone', 'like', "%$q%")
                ->orWhere('national_id', 'like', "%$q%");
        })
        ->orderBy($request->get('sort', 'created_at'), $request->get('order', 'desc'))
        ->paginate(15);

        return response()->json($patients);
    }

    public function store(StorePatientRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()?->id ?? null;
        $patient = Patient::create($data);
        return response()->json($patient, 201);
    }

    public function show(Patient $patient)
    {
        return response()->json($patient->load('appointments','medicalRecords','invoices','creator','treatmentPlans.procedures'));
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());
        return response()->json($patient);
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->json(['message' => 'deleted']);
    }
}
