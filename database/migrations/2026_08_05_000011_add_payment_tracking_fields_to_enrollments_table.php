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
            $table->date('inscription_payment_date')->nullable()->after('paid_inscription');
            $table->decimal('inscription_amount_paid', 10, 2)->nullable()->after('inscription_payment_date');
            $table->date('tuition_payment_date')->nullable()->after('paid_tuition');
            $table->decimal('tuition_amount_paid', 10, 2)->nullable()->after('tuition_payment_date');
            $table->date('refund_date')->nullable()->after('student_status');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('refund_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['inscription_payment_date', 'inscription_amount_paid', 'tuition_payment_date', 'tuition_amount_paid', 'refund_date', 'refund_amount']);
        });
    }
};
