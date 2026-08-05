<?php

namespace Database\Seeders;

use App\Models\CampusScheduleOption;
use Illuminate\Database\Seeder;

class JornadaOptionSeeder extends Seeder
{
    /**
     * Seed the application's jornada options.
     */
    public function run(): void
    {
        $jornadas = [
            'Diurno',
            'Nocturno',
            'Fin de semana',
            'Unica',
        ];

        foreach ($jornadas as $index => $name) {
            CampusScheduleOption::updateOrCreate(
                ['name' => $name],
                [
                    'sort_order' => $index + 100,
                    'is_active' => true,
                ]
            );
        }
    }
}
