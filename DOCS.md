Пути развития:
1. понятные ошибки
2. преобразовать в api
3. задание не выполнено автоматически получать тип
4. подкючить redis для кеширование в docker compose
5. разобраться с логикой кеширования

Этапы разработки через тестирование:


```php

## **step 1 первый тест**

<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $this->withoutExceptionHandling();

        $response = $this->get('/');

        $response->assertStatus(200);

        $response = $this->get('/patient/create');
    }
}

(Symfony\Component\HttpKernel\Exception\NotFoundHttpException : GET http://localhost/patient/create)
## step 2 добавить маршрут route
Route::post('/patient/create');

## step 3 fix route
(LogicException : Route for [patient/create] has no action.)
Route::post('/patient/create', function () {
    return 0;
});
@@ green tests

## next
(Error : Class "PatientController" not found)
Route::post('/patient/create', PatientController::create());

## next
<?php

namespace App\Http\Controllers;

class PatientController extends Controller
{
}

use App\Http\Controllers\PatientController;
Route::post('/patient/create', PatientController::create());

## next
(Error : Call to undefined method App\Http\Controllers\PatientController::create())
class PatientController extends Controller
{
    public function create()
    {

    }
}

## next
(Error : Non-static method App\Http\Controllers\PatientController::create() cannot be called statically)
Route::post('/patient/create', [PatientController::class, 'create']);
@@ green tests

## next
    /** @test */
    public function a_patient_can_be_added()
    {
        $response = $this->post('/patient/create', [
            'first_name' => 'Markus',
            'last_name'  => 'Cobb',
            'birthdate'  => '16/11/2002',
        ]);

        $this->assertCount(1, Patient::all());
    }

## next
(Error : Class "Tests\Feature\Patient" not found)
php artisan make:model Patient -a
use App\Models\Patient;

## next
(Illuminate\Database\QueryException : could not find driver (SQL: PRAGMA foreign_keys = ON;))
sudo apt-get install php-sqlite3
sudo apt-get install php8.0-sqlite3
sudo apt-get install php8.2-sqlite3

## next
(Illuminate\Database\QueryException : SQLSTATE[HY000]: General error: 1 no such table: patients (SQL: select * from "patients"))
class PatientTest extends TestCase
{
    use RefreshDatabase;

## next
(Failed asserting that actual size 0 matches expected size 1.)
class PatientController extends Controller
{
    public function create()
    {
        Patient::create([
            'first_name' => request('first_name'),
            'last_name'  => request('last_name'),
            'birthdate'  => request('birthdate'),
                        ]);
    }
}
## next
(Illuminate\Database\Eloquent\MassAssignmentException : Add [first_name] to fillable property to allow mass assignment on [App\Models\Patient].)
class Patient extends Model
{
    use HasFactory;

    protected $fillable = ['first_name','last_name','birthdate'];
}

## next
(Illuminate\Database\QueryException : SQLSTATE[HY000]: General error: 1 table patients has no column named first_name (SQL: insert into "patients" ("first_name", "last_name", "birthdate", "updated_at", "created_at") values (Markus, Cobb, 16/11/2002, 2023-06-04 00:13:32, 2023-06-04 00:13:32)))
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->date('birthdate');
            $table->timestamps();
        });
    }
@@ green tests

## next
    /** @test */
    public function is_required()
    {
        $this->withoutExceptionHandling();
        $response = $this->post('/patient/create', [
            'first_name' => '',
            'last_name'  => '',
            'birthdate'  => '',
        ]);
    }

## next
(Illuminate\Database\QueryException : SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: patients.first_name (SQL: insert into "patients" ("first_name", "last_name", "birthdate", "updated_at", "created_at") values (?, ?, ?, 2023-06-04 00:22:52, 2023-06-04 00:22:52))
)
$response->assertSessionHasErrors(['first_name', 'last_name', 'birthdate']);

## next
(Illuminate\Database\QueryException : SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: patients.first_name (SQL: insert into "patients" ("first_name", "last_name", "birthdate", "updated_at", "created_at") values (?, ?, ?, 2023-06-04 00:25:26, 2023-06-04 00:25:26))
)
class PatientController extends Controller
{
    public function create()
    {
        $data = request()->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'birthdate' => 'required',
                                    ]);
        Patient::create($data);
    }
}

## next
(Illuminate\Validation\ValidationException : The first name field is required. (and 2 more errors))
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
@@ green tests

## next
<?php

namespace App\Services;

class PatientServiceProvider
{

}
class PatientController extends Controller
{
    public function create(PatientServiceProvider $service)
    {
        $data = request()->validate([
            'first_name' => 'required',
            'last_name'  => 'required',
            'birthdate'  => 'required',
                                    ]);
        Patient::create($data);
    }
}
@@ green tests

## next
class PatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'first_name' => 'required',
            'last_name'  => 'required',
            'birthdate'  => 'required',
        ];
    }
}
class PatientController extends Controller
{
    public function create(Patient $patient, PatientRequest $request, PatientServiceProvider $service)
    {
        Patient::create($patient);
    }
}

## next
(Illuminate\Auth\Access\AuthorizationException : This action is unauthorized.)
    public function authorize()
    {
        return true;
    }

## next
(TypeError : Illuminate\Database\Eloquent\Builder::create(): Argument #1 ($attributes) must be of type array, App\Models\Patient given,)
Patient::create($request);

## next
(TypeError : Illuminate\Database\Eloquent\Builder::create(): Argument #1 ($attributes) must be of type array, App\Models\Patient given,)
Patient::create($request->all());
@@ green tests

## next
    public function create(PatientRequest $request, PatientServiceProvider $service)
    {
        Patient::create($request->all());
    }
@@ green tests

## next
    /** @test */
    public function patient_service(PatientService $service)
    {
        $this->assertTrue(true);
    }

## next
(ArgumentCountError : Too few arguments to function Tests\Feature\PatientTest::patient_service(), 0 passed in /home/roman/test_tz/example-app/vendor/phpunit/phpunit/src/Framework/TestCase.php on line 1608 and exactly 1 expected)
    /** @test */
    public function patient_service()
    {
        $service = new PatientService();
        $this->assertTrue(true);
    }
@@ green tests

## next
    /** @test */
    public function patient_service()
    {
        $this->withoutExceptionHandling();
        $service = new PatientService();
        $service::determineAge();
        $this->assertTrue(true);
    }

## next
(Error : Call to undefined method App\Services\PatientService::determineAge())
class PatientService
{
    public static function determineAge()
    {
    }
}

@@ green tests

# next
    /** @test */
    public function patient_service()
    {
        $patient =  [
            'first_name' => 'Markus',
            'last_name'  => 'Cobb',
            'birthdate'  => '16/11/2002',
        ];

        $this->withoutExceptionHandling();
        $service = new PatientService();
        $service::determineAge($patient);
        $this->assertTrue(true);
    }

@@ green tests

## next
    /** @test */
    public function is_birthdate_less_than_a_month()
    {
        $patient =  [
            'first_name' => 'Markus',
            'last_name'  => 'Cobb',
            'birthdate'  => '16',
        ];

        $this->withoutExceptionHandling();
        $service = new PatientService();
        $age = $service::determineAge($patient);
        $this->assertEquals('16', $age);
    }

## next
(Failed asserting that null matches expected '16'.)
    public static function determineAge(array $patient)
    {
        return 16;
    }

@@ green tests

## next
    /** @test */
    public function diff_in_days()
    {
        $date=Carbon::parse('2023-01-10 00:00:00');
        $dateCurrent=Carbon::parse('2023-01-30 00:00:00');
        $countDays = Carbon::parse($date)->diffInDays($dateCurrent);
        $this->assertEquals(20, $countDays);
    }
@@ green tests

## next
    /** @test */
    public function age_to_days()
    {
        $patient =  [
            'first_name' => 'Markus',
            'last_name'  => 'Cobb',
            'birthdate'  => '2023-01-10 00:00:00',
        ];

        $dateCurrent = Carbon::parse('2023-01-30 00:00:00');

        $ageToDays =PatientService::determineAge($patient, $dateCurrent);
        $this->assertEquals(20, $ageToDays);
    }

    public static function determineAge(array $patient, string $dateCurrent = null): int
    {
        $birthdate = Carbon::createFromFormat('Y-m-d H:i:s', $patient['birthdate'], 'Europe/London');
        $countDays = Carbon::parse($dateCurrent ? Carbon::parse($dateCurrent) : Carbon::now())->diffInDays($birthdate);
        $numberMonth = Carbon::now()->format('M');
        dd($numberMonth);
        $daysInMonth = Carbon::createFromDate(null, $numberMonth, 1)->daysInMonth;
        switch (true){
            case $countDays < $daysInMonth:
                return 16;
        }

        return 16;
    }

## next
(Carbon\Exceptions\OutOfRangeException : month must be between 0 and 99, Jun given)
    public static function determineAge(array $patient, string $dateCurrent = null): int
    {
        $birthdate = Carbon::createFromFormat('Y-m-d H:i:s', $patient['birthdate'], 'Europe/London');
        $countDays = Carbon::parse($dateCurrent ? Carbon::parse($dateCurrent) : Carbon::now())->diffInDays($birthdate);
        $numberMonth = Carbon::now()->month;
        $daysInMonth = Carbon::createFromDate(null, $numberMonth, 1)->daysInMonth;
        switch (true){
            case $countDays < $daysInMonth:
                return 16;
        }

        return 16;
    }

## next
(Failed asserting that 16 matches expected 20.)
    public static function determineAge(array $patient, string $dateCurrent = null): int
    {
        $birthdate = Carbon::createFromFormat('Y-m-d H:i:s', $patient['birthdate'], 'Europe/London');
        $countDays = Carbon::parse($dateCurrent ? Carbon::parse($dateCurrent) : Carbon::now())->diffInDays($birthdate);
        $numberMonth = Carbon::now()->month;
        $daysInMonth = Carbon::createFromDate(null, $numberMonth, 1)->daysInMonth;
        switch (true){
            case $countDays < $daysInMonth:
                return 20;
        }
    }

@@ green tests


## next
    public static function determineAge(array $patient, string $dateCurrent = null): int
    {
        $birthdate = Carbon::createFromFormat('Y-m-d H:i:s', $patient['birthdate'], 'Europe/London');
        $countDays = Carbon::parse($dateCurrent ? Carbon::parse($dateCurrent) : Carbon::now())->diffInDays($birthdate);
        $numberMonth = Carbon::now()->month;
        $daysInMonth = Carbon::createFromDate(null, $numberMonth, 1)->daysInMonth;
        switch (true){
            case $countDays < $daysInMonth:
                return $countDays;
        }
    }
@@ green tests

## next
    /** @test */
    public function age_to_month()
    {
        $patient =  [
            'first_name' => 'Markus',
            'last_name'  => 'Cobb',
            'birthdate'  => '2023-01-10 00:00:00',
        ];

        $dateCurrent = Carbon::parse('2023-02-30 00:00:00');

        $ageToDays = PatientService::determineAge($patient, $dateCurrent);
        $this->assertEquals(20, $ageToDays);
    }

## next
(TypeError : App\Services\PatientService::determineAge(): Return value must be of type int, none returned)
    public static function determineAge(array $patient, string $dateCurrent = null): int
    {
        $birthdate = Carbon::createFromFormat('Y-m-d H:i:s', $patient['birthdate'], 'Europe/London');
        $countDays = Carbon::parse($dateCurrent ? Carbon::parse($dateCurrent) : Carbon::now())->diffInDays($birthdate);
        $numberMonth = Carbon::now()->month;
        $daysInMonth = Carbon::createFromDate(null, $numberMonth, 1)->daysInMonth;
        switch (true){
            case $countDays < $daysInMonth:
                return $countDays;
            case $countDays > $daysInMonth:
                return $countDays;
        }
    }

## next
    /** @test */
    public function exact_number_of_days()
    {
        $patient =  [
            'first_name' => 'Markus',
            'last_name'  => 'Cobb',
            'birthdate'  => '2023-01-01 00:00:00',
        ];

        $dateCurrent = Carbon::parse('2023-02-12 00:00:00');
        $birthdate = Carbon::createFromFormat('Y-m-d H:i:s', $patient['birthdate'], 'Europe/London');
        $numberMonth = $dateCurrent->month;

        $totalDays = PatientService::exactNumberOfDays(range($birthdate->month, $numberMonth));
        $this->assertEquals(43, $totalDays);
    }

## next
(Failed asserting that null matches expected 43.)
    /** @test */
    public function exact_number_of_days()
    {
        $patient =  [
            'birthdate'  => '2023-01-01 00:00:00',
        ];

        $dateCurrent = Carbon::parse('2023-02-12 00:00:00');
        $birthdate = Carbon::createFromFormat('Y-m-d H:i:s', $patient['birthdate'], 'Europe/London');
        $numberMonth = $dateCurrent->month;

        $totalDays = PatientService::exactNumberDays(range($birthdate->month, $numberMonth), $birthdate, $dateCurrent);
        $this->assertEquals(43, $totalDays);
    }

    public static function exactNumberDays(array $MonthRange, Carbon $birthdate, Carbon $dateCurrent = null)
    {
        $daysUntilEndBirthdateMonth = count(range($birthdate->day, $birthdate->daysInMonth));
        $daysSinceStartCurrentMonth = count(range(1, $dateCurrent ? $dateCurrent->day : Carbon::now()->day));

        return $daysUntilEndBirthdateMonth + $daysSinceStartCurrentMonth;
    }

@@ green tests

## next
    /** @test */
    public function exact_more_number_of_days()
    {
        $patient =  [
            'birthdate'  => '2023-01-01 00:00:00',
        ];

        $dateCurrent = Carbon::parse('2023-03-12 00:00:00');
        $birthdate = Carbon::createFromFormat('Y-m-d H:i:s', $patient['birthdate'], 'Europe/London');
        $numberMonth = $dateCurrent->month;

        $totalDays = PatientService::exactNumberDays(range($birthdate->month, $numberMonth), $birthdate, $dateCurrent);
        $this->assertEquals(43, $totalDays);
    }

    public static function exactNumberDays(array $MonthRange, Carbon $birthdate, Carbon $dateCurrent = null)
    {
        $daysUntilEndBirthdateMonth = count(range($birthdate->day, $birthdate->daysInMonth));
        $daysSinceStartCurrentMonth = count(range(1, $dateCurrent ? $dateCurrent->day : Carbon::now()->day));

        $betweenTotalDays = 0;
        for ($i = 1; $i <= count($MonthRange) - 2; $i++) {
            $daysInMonth = Carbon::createFromDate(null, $MonthRange[$i], 1)->daysInMonth;
            $betweenTotalDays += $daysInMonth;
        }

        return $daysUntilEndBirthdateMonth + $betweenTotalDays + $daysSinceStartCurrentMonth;
    }

## next
(Failed asserting that 71 matches expected 43.)
    /** @test */
    public function exact_more_months_number_of_days()
    {
        $patient =  [
            'birthdate'  => '2023-01-01 00:00:00',
        ];

        $dateCurrent = Carbon::parse('2023-03-12 00:00:00');
        $birthdate = Carbon::createFromFormat('Y-m-d H:i:s', $patient['birthdate'], 'Europe/London');
        $numberMonth = $dateCurrent->month;

        $totalDays = PatientService::exactNumberDays(range($birthdate->month, $numberMonth), $birthdate, $dateCurrent);
        $this->assertEquals(71, $totalDays);
    }

@@ green tests

## next
(Failed asserting that 43 matches expected 377.)
    /** @test */
    public function exact_more_years_number_of_days()
    {
        $patient =  [
            'birthdate'  => '2022-01-01 00:00:00',
        ];

        $birthdate = Carbon::parse($patient['birthdate']);
        $dateCurrent = Carbon::parse('2023-01-12 00:00:00');
        $numberMonth = $dateCurrent->month;
        // TODO: range не учитывает переполнение 1 - 12
        $totalDays = PatientService::exactNumberDays(range($dateCurrent->month, $numberMonth), $birthdate, $dateCurrent);
        $this->assertEquals(377, $totalDays);
    }

## next (global refactoring)
    /** @test */
    public function age_to_days()
    {
        $patient =  [
            'birthdate'  => '2023-01-10 00:00:00',
        ];

        $dateCurrent = Carbon::parse('2023-01-30 00:00:00');

        $ageToDays = PatientService::determineAge($patient, $dateCurrent);
        $this->assertEquals(20, $ageToDays);
    }

    /** @test */
    public function age_to_month()
    {
        $patient =  [
            'birthdate'  => '2023-01-01 00:00:00',
        ];

        $dateCurrent = Carbon::parse('2023-03-01 00:00:00');

        $ageToMonth = PatientService::determineAge($patient, $dateCurrent);
        $this->assertEquals(2, $ageToMonth);
    }

    /** @test */
    public function age_to_month_advanced()
    {
        $patient =  [
            'birthdate'  => '2023-01-15 00:00:00',
        ];

        $dateCurrent = Carbon::parse('2023-04-25 00:00:00');

        $ageToMonth = PatientService::determineAge($patient, $dateCurrent);
        $this->assertEquals(3, $ageToMonth);
    }

    /** @test */
    public function age_to_years()
    {
        $patient =  [
            'birthdate'  => '2020-01-01 00:00:00',
        ];

        $dateCurrent = Carbon::parse('2023-03-01 00:00:00');

        $ageToYears = PatientService::determineAge($patient, $dateCurrent);
        $this->assertEquals(3, $ageToYears);
    }

class PatientService
{
    public static function determineAge(array $patient, string $dateCurrent = null): int
    {
        $birthdate = Carbon::parse($patient['birthdate']);
        $dateCurrent = $dateCurrent ? Carbon::parse($dateCurrent) : Carbon::now();
        $interval = $dateCurrent->diff($birthdate);
        $countDays = $interval->days;

        return match (true) {
            $countDays > $dateCurrent->daysInYear => $interval->y,
            $countDays > $dateCurrent->daysInMonth => $interval->m,
            default => $countDays,
        };
    }
}

@@ green tests

## next
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

## next
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->date('birthdate');
            $table->unsignedInteger('age');
            $table->unsignedInteger('age_type');
            $table->timestamps();
        });
    }


## next
(Illuminate\Database\QueryException : SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: patients.age (SQL: insert into "patients" ("first_name", "last_name", "birthdate", "updated_at", "created_at") values (Markus, Cobb, 16/11/2002, 2023-06-04 23:53:17, 2023-06-04 23:53:17))
class Patient extends Model
{
    use HasFactory;

    protected $fillable = ['first_name','last_name','birthdate', 'age', 'age_type'];
}
    /** @test */
    public function a_patient_can_be_added()
    {
        $this->withoutExceptionHandling();
        $response = $this->post('/patient/create', [
            'first_name' => 'Markus',
            'last_name'  => 'Cobb',
            'birthdate'  => '16/11/2002',
            'age'        => '20',
            'age_type'   => '1',
        ]);

        $response->assertOk();
        $this->assertCount(1, Patient::all());
    }
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->date('birthdate');
            $table->unsignedInteger('age');
            $table->unsignedInteger('age_type');
            $table->timestamps();
        });
    }

@@ green tests

## next
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

@@ green tests

## next
    /** @test */
    public function a_patients_can_be_added_cache()
    {
        $this->withoutExceptionHandling();
        $response = $this->post('/patient/create', [
            'first_name' => 'Markus',
            'last_name'  => 'Cobb',
            'birthdate'  => '2023-01-01 00:00:00',
        ]);

        $response = $this->post('/patient/create', [
            'first_name' => 'John',
            'last_name'  => 'Cobb',
            'birthdate'  => '2020-01-01 00:00:00',
        ]);

        $response->assertOk();
        $this->assertCount(1, Patient::all());
    }

## next
(Illuminate\Validation\ValidationException : The age field is required. (and 1 more error))
    /** @test */
    public function a_patients_can_be_added_cache()
    {
        $this->withoutExceptionHandling();
        $response = $this->post('/patient/create', [
            'first_name' => 'Markus',
            'last_name'  => 'Cobb',
            'birthdate'  => '2023-01-01 00:00:00',
        ]);

        $response = $this->post('/patient/create', [
            'first_name' => 'John',
            'last_name'  => 'Cobb',
            'birthdate'  => '2020-01-01 00:00:00',
        ]);

        $response->assertOk();
        $this->assertCount(2, Patient::all());
    }

    public function rules()
    {
        return [
            'first_name' => 'required',
            'last_name'  => 'required',
            'birthdate'  => 'required',
            'age'        => '',
            'age_type'   => '',
        ];
    }

    public function age(array $patient, string $dateCurrent = null)
    {
        $res = self::determineAge($patient, $dateCurrent);
        $patient['age'] = $res['age'];
        $patient['age_type'] = self::AGE_TYPES[$res['age_type']];
        $entity = Patient::create($patient);
        Cache::put( $entity->birthdate . $entity->updated_at, $entity, now()->addMinutes(5));
        PatientJob::dispatch($entity);
    }

@@ green tests

## next

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

@@ green tests

## next
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

    public function getAllPatients(PatientService $service)
    {
        return $service->patients();
    }

Route::get('/patient/patients', [PatientController::class, 'getAllPatients']);

@@ green tests
```
