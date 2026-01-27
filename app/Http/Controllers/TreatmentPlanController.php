<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Service;
use App\Models\TreatmentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreatmentPlanController extends Controller
{
    public function index()
    {
        $plans = TreatmentPlan::with(['patient', 'doctor'])->latest()->get();
        return view('treatment_plans.index', compact('plans'));
    }

    public function create()
    {
        $patients = Patient::all();
        $services = Service::all();
        $doctors = \App\Models\User::where('role', 'doctor')->select('id','name')->get();
        return view('treatment_plans.create', compact('patients', 'services', 'doctors'));
    }

    public function edit(TreatmentPlan $treatmentPlan)
    {
        $patients = Patient::all();
        $services = Service::all();
        $doctors = \App\Models\User::where('role', 'doctor')->select('id','name')->get();
        $treatmentPlan->load('procedures');
        return view('treatment_plans.edit', compact('treatmentPlan', 'patients', 'services', 'doctors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'procedures' => 'required|array|min:1',
            'procedures.*.service_id' => 'nullable|exists:services,id',
            'procedures.*.procedure_name' => 'required|string',
            'procedures.*.tooth_number' => 'nullable|string',
            'procedures.*.estimated_cost' => 'required|numeric|min:0',
            'procedures.*.session_number' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            $plan = TreatmentPlan::create([
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'] ?? auth()->id(),
                'status' => 'proposed',
                'notes' => $validated['notes'] ?? null,
                'total_estimated_cost' => collect($validated['procedures'])->sum('estimated_cost'),
            ]);

            foreach ($validated['procedures'] as $proc) {
                $plan->procedures()->create($proc);
            }
        });

        return redirect()->route('treatment-plans.index')->with('success', 'تم إنشاء خطة العلاج بنجاح');
    }

    public function show(TreatmentPlan $treatmentPlan)
    {
        $treatmentPlan->load(['patient', 'procedures', 'doctor']);
        return view('treatment_plans.show', compact('treatmentPlan'));
    }

    public function update(Request $request, TreatmentPlan $treatmentPlan)
    {
        // If procedures are present, treat this as a full edit; otherwise handle status update
        if ($request->has('procedures')) {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'doctor_id' => 'nullable|exists:users,id',
                'notes' => 'nullable|string',
                'procedures' => 'required|array|min:1',
                'procedures.*.service_id' => 'nullable|exists:services,id',
                'procedures.*.procedure_name' => 'required|string',
                'procedures.*.tooth_number' => 'nullable|string',
                'procedures.*.estimated_cost' => 'required|numeric|min:0',
                'procedures.*.session_number' => 'required|integer|min:1',
            ]);

            DB::transaction(function () use ($validated, $treatmentPlan) {
                $treatmentPlan->update([
                    'patient_id' => $validated['patient_id'],
                    'doctor_id' => $validated['doctor_id'] ?? $treatmentPlan->doctor_id,
                    'notes' => $validated['notes'] ?? $treatmentPlan->notes,
                    'total_estimated_cost' => collect($validated['procedures'])->sum('estimated_cost'),
                ]);

                // replace procedures
                $treatmentPlan->procedures()->delete();
                foreach ($validated['procedures'] as $proc) {
                    $treatmentPlan->procedures()->create($proc);
                }
            });

            return redirect()->route('treatment-plans.show', $treatmentPlan)->with('success', 'تم تحديث خطة العلاج');
        }

        // Otherwise only status/notes update
        $validated = $request->validate([
            'status' => 'required|in:proposed,accepted,rejected,completed',
            'notes' => 'nullable|string'
        ]);

        $treatmentPlan->update($validated);

        return back()->with('success', 'تم تحديث حالة الخطة');
    }
    
    public function destroy(TreatmentPlan $treatmentPlan)
    {
        $treatmentPlan->delete();
        return redirect()->route('treatment-plans.index')->with('success', 'تم حذف الخطة');
    }
}
