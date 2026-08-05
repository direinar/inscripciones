<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('enrollments')
            ->select(['id', 'campus_schedule', 'campus', 'jornada'])
            ->whereNotNull('campus_schedule')
            ->where(function ($query) {
                $query->whereNull('campus')->orWhereNull('jornada');
            })
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    $legacy = trim((string) $row->campus_schedule);
                    if ($legacy === '') {
                        continue;
                    }

                    [$campus, $jornada] = $this->splitLegacyCampusSchedule($legacy);

                    DB::table('enrollments')
                        ->where('id', $row->id)
                        ->update([
                            'campus' => $campus,
                            'jornada' => $jornada,
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('enrollments')
            ->whereNotNull('campus_schedule')
            ->update([
                'campus' => null,
                'jornada' => null,
            ]);
    }

    /**
     * @return array{0:string,1:?string}
     */
    private function splitLegacyCampusSchedule(string $legacy): array
    {
        $parts = explode(' - ', $legacy, 2);
        $campus = trim($parts[0] ?? $legacy);
        $jornada = isset($parts[1]) ? trim($parts[1]) : null;

        return [$campus, $jornada !== '' ? $jornada : null];
    }
};
