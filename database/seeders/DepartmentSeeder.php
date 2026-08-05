<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Seed the application's departments.
     */
    public function run(): void
    {
        $datasetPath = database_path('data/colombia.min.json');

        if (!file_exists($datasetPath)) {
            throw new \RuntimeException('Dataset no encontrado: ' . $datasetPath);
        }

        $rows = json_decode((string) file_get_contents($datasetPath), true, flags: JSON_THROW_ON_ERROR);

        foreach ($rows as $index => $row) {
            Department::updateOrCreate(
                ['name' => trim((string) ($row['departamento'] ?? ''))],
                ['is_active' => true]
            );
        }
    }
}
