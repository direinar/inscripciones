<?php

namespace Database\Seeders;

use App\Models\JornadaOption;
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
            'Unica y Virtual',
        ];

        JornadaOption::query()
            ->whereNotIn('name', $jornadas)
            ->update(['is_active' => false]);

        foreach ($jornadas as $index => $name) {
            JornadaOption::updateOrCreate(
                ['name' => $name],
                [
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
