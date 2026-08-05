<?php

namespace Database\Seeders;

use App\Models\ProgramOption;
use Illuminate\Database\Seeder;

class ProgramOptionSeeder extends Seeder
{
    /**
     * Seed the application's program options.
     */
    public function run(): void
    {
        $programs = [
            'TL. Agente de Transito',
            'TL. Atención a la Primera Infancia',
            'TL. Atención al Adulto Mayor',
            'TL. Auxiliar Administrativo y Contable',
            'TL. Auxiliar de Recreación y Deporte',
            'TL. Auxiliar de Telemercadeo',
            'TL. Auxiliar de Veterinaria',
            'TL. Auxiliar en Enfermería',
            'TL. Auxiliar Judicial',
            'TL. Gestión del Talento Humano',
            'TL. Producción Agropecuaria',
            'TL. Producción Ganadera y Equina',
            'TL. Saneamiento Ambiental',
            'TL. Seguridad Ocupacional y Laboral',
            'TL. Auxiliar de Centro de Distribución',
            'TL. Producción Pecuaria',
        ];

        foreach ($programs as $index => $name) {
            ProgramOption::updateOrCreate(
                ['name' => $name],
                [
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
