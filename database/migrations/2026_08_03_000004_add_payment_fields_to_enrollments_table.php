<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->boolean('paid_inscription')->default(false)->after('neighborhood');
            $table->boolean('paid_tuition')->default(false)->after('paid_inscription');
            $table->string('student_status')->default('pendiente')->after('paid_tuition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['paid_inscription', 'paid_tuition', 'student_status']);
        });
    }
};
