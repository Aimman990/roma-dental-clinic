<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function index(Request $request)
    {
        $services = Service::with('doctor')->paginate(20);
        return response()->json($services);
    }

    public function store(StoreServiceRequest $request)
    {
        $service = Service::create($request->validated());
        return response()->json($service, 201);
    }

    public function show(Service $service)
    {
        return response()->json($service->load('doctor'));
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        $service->update($request->validated());
        return response()->json($service);
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return response()->json(['message' => 'deleted']);
    }
}
