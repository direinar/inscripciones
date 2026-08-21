<?php

namespace Tests\Feature;

use App\Models\CampusScheduleOption;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\JornadaOption;
use App\Models\Municipality;
use App\Models\PeriodOption;
use App\Models\ProgramOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalogsAndLocation(): array
    {
        PeriodOption::create([
            'name' => '2026-1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        CampusScheduleOption::create([
            'name' => 'Vegachí',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        JornadaOption::create([
            'name' => 'Fin de semana',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        ProgramOption::create([
            'name' => 'TL Agente de Tránsito',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $department = Department::create([
            'name' => 'Antioquia',
            'is_active' => true,
        ]);

        $municipality = Municipality::create([
            'department_id' => $department->id,
            'name' => 'Medellín',
            'is_active' => true,
        ]);

        return [
            'department_id' => $department->id,
            'municipality_id' => $municipality->id,
        ];
    }

    private function enrollmentPayload(array $overrides = []): array
    {
        $location = $this->seedCatalogsAndLocation();

        return array_merge([
            'period' => '2026-1',
            'campus' => 'Vegachí',
            'jornada' => 'Fin de semana',
            'program' => 'TL Agente de Tránsito',
            'first_name' => 'Laura',
            'middle_name' => 'Sofia',
            'last_name' => 'Reina',
            'second_last_name' => 'Martinez',
            'document_type' => 'Cédula de Ciudadanía',
            'document_number' => '123456789',
            'sex' => 'Femenino',
            'email' => 'laura@example.com',
            'mobile' => '3001234567',
            'birth_date' => '2000-05-10',
            'address' => 'Calle 10 # 20-30',
            'residence_department_id' => $location['department_id'],
            'residence_municipality_id' => $location['municipality_id'],
        ], $overrides);
    }

    public function test_public_user_can_submit_an_enrollment(): void
    {
        $response = $this->post('/inscripciones', $this->enrollmentPayload());

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('enrollments', [
            'document_number' => '123456789',
            'email' => 'laura@example.com',
            'program' => 'TL Agente de Tránsito',
        ]);
    }

    public function test_create_view_uses_a_relative_municipalities_endpoint(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('/municipios-por-departamento')
            ->assertDontSee('http://localhost/municipios-por-departamento');
    }

    public function test_marketing_user_can_view_enrollment_reports(): void
    {
        /** @var User $mercadeo */
        $mercadeo = User::factory()->create(['role' => 'mercadeo']);
        Enrollment::create($this->enrollmentPayload());

        $this->actingAs($mercadeo)
            ->get('/reportes/inscripciones')
            ->assertOk()
            ->assertSee('Reporte de inscripciones')
            ->assertSee('Laura');
    }

    public function test_admin_can_export_enrollment_reports_as_excel(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        Enrollment::create($this->enrollmentPayload());

        $response = $this->actingAs($admin)
            ->get('/reportes/inscripciones/exportar');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
    }

    public function test_marketing_user_can_view_financial_report_by_movement_dates(): void
    {
        /** @var User $mercadeo */
        $mercadeo = User::factory()->create(['role' => 'mercadeo']);
        $enrollment = Enrollment::create($this->enrollmentPayload());

        $this->actingAs($mercadeo)
            ->patch('/reportes/inscripciones/' . $enrollment->id . '/pagos', [
                'movement_type' => 'payment',
                'concept' => 'inscription',
                'movement_date' => '2026-08-18',
                'movement_amount' => 60000,
            ])
            ->assertRedirect('/reportes/inscripciones');

        $this->actingAs($mercadeo)
            ->get('/reportes/financieros?payment_date_from=2026-08-18&payment_date_to=2026-08-18')
            ->assertOk()
            ->assertSee('Reporte financiero')
            ->assertSee('Laura')
            ->assertSee('Pago');
    }

    public function test_marketing_user_can_update_payment_status_and_activate_student(): void
    {
        /** @var User $mercadeo */
        $mercadeo = User::factory()->create(['role' => 'mercadeo']);
        $enrollment = Enrollment::create($this->enrollmentPayload());

        $this->actingAs($mercadeo)
            ->patch('/reportes/inscripciones/' . $enrollment->id . '/pagos', [
                'movement_type' => 'payment',
                'concept' => 'inscription',
                'movement_date' => '2026-08-18',
                'movement_amount' => 60000,
            ])
            ->assertRedirect('/reportes/inscripciones');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'paid_inscription' => true,
            'paid_tuition' => false,
            'student_status' => 'inscrito',
        ]);

        $this->actingAs($mercadeo)
            ->patch('/reportes/inscripciones/' . $enrollment->id . '/pagos', [
                'movement_type' => 'payment',
                'concept' => 'tuition',
                'movement_date' => '2026-08-19',
                'movement_amount' => 185000,
            ])
            ->assertRedirect('/reportes/inscripciones');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'paid_inscription' => true,
            'paid_tuition' => true,
            'student_status' => 'matriculado',
        ]);

        $this->actingAs($mercadeo)
            ->patch('/reportes/inscripciones/' . $enrollment->id . '/pagos', [
                'movement_type' => 'refund',
                'concept' => 'tuition',
                'movement_date' => '2026-08-20',
                'movement_amount' => 185000,
            ])
            ->assertRedirect('/reportes/inscripciones');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'paid_inscription' => true,
            'paid_tuition' => false,
            'student_status' => 'inscrito',
        ]);

        $this->actingAs($mercadeo)
            ->patch('/reportes/inscripciones/' . $enrollment->id . '/pagos', [
                'movement_type' => 'refund',
                'concept' => 'inscription',
                'movement_date' => '2026-08-20',
                'movement_amount' => 60000,
            ])
            ->assertRedirect('/reportes/inscripciones');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'paid_inscription' => false,
            'paid_tuition' => false,
            'student_status' => 'pendiente',
        ]);
    }
}
