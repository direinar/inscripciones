<?php

namespace Tests\Feature;

use App\Models\CampusScheduleOption;
use App\Models\PeriodOption;
use App\Models\ProgramOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelectorOptionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_program_option(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post('/admin/program-options', [
                'name' => 'TL Diseño Gráfico',
                'sort_order' => 10,
                'is_active' => 1,
            ])
            ->assertRedirect('/admin/program-options');

        $this->assertDatabaseHas('program_options', [
            'name' => 'TL Diseño Gráfico',
            'is_active' => true,
        ]);
    }

    public function test_non_admin_cannot_access_selector_option_crud(): void
    {
        $mercadeo = User::factory()->create(['role' => 'mercadeo']);

        $this->actingAs($mercadeo)
            ->get('/admin/period-options')
            ->assertForbidden();
    }

    public function test_form_submission_rejects_values_not_in_active_catalogs(): void
    {
        PeriodOption::query()->update(['is_active' => false]);
        CampusScheduleOption::query()->update(['is_active' => false]);
        ProgramOption::query()->update(['is_active' => false]);

        $response = $this->post('/inscripciones', [
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
        ]);

        $response->assertSessionHasErrors(['period', 'campus_schedule', 'program']);
    }
}
