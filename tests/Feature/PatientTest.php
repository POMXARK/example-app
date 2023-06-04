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
            'birthdate'  => '16/11/2002',
        ]);

        $response->assertOk();
        $this->assertCount(1, Patient::all());
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
