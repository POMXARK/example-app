<?php

namespace App\Services;

use App\Jobs\PatientJob;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PatientService
{
    const SECONDS = 60 * 5;
    const AGE_TYPES = ['день' => 1, 'месяц' => 2, 'лет' => 3];

    /**
     * Записать данные по пациенту в БД, в кэш, в очередь
     *
     * @param array $patient
     * @param string|null $dateCurrent
     * @return Patient|string
     */
    public function create(array $patient, string $dateCurrent = null): Patient|string
    {
        try {
            $res = self::determineAge($patient, $dateCurrent);
            $patient['age'] = $res['age'];
            $patient['age_type'] = self::AGE_TYPES[$res['age_type']];
            $entity = Patient::create($patient);
            Cache::forget('patients');
            PatientJob::dispatch($entity);
            Cache::set('patients' . $entity->id, $entity, self::SECONDS);

            return $entity;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Расчитать значение полей: age, age_type
     *
     * @param array $patient
     * @param string|null $dateCurrent
     * @return array
     */
    private static function determineAge(array $patient, string $dateCurrent = null): array
    {
        $birthdate = Carbon::parse($patient['birthdate']);
        $dateCurrent = $dateCurrent ? Carbon::parse($dateCurrent) : Carbon::now();
        $interval = $dateCurrent->diff($birthdate);
        $countDays = $interval->days;

        return match (true) {
            $countDays > $dateCurrent->daysInYear => ['age' => $interval->y, 'age_type' => 'лет'],
            $countDays > $dateCurrent->daysInMonth => ['age' => $interval->m, 'age_type' => 'месяц'],
            default => ['age' => $countDays, 'age_type' => 'день'],
        };
    }

    /**
     * Данные из кэша и отсутвующие в кэше из БД
     *
     * @return array|string
     */
    public function patients(): array|string
    {
        try {
            $out = [];
            $patients = Cache::remember('patients', self::SECONDS , function () {
                sleep(5); // имитация нагрузки на БД
                return Patient::all();
            });
            foreach ($patients as $patient) {
                $id = $patient['id'];
                $fullName = $patient['first_name'] . ' ' . $patient['last_name'];
                $birthdate = Carbon::parse($patient['birthdate'])->format('d.m.Y');
                $age = $patient['age'] . ' ' . array_search($patient['age_type'], self::AGE_TYPES);
                $out[] = ['id' => $id, 'fullName' => $fullName, 'birthdate' => $birthdate, 'age' => $age];
            }

            return $out;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
