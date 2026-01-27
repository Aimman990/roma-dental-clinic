<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalRecordRequest;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class MedicalRecordsController extends Controller
{
    public function index(Request $request)
    {
        $records = MedicalRecord::with('patient','doctor')->paginate(25);
        return response()->json($records);
    }

    public function store(StoreMedicalRecordRequest $request)
    {
        $data = $request->validated();
        $record = MedicalRecord::create($data);
        return response()->json($record, 201);
    }

    public function show(MedicalRecord $medicalRecord)
    {
        return response()->json($medicalRecord->load('patient','doctor','appointment'));
    }
}
