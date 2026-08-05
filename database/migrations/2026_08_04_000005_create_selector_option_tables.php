<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('period_options', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('campus_schedule_options', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('program_options', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->seedInitialOptions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_options');
        Schema::dropIfExists('campus_schedule_options');
        Schema::dropIfExists('period_options');
    }

    private function seedInitialOptions(): void
    {
        $now = now();

        foreach (['2026-1', '2026-2', '2027-1'] as $index => $value) {
            DB::table('period_options')->insert([
                'name' => $value,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            'Principal - Diurna',
            'Principal - Fin de Semana',
            'Principal - Única',
        ] as $index => $value) {
            DB::table('campus_schedule_options')->insert([
                'name' => $value,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            'TL Agente de Tránsito',
            'TL Auxiliar Administrativo',
            'TL Auxiliar en Seguridad y Salud en el Trabajo',
            'TL Sistemas Informáticos',
        ] as $index => $value) {
            DB::table('program_options')->insert([
                'name' => $value,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
