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
])]
class Enrollment extends Model
{
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'paid_inscription' => 'boolean',
            'paid_tuition' => 'boolean',
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
        $this->student_status = $this->paid_inscription && $this->paid_tuition ? 'activo' : 'pendiente';
    }
}
