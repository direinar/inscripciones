<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Municipality;
use Illuminate\Database\Seeder;

class MunicipalitySeeder extends Seeder
{
    /**
     * Seed the application's municipalities.
     */
    public function run(): void
    {
        $datasetPath = database_path('data/colombia.min.json');

        if (!file_exists($datasetPath)) {
            throw new \RuntimeException('Dataset no encontrado: ' . $datasetPath);
        }

        $rows = json_decode((string) file_get_contents($datasetPath), true, flags: JSON_THROW_ON_ERROR);

        foreach ($rows as $row) {
            $departmentName = trim((string) ($row['departamento'] ?? ''));
            if ($departmentName === '') {
                continue;
            }

            $department = Department::query()->firstOrCreate(
                ['name' => $departmentName],
                ['is_active' => true]
            );

            $municipalities = $row['ciudades'] ?? [];
            foreach ($municipalities as $municipalityName) {
                $cleanName = trim((string) $municipalityName);
                if ($cleanName === '') {
                    continue;
                }

                Municipality::updateOrCreate(
                    [
                        'department_id' => $department->id,
                        'name' => $cleanName,
                    ],
                    ['is_active' => true]
                );
            }
        }
    }
}
