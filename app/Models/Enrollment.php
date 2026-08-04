<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'period',
    'campus_schedule',
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
        ];
    }

    public function syncStudentStatus(): void
    {
        $this->student_status = $this->paid_inscription && $this->paid_tuition ? 'activo' : 'pendiente';
    }
}
