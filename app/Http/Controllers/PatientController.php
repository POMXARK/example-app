<?php

namespace App\Http\Controllers;

use App\Models\Patient;

class PatientController extends Controller
{
    public function create()
    {
        $data = request()->validate([
            'first_name' => 'required',
            'last_name'  => 'required',
            'birthdate'  => 'required',
                                    ]);
        Patient::create($data);
    }
}
