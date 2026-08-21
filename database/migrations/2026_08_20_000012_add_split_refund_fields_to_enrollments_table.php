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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->date('inscription_refund_date')->nullable()->after('inscription_amount_paid');
            $table->decimal('inscription_refund_amount', 10, 2)->nullable()->after('inscription_refund_date');
            $table->date('tuition_refund_date')->nullable()->after('tuition_amount_paid');
            $table->decimal('tuition_refund_amount', 10, 2)->nullable()->after('tuition_refund_date');
        });

        DB::table('enrollments')
            ->select(['id', 'tuition_amount_paid', 'refund_date', 'refund_amount'])
            ->orderBy('id')
            ->chunkById(100, function ($enrollments): void {
                foreach ($enrollments as $enrollment) {
                    if ($enrollment->refund_date === null && $enrollment->refund_amount === null) {
                        continue;
                    }

                    $targetPrefix = (float) ($enrollment->tuition_amount_paid ?? 0) > 0 ? 'tuition' : 'inscription';

                    DB::table('enrollments')
                        ->where('id', $enrollment->id)
                        ->update([
                            $targetPrefix . '_refund_date' => $enrollment->refund_date,
                            $targetPrefix . '_refund_amount' => $enrollment->refund_amount,
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'inscription_refund_date',
                'inscription_refund_amount',
                'tuition_refund_date',
                'tuition_refund_amount',
            ]);
        });
    }
};
