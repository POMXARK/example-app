<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Models\Patient;
use App\Services\PatientService;

class PatientController extends Controller
{
    public function create(PatientRequest $request, PatientService $service)
    {
        $service->age($request->all());
    }

    public function getAllPatients(PatientService $service)
    {
        return $service->patients();
    }
}
