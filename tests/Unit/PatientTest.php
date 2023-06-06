<?php

namespace Tests\Unit;

use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_patients_can_be_added_cache()
    {
        foreach (range(1,3) as $value) {
            $this->withoutExceptionHandling();
            $entity = Patient::create([
                                          'first_name' => 'Markus',
                                          'last_name'  => 'Cobb',
                                          'birthdate'  => '2023-01-01 00:00:00',
                                          'age'        => $value,
                                          'age_type'   => '1',
                                      ]);

            $patients = (Cache::get('patients', []));

            $patients[] = $entity;

            Cache::put('patients', $patients, now()->addSeconds(10));

            $ids = [];
            foreach (Cache::get('patients') as $patient) {
                $ids[] = $patient->id;
            }
        }

        $this->assertEquals([1,2, 3] , $ids);
    }

    /** @test */
    public function age_to_days()
    {
        $patient =  [
            'birthdate'  => '2023-01-10 00:00:00',
        ];

        $dateCurrent = Carbon::parse('2023-01-30 00:00:00');

        $ageToDays = PatientService::determineAge($patient, $dateCurrent);
        $this->assertEquals(['age' => 20, 'age_type' => 'день'], $ageToDays);
    }

    /** @test */
    public function age_to_month()
    {
        $patient =  [
            'birthdate'  => '2023-01-01 00:00:00',
        ];

        $dateCurrent = Carbon::parse('2023-03-01 00:00:00');

        $ageToMonth = PatientService::determineAge($patient, $dateCurrent);
        $this->assertEquals(['age' => 2, 'age_type' => 'месяц'], $ageToMonth);
    }

    /** @test */
    public function age_to_month_advanced()
    {
        $patient =  [
            'birthdate'  => '2023-01-15 00:00:00',
        ];

        $dateCurrent = Carbon::parse('2023-04-25 00:00:00');

        $ageToMonth = PatientService::determineAge($patient, $dateCurrent);
        $this->assertEquals(['age' => 3, 'age_type' => 'месяц'], $ageToMonth);
    }

    /** @test */
    public function age_to_years()
    {
        $patient =  [
            'birthdate'  => '2020-01-01 00:00:00',
        ];

        $dateCurrent = Carbon::parse('2023-03-01 00:00:00');

        $ageToYears = PatientService::determineAge($patient, $dateCurrent);
        $this->assertEquals(['age' => 3, 'age_type' => 'лет'], $ageToYears);
    }
}
