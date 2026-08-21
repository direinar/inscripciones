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
    'personal_data_updated_by',
    'personal_data_updated_at',
    'paid_inscription',
    'paid_tuition',
    'student_status',
    'inscription_payment_date',
    'inscription_amount_paid',
    'inscription_refund_date',
    'inscription_refund_amount',
    'tuition_payment_date',
    'tuition_amount_paid',
    'tuition_refund_date',
    'tuition_refund_amount',
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
            'inscription_refund_date' => 'date',
            'tuition_payment_date' => 'date',
            'tuition_refund_date' => 'date',
            'refund_date' => 'date',
            'inscription_amount_paid' => 'decimal:2',
            'inscription_refund_amount' => 'decimal:2',
            'tuition_amount_paid' => 'decimal:2',
            'tuition_refund_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'residence_department_id' => 'integer',
            'residence_municipality_id' => 'integer',
            'personal_data_updated_by' => 'integer',
            'personal_data_updated_at' => 'datetime',
        ];
    }

    public function inscriptionNetAmount(): float
    {
        return max((float) ($this->inscription_amount_paid ?? 0) - (float) ($this->inscription_refund_amount ?? 0), 0);
    }

    public function tuitionNetAmount(): float
    {
        return max((float) ($this->tuition_amount_paid ?? 0) - (float) ($this->tuition_refund_amount ?? 0), 0);
    }

    public function totalRefundAmount(): float
    {
        return (float) ($this->inscription_refund_amount ?? 0) + (float) ($this->tuition_refund_amount ?? 0);
    }

    public function residenceDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'residence_department_id');
    }

    public function residenceMunicipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class, 'residence_municipality_id');
    }

    public function personalDataUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'personal_data_updated_by');
    }

    public function syncStudentStatus(): void
    {
        $this->paid_inscription = $this->inscriptionNetAmount() > 0;
        $this->paid_tuition = $this->tuitionNetAmount() > 0;

        if ($this->paid_tuition) {
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
