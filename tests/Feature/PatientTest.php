<?php

namespace Tests\Feature;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function the_application_returns_a_successful_response()
    {
        $this->withoutExceptionHandling();
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function a_patient_can_be_added()
    {
        $this->withoutExceptionHandling();
        $response = $this->post('/patient/create', [
            'first_name' => 'Markus',
            'last_name'  => 'Cobb',
            'birthdate'  => '2023-01-01 00:00:00',
            'age'        => '20',
            'age_type'   => '1',
        ]);

        $response->assertOk();
        $this->assertCount(1, Patient::all());
    }

    /** @test */
    public function a_patients_can_be_added_cache()
    {
        $this->withoutExceptionHandling();
        foreach (range(1,3) as $value) {
            $response = $this->post('/patient/create', [
                'first_name' => 'Markus',
                'last_name'  => 'Cobb',
                'birthdate'  => '2023-01-01 00:00:00',
                'age'        => $value,
                'age_type'   => '1',
            ]);
        }
        $response->assertOk();

        $response = $this->get('/patient/patients');
        $response->assertOk();

        $this->assertCount(3, $response->json());
    }

    /** @test */
    public function is_required()
    {
        $response = $this->post('/patient/create', [
            'first_name' => '',
            'last_name'  => '',
            'birthdate'  => '',
        ]);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'birthdate']);
    }
}
