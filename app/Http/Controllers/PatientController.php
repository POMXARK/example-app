<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Models\Patient;
use App\Services\PatientService;

class PatientController extends Controller
{
    /**
     * Записать данные по пациенту
     *
     * @param PatientRequest $request
     * @param PatientService $service
     * @return Patient|string
     */
    public function create(PatientRequest $request, PatientService $service): Patient|string
    {
        return $service->create($request->all());
    }

    public function getAllPatients(PatientService $service): array|string
    {
        return $service->patients();
    }
}
