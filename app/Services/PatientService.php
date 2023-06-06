<?php

namespace App\Services;

use App\Jobs\PatientJob;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PatientService
{
    const AGE_TYPES = ['день' => 1, 'месяц' => 2, 'лет' => 3];
    public function age(array $patient, string $dateCurrent = null)
    {
        $res = self::determineAge($patient, $dateCurrent);
        $patient['age'] = $res['age'];
        $patient['age_type'] = self::AGE_TYPES[$res['age_type']];
        $entity = Patient::create($patient);

        $patients = (Cache::get('patients', []));
        $patients[] = $entity;
        Cache::put('patients', $patients, now()->addSeconds(10));

        PatientJob::dispatch($entity);
    }
    public static function determineAge(array $patient, string $dateCurrent = null): array
    {
        $birthdate = Carbon::parse($patient['birthdate']);
        $dateCurrent = $dateCurrent ? Carbon::parse($dateCurrent) : Carbon::now();
        $interval = $dateCurrent->diff($birthdate);
        $countDays = $interval->days;

        return match (true) {
            $countDays > $dateCurrent->daysInYear  => ['age' => $interval->y, 'age_type' => 'лет'],
            $countDays > $dateCurrent->daysInMonth => ['age' => $interval->m, 'age_type' => 'месяц'],
            default => ['age' => $countDays, 'age_type' => 'день'],
        };
    }

    public static function getAllPatients()
    {
        $ids = [];
        foreach (Cache::get('patients') as $patient) {
            $ids[] = $patient->id;
        }

        return Cache::get('patients') + Patient::whereNotIn('id', $ids)->get()->toArray();
    }

    public function patients() {
        $out = [];
        $patients = self::getAllPatients();
        foreach ($patients as $patient) {
            $fullName = $patient['first_name'] . $patient['last_name'];
            $birthdate = Carbon::parse($patient['birthdate'])->format('d.m.Y');
            $age = $patient['age'] . ' ' . array_search($patient['age_type'],self::AGE_TYPES);
            $out[] = ['fullName' => $fullName, 'birthdate' => $birthdate, 'age' => $age];
        }
        return $out;
    }
}
