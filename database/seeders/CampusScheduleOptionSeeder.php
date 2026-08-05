<?php

namespace Database\Seeders;

use App\Models\CampusScheduleOption;
use Illuminate\Database\Seeder;

class CampusScheduleOptionSeeder extends Seeder
{
    /**
     * Seed the application's campus/sede options.
     */
    public function run(): void
    {
        $campusSchedules = [
            'Vegachí',
            'Amalfi',
            'Anorí',
            'Anza',
            'Caceres',
            'CBolivar',
            'CPríncipe',
            'Maceo',
            'Remedios',
            'Sabanalarga',
            'SFrancisco',
            'SRoque',
            'Yali',
            'Yolombo',
            'Virtual',
        ];

        foreach ($campusSchedules as $index => $name) {
            CampusScheduleOption::updateOrCreate(
                ['name' => $name],
                [
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
