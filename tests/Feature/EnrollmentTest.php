<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private function enrollmentPayload(array $overrides = []): array
    {
        return array_merge([
            'period' => '2026-1',
            'campus_schedule' => 'Principal - Fin de Semana',
            'program' => 'TL Agente de Tránsito',
            'first_name' => 'Laura',
            'middle_name' => 'Sofia',
            'last_name' => 'Reina',
            'second_last_name' => 'Martinez',
            'document_type' => 'Cédula de Ciudadanía',
            'document_number' => '123456789',
            'sex' => 'Femenino',
            'email' => 'laura@example.com',
            'phone' => '6051234567',
            'mobile' => '3001234567',
            'birth_date' => '2000-05-10',
            'address' => 'Calle 10 # 20-30',
            'residence_city' => 'Soledad',
            'neighborhood' => 'Los Robles',
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

    public function test_admin_can_export_enrollment_reports_as_csv(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        Enrollment::create($this->enrollmentPayload());

        $response = $this->actingAs($admin)
            ->get('/reportes/inscripciones/exportar');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_marketing_user_can_update_payment_status_and_activate_student(): void
    {
        /** @var User $mercadeo */
        $mercadeo = User::factory()->create(['role' => 'mercadeo']);
        $enrollment = Enrollment::create($this->enrollmentPayload());

        $this->actingAs($mercadeo)
            ->patch('/reportes/inscripciones/' . $enrollment->id . '/pagos', [
                'paid_inscription' => 1,
            ])
            ->assertRedirect('/reportes/inscripciones');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'paid_inscription' => true,
            'paid_tuition' => false,
            'student_status' => 'pendiente',
        ]);

        $this->actingAs($mercadeo)
            ->patch('/reportes/inscripciones/' . $enrollment->id . '/pagos', [
                'paid_tuition' => 1,
            ])
            ->assertRedirect('/reportes/inscripciones');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'paid_inscription' => true,
            'paid_tuition' => true,
            'student_status' => 'activo',
        ]);
    }
}
