<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'period',
    'campus_schedule',
    'campus',
    'jornada',
    'program',
    'first_name',
    'middle_name',
    'last_name',
    'second_last_name',
    'document_type',
    'document_number',
    'sex',
    'email',
    'phone',
    'mobile',
    'birth_date',
    'address',
    'residence_department_id',
    'residence_municipality_id',
    'residence_city',
    'neighborhood',
    'paid_inscription',
    'paid_tuition',
    'student_status',
    'inscription_payment_date',
    'inscription_amount_paid',
    'tuition_payment_date',
    'tuition_amount_paid',
    'refund_date',
    'refund_amount',
])]
class Enrollment extends Model
{
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'paid_inscription' => 'boolean',
            'paid_tuition' => 'boolean',
            'inscription_payment_date' => 'date',
            'tuition_payment_date' => 'date',
            'refund_date' => 'date',
            'inscription_amount_paid' => 'decimal:2',
            'tuition_amount_paid' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'residence_department_id' => 'integer',
            'residence_municipality_id' => 'integer',
        ];
    }

    public function residenceDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'residence_department_id');
    }

    public function residenceMunicipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class, 'residence_municipality_id');
    }

    public function syncStudentStatus(): void
    {
        if ($this->refund_amount && $this->refund_date) {
            $this->student_status = 'retirado';
            return;
        }

        if ($this->paid_inscription && $this->paid_tuition) {
            $this->student_status = 'matriculado';
            return;
        }

        if ($this->paid_inscription) {
            $this->student_status = 'inscrito';
            return;
        }

        $this->student_status = 'pendiente';
    }
}
