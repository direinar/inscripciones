<?php

namespace App\Http\Controllers;

use App\Models\CampusScheduleOption;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\JornadaOption;
use App\Models\Municipality;
use App\Models\PeriodOption;
use App\Models\ProgramOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnrollmentController extends Controller
{
    public function create()
    {
        return view('enrollments.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'period' => [
                'required',
                'string',
                'max:100',
                Rule::exists('period_options', 'name')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'campus' => [
                'required',
                'string',
                'max:150',
                Rule::exists('campus_schedule_options', 'name')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'jornada' => [
                'required',
                'string',
                'max:150',
                Rule::exists('jornada_options', 'name')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'program' => [
                'required',
                'string',
                'max:150',
                Rule::exists('program_options', 'name')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'second_last_name' => ['nullable', 'string', 'max:100'],
            'document_type' => ['required', 'string', 'max:100'],
            'document_number' => ['required', 'string', 'max:50'],
            'sex' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150'],
            'mobile' => ['required', 'string', 'max:30'],
            'residence_department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'residence_municipality_id' => [
                'required',
                'integer',
                Rule::exists('municipalities', 'id')->where(function ($query) use ($request) {
                    $query
                        ->where('department_id', (int) $request->input('residence_department_id'))
                        ->where('is_active', true);
                }),
            ],
        ]);

        $selectedMunicipality = Municipality::query()
            ->whereKey((int) $data['residence_municipality_id'])
            ->where('department_id', (int) $data['residence_department_id'])
            ->firstOrFail();

        // Keep compatibility with current non-null database columns removed from public form.
        $data['phone'] = '';
        $data['birth_date'] = now()->subYears(18)->toDateString();
        $data['address'] = 'No registrada';
        $data['residence_city'] = $selectedMunicipality->name;
        $data['neighborhood'] = null;
        $data['campus_schedule'] = $data['campus'].' - '.$data['jornada'];

        $data['paid_inscription'] = false;
        $data['paid_tuition'] = false;
        $data['student_status'] = 'pendiente';

        Enrollment::create($data);

        return redirect()->route('enrollments.create')->with('success', 'Tu inscripción fue enviada correctamente.');
    }

    public function municipalitiesByDepartment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ]);

        $municipalities = Municipality::query()
            ->where('department_id', (int) $validated['department_id'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'municipalities' => $municipalities,
        ]);
    }

    public function index(Request $request)
    {
        $query = $this->filteredQuery($request);
        $summaryRows = (clone $query)->get();
        $enrollments = (clone $query)
            ->with(['residenceDepartment:id,name', 'residenceMunicipality:id,name', 'personalDataUpdatedBy:id,name,email'])
            ->paginate(10)
            ->appends($request->query());

        return view('enrollments.index', [
            'enrollments' => $enrollments,
            'summary' => [
                'total' => $summaryRows->count(),
                'pending' => $summaryRows->where('student_status', 'pendiente')->count(),
                'inscribed' => $summaryRows->where('student_status', 'inscrito')->count(),
                'matriculated' => $summaryRows->where('student_status', 'matriculado')->count(),
                'with_refunds' => $summaryRows->filter(fn (Enrollment $enrollment) => $enrollment->totalRefundAmount() > 0)->count(),
                'inscription_paid' => (float) $summaryRows->sum(fn (Enrollment $enrollment) => $enrollment->inscriptionNetAmount()),
                'tuition_paid' => (float) $summaryRows->sum(fn (Enrollment $enrollment) => $enrollment->tuitionNetAmount()),
                'refunds' => (float) $summaryRows->sum(fn (Enrollment $enrollment) => $enrollment->totalRefundAmount()),
            ],
            ...$this->formOptions(),
        ]);
    }

    public function prospects(Request $request)
    {
        $enrollments = $this->filteredQuery($request)
            ->paginate(15)
            ->appends($request->query());

        return view('prospects.index', [
            'enrollments' => $enrollments,
            ...$this->formOptions(),
        ]);
    }

    public function marketing(Request $request)
    {
        $enrollments = $this->filteredQuery($request)
            ->paginate(15)
            ->appends($request->query());

        return view('marketing.index', compact('enrollments'));
    }

    public function financialReport(Request $request)
    {
        $query = $this->filteredQuery($request);
        $enrollments = (clone $query)
            ->with(['residenceDepartment:id,name', 'residenceMunicipality:id,name', 'personalDataUpdatedBy:id,name,email'])
            ->get();

        $rows = $this->buildFinancialMovementRows($enrollments, $request);

        if ($request->input('module') === 'pagos') {
            $rows = collect($rows)
                ->where('movement_type', 'payment')
                ->values()
                ->all();
        }

        usort($rows, function (array $left, array $right): int {
            $dateComparison = $right['movement_date']->timestamp <=> $left['movement_date']->timestamp;

            if ($dateComparison !== 0) {
                return $dateComparison;
            }

            return strcmp($left['student_name'], $right['student_name']);
        });

        $rowsCollection = collect($rows);
        $summary = [
            'movements' => $rowsCollection->count(),
            'payments' => $rowsCollection->where('movement_type', 'payment')->count(),
            'refunds' => $rowsCollection->where('movement_type', 'refund')->count(),
            'amount_paid' => (float) $rowsCollection->where('movement_type', 'payment')->sum('movement_amount'),
            'amount_refunded' => (float) $rowsCollection->where('movement_type', 'refund')->sum('movement_amount'),
        ];
        $summary['net_amount'] = $summary['amount_paid'] - $summary['amount_refunded'];

        return view('enrollments.financial', [
            'rows' => $rows,
            'summary' => $summary,
            'filtersSummary' => $this->financialFilterSummary($request),
            ...$this->formOptions(),
        ]);
    }

    public function exportFinancialExcel(Request $request): StreamedResponse
    {
        $rows = $this->buildFinancialMovementRows(
            (clone $this->filteredQuery($request))
                ->with(['residenceDepartment:id,name', 'residenceMunicipality:id,name', 'personalDataUpdatedBy:id,name,email'])
                ->get(),
            $request
        );

        usort($rows, function (array $left, array $right): int {
            return $right['movement_date']->timestamp <=> $left['movement_date']->timestamp;
        });

        $filename = 'reporte-financiero-'.Carbon::now()->format('Ymd-His').'.xls';

        return response()->streamDownload(function () use ($rows) {
            echo '<?xml version="1.0"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"';
            echo ' xmlns:o="urn:schemas-microsoft-com:office:office"';
            echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"';
            echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
            echo '<Worksheet ss:Name="Financiero"><Table>';

            $header = [
                'Fecha movimiento',
                'Tipo',
                'Concepto',
                'Estudiante',
                'Documento',
                'Programa',
                'Sede',
                'Jornada',
                'Valor',
                'Neto actual',
                'Estado',
            ];

            echo '<Row>';
            foreach ($header as $column) {
                echo '<Cell><Data ss:Type="String">'.e($column).'</Data></Cell>';
            }
            echo '</Row>';

            foreach ($rows as $row) {
                $excelRow = [
                    $row['movement_date']->format('Y-m-d H:i:s'),
                    $row['movement_type_label'],
                    $row['concept_label'],
                    $row['student_name'],
                    $row['document_number'],
                    $row['program'],
                    $row['campus'],
                    $row['jornada'],
                    number_format($row['movement_amount'], 2, ',', '.'),
                    number_format($row['net_amount'], 2, ',', '.'),
                    $row['status'],
                ];

                echo '<Row>';
                foreach ($excelRow as $column) {
                    echo '<Cell><Data ss:Type="String">'.e((string) ($column ?? '')).'</Data></Cell>';
                }
                echo '</Row>';
            }

            echo '</Table></Worksheet></Workbook>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function exportFinancialPdf(Request $request): Response
    {
        $rows = $this->buildFinancialMovementRows(
            (clone $this->filteredQuery($request))
                ->with(['residenceDepartment:id,name', 'residenceMunicipality:id,name', 'personalDataUpdatedBy:id,name,email'])
                ->get(),
            $request
        );

        usort($rows, function (array $left, array $right): int {
            return $right['movement_date']->timestamp <=> $left['movement_date']->timestamp;
        });

        $pdfContent = $this->buildFinancialTextPdf($rows, $request);
        $filename = 'reporte-financiero-'.Carbon::now()->format('Ymd-His').'.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function updatePayments(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate([
            'movement_type' => ['required', Rule::in(['payment', 'refund'])],
            'concept' => ['required', Rule::in(['inscription', 'tuition'])],
            'movement_date' => ['required', 'date'],
            'movement_amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $fields = $this->movementFields($data['concept']);
        $paymentAmount = (float) ($enrollment->{$fields['payment_amount']} ?? 0);
        $refundAmount = (float) ($enrollment->{$fields['refund_amount']} ?? 0);
        $requestedAmount = round((float) $data['movement_amount'], 2);

        if ($data['movement_type'] === 'payment') {
            if ($refundAmount > $requestedAmount) {
                return redirect()->route('enrollments.index', $request->query())
                    ->with('error', 'El valor pagado no puede ser menor que la devolución acumulada para '.$this->conceptLabel($data['concept']).'.');
            }

            $enrollment->{$fields['payment_date']} = $data['movement_date'];
            $enrollment->{$fields['payment_amount']} = $requestedAmount;
        }

        if ($data['movement_type'] === 'refund') {
            $availableBalance = max($paymentAmount - $refundAmount, 0);

            if ($availableBalance <= 0) {
                return redirect()->route('enrollments.index', $request->query())
                    ->with('error', 'No hay saldo disponible para devolver en '.$this->conceptLabel($data['concept']).'.');
            }

            if ($requestedAmount > $availableBalance) {
                return redirect()->route('enrollments.index', $request->query())
                    ->with('error', 'La devolución supera el saldo disponible de '.$this->conceptLabel($data['concept']).'.');
            }

            $enrollment->{$fields['refund_date']} = $data['movement_date'];
            $enrollment->{$fields['refund_amount']} = round($refundAmount + $requestedAmount, 2);
        }

        $enrollment->syncStudentStatus();
        $enrollment->save();

        $message = $data['movement_type'] === 'payment'
            ? 'Pago de '.$this->conceptLabel($data['concept']).' registrado correctamente.'
            : 'Devolución de '.$this->conceptLabel($data['concept']).' registrada correctamente.';

        return redirect()->route('enrollments.index', $request->query())->with('success', $message);
    }

    public function updatePersonalData(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'second_last_name' => ['nullable', 'string', 'max:100'],
            'document_type' => ['required', 'string', 'max:100'],
            'document_number' => ['required', 'string', 'max:50'],
            'sex' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150'],
            'mobile' => ['required', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $data['personal_data_updated_by'] = (int) ($request->user()?->id ?? 0) ?: null;
        $data['personal_data_updated_at'] = now();

        $enrollment->update($data);

        return redirect()->route('enrollments.index', $request->query())
            ->with('success', 'Datos personales actualizados correctamente.');
    }

    public function export(Request $request): StreamedResponse
    {
        return $this->exportExcel($request);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $query = $this->filteredQuery($request);
        $filename = 'reporte-inscripciones-'.Carbon::now()->format('Ymd-His').'.xls';

        return response()->streamDownload(function () use ($query) {
            echo '<?xml version="1.0"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"';
            echo ' xmlns:o="urn:schemas-microsoft-com:office:office"';
            echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"';
            echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
            echo '<Worksheet ss:Name="Inscripciones"><Table>';

            $header = [
                'Periodo',
                'Sede',
                'Jornada',
                'Programa',
                'Nombres',
                'Apellidos',
                'Tipo documento',
                'Numero documento',
                'Sexo',
                'Correo',
                'Telefono',
                'Celular',
                'Fecha nacimiento',
                'Direccion',
                'Departamento residencia',
                'Municipio',
                'Fecha pago inscripción',
                'Valor pago inscripción bruto',
                'Fecha devolución inscripción',
                'Valor devolución inscripción',
                'Saldo inscripción',
                'Fecha pago matrícula',
                'Valor pago matrícula bruto',
                'Fecha devolución matrícula',
                'Valor devolución matrícula',
                'Saldo matrícula',
                'Estado',
                'Fecha registro',
            ];

            echo '<Row>';
            foreach ($header as $column) {
                echo '<Cell><Data ss:Type="String">'.e($column).'</Data></Cell>';
            }
            echo '</Row>';

            foreach ($query->cursor() as $enrollment) {
                $row = [
                    $enrollment->period,
                    $enrollment->campus ?: $this->extractCampusFromLegacy((string) $enrollment->campus_schedule),
                    $enrollment->jornada ?: $this->extractJornadaFromLegacy((string) $enrollment->campus_schedule),
                    $enrollment->program,
                    trim($enrollment->first_name.' '.$enrollment->middle_name),
                    trim($enrollment->last_name.' '.$enrollment->second_last_name),
                    $enrollment->document_type,
                    $enrollment->document_number,
                    $enrollment->sex,
                    $enrollment->email,
                    $enrollment->phone,
                    $enrollment->mobile,
                    optional($enrollment->birth_date)->format('Y-m-d'),
                    $enrollment->address,
                    optional($enrollment->residenceDepartment)->name,
                    optional($enrollment->residenceMunicipality)->name ?: $enrollment->residence_city,
                    optional($enrollment->inscription_payment_date)->format('Y-m-d'),
                    $enrollment->inscription_amount_paid !== null ? number_format((float) $enrollment->inscription_amount_paid, 2, ',', '.') : '',
                    optional($enrollment->inscription_refund_date)->format('Y-m-d'),
                    $enrollment->inscription_refund_amount !== null ? number_format((float) $enrollment->inscription_refund_amount, 2, ',', '.') : '',
                    number_format($enrollment->inscriptionNetAmount(), 2, ',', '.'),
                    optional($enrollment->tuition_payment_date)->format('Y-m-d'),
                    $enrollment->tuition_amount_paid !== null ? number_format((float) $enrollment->tuition_amount_paid, 2, ',', '.') : '',
                    optional($enrollment->tuition_refund_date)->format('Y-m-d'),
                    $enrollment->tuition_refund_amount !== null ? number_format((float) $enrollment->tuition_refund_amount, 2, ',', '.') : '',
                    number_format($enrollment->tuitionNetAmount(), 2, ',', '.'),
                    $enrollment->student_status,
                    $enrollment->created_at->format('Y-m-d H:i:s'),
                ];

                echo '<Row>';
                foreach ($row as $column) {
                    echo '<Cell><Data ss:Type="String">'.e((string) ($column ?? '')).'</Data></Cell>';
                }
                echo '</Row>';
            }

            echo '</Table></Worksheet></Workbook>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $rows = $this->filteredQuery($request)->get();
        $pdfContent = $this->buildStyledReportPdf($rows, $request);
        $filename = 'reporte-inscripciones-'.Carbon::now()->format('Ymd-His').'.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    protected function filteredQuery(Request $request): Builder
    {
        $query = Enrollment::query()
            ->with(['residenceDepartment:id,name', 'residenceMunicipality:id,name', 'personalDataUpdatedBy:id,name,email'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('second_last_name', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        foreach (['period', 'program', 'campus', 'jornada'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->string($filter)->toString());
            }
        }

        if ($request->filled('status')) {
            $query->where('student_status', $request->string('status')->toString());
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->string('from_date')->toString().' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->string('to_date')->toString().' 23:59:59');
        }

        if ($request->filled('payment_date_from')) {
            $query->where(function ($builder) use ($request) {
                $builder
                    ->whereDate('inscription_payment_date', '>=', $request->string('payment_date_from')->toString())
                    ->orWhereDate('tuition_payment_date', '>=', $request->string('payment_date_from')->toString())
                    ->orWhereDate('inscription_refund_date', '>=', $request->string('payment_date_from')->toString())
                    ->orWhereDate('tuition_refund_date', '>=', $request->string('payment_date_from')->toString());
            });
        }

        if ($request->filled('payment_date_to')) {
            $query->where(function ($builder) use ($request) {
                $builder
                    ->whereDate('inscription_payment_date', '<=', $request->string('payment_date_to')->toString())
                    ->orWhereDate('tuition_payment_date', '<=', $request->string('payment_date_to')->toString())
                    ->orWhereDate('inscription_refund_date', '<=', $request->string('payment_date_to')->toString())
                    ->orWhereDate('tuition_refund_date', '<=', $request->string('payment_date_to')->toString());
            });
        }

        return $query;
    }

    protected function buildFinancialMovementRows(iterable $enrollments, Request $request): array
    {
        $fromDate = $request->filled('payment_date_from') ? Carbon::parse($request->string('payment_date_from')->toString())->startOfDay() : null;
        $toDate = $request->filled('payment_date_to') ? Carbon::parse($request->string('payment_date_to')->toString())->endOfDay() : null;
        $rows = [];

        foreach ($enrollments as $enrollment) {
            $studentName = trim(implode(' ', array_filter([
                $enrollment->first_name,
                $enrollment->middle_name,
                $enrollment->last_name,
                $enrollment->second_last_name,
            ])));

            $campus = $enrollment->campus ?: $this->extractCampusFromLegacy((string) $enrollment->campus_schedule);
            $jornada = $enrollment->jornada ?: $this->extractJornadaFromLegacy((string) $enrollment->campus_schedule);
            $baseData = [
                'student_name' => $studentName,
                'document_number' => $enrollment->document_number,
                'period' => $enrollment->period,
                'program' => $enrollment->program,
                'campus' => $campus,
                'jornada' => $jornada,
                'status' => ucfirst((string) $enrollment->student_status),
            ];

            $candidateMovements = [
                [
                    'movement_date' => $enrollment->inscription_payment_date,
                    'movement_type' => 'payment',
                    'movement_type_label' => 'Pago',
                    'concept' => 'inscription',
                    'concept_label' => 'Inscripción',
                    'movement_amount' => (float) ($enrollment->inscription_amount_paid ?? 0),
                    'net_amount' => $enrollment->inscriptionNetAmount(),
                ],
                [
                    'movement_date' => $enrollment->inscription_refund_date,
                    'movement_type' => 'refund',
                    'movement_type_label' => 'Devolución',
                    'concept' => 'inscription',
                    'concept_label' => 'Inscripción',
                    'movement_amount' => (float) ($enrollment->inscription_refund_amount ?? 0),
                    'net_amount' => $enrollment->inscriptionNetAmount(),
                ],
                [
                    'movement_date' => $enrollment->tuition_payment_date,
                    'movement_type' => 'payment',
                    'movement_type_label' => 'Pago',
                    'concept' => 'tuition',
                    'concept_label' => 'Matrícula',
                    'movement_amount' => (float) ($enrollment->tuition_amount_paid ?? 0),
                    'net_amount' => $enrollment->tuitionNetAmount(),
                ],
                [
                    'movement_date' => $enrollment->tuition_refund_date,
                    'movement_type' => 'refund',
                    'movement_type_label' => 'Devolución',
                    'concept' => 'tuition',
                    'concept_label' => 'Matrícula',
                    'movement_amount' => (float) ($enrollment->tuition_refund_amount ?? 0),
                    'net_amount' => $enrollment->tuitionNetAmount(),
                ],
            ];

            foreach ($candidateMovements as $movement) {
                if (! $movement['movement_date'] || $movement['movement_amount'] <= 0) {
                    continue;
                }

                if (! $this->movementDateWithinRange($movement['movement_date'], $fromDate, $toDate)) {
                    continue;
                }

                $rows[] = array_merge($baseData, [
                    'movement_date' => $movement['movement_date'],
                    'movement_type' => $movement['movement_type'],
                    'movement_type_label' => $movement['movement_type_label'],
                    'concept' => $movement['concept'],
                    'concept_label' => $movement['concept_label'],
                    'movement_amount' => $movement['movement_amount'],
                    'net_amount' => $movement['net_amount'],
                ]);
            }
        }

        return $rows;
    }

    protected function movementDateWithinRange(Carbon $date, ?Carbon $fromDate, ?Carbon $toDate): bool
    {
        if ($fromDate && $date->lt($fromDate)) {
            return false;
        }

        if ($toDate && $date->gt($toDate)) {
            return false;
        }

        return true;
    }

    protected function financialFilterSummary(Request $request): string
    {
        $labels = [
            'search' => 'Búsqueda',
            'period' => 'Periodo',
            'program' => 'Programa',
            'campus' => 'Sede',
            'jornada' => 'Jornada',
            'status' => 'Estado',
            'payment_date_from' => 'Desde',
            'payment_date_to' => 'Hasta',
        ];

        $parts = [];

        foreach ($labels as $field => $label) {
            if ($request->filled($field)) {
                $parts[] = $label.': '.$request->string($field)->toString();
            }
        }

        return $parts === [] ? 'Sin filtros' : implode(' | ', $parts);
    }

    protected function buildFinancialTextPdf(array $rows, Request $request): string
    {
        $lines = [
            'REPORTE FINANCIERO',
            'Generado: '.Carbon::now()->format('d/m/Y H:i'),
            'Filtros: '.$this->financialFilterSummary($request),
            'Total de movimientos: '.count($rows),
            str_repeat('-', 118),
            'Fecha | Tipo | Concepto | Estudiante | Documento | Programa | Sede | Valor | Neto actual | Estado',
            str_repeat('-', 118),
        ];

        foreach ($rows as $row) {
            $lines[] = implode(' | ', [
                $row['movement_date']->format('d/m/Y H:i'),
                $row['movement_type_label'],
                $row['concept_label'],
                $this->truncateText($row['student_name'], 22),
                $this->truncateText((string) $row['document_number'], 14),
                $this->truncateText((string) $row['program'], 22),
                $this->truncateText((string) $row['campus'], 16),
                number_format($row['movement_amount'], 0, ',', '.'),
                number_format($row['net_amount'], 0, ',', '.'),
                $row['status'],
            ]);
        }

        if ($rows === []) {
            $lines[] = 'No hay movimientos para los filtros seleccionados.';
        }

        return $this->buildSimpleTextPdf($lines, 34, 792, 612);
    }

    protected function buildSimpleTextPdf(array $lines, int $linesPerPage = 34, int $pageWidth = 792, int $pageHeight = 612): string
    {
        $chunks = array_chunk($lines, $linesPerPage);

        if ($chunks === []) {
            $chunks = [['No hay movimientos para los filtros seleccionados.']];
        }

        $pageCount = count($chunks);
        $pageObjects = [];
        $contentObjects = [];

        for ($i = 0; $i < $pageCount; $i++) {
            $pageObjects[] = 3 + $i;
            $contentObjects[] = 3 + $pageCount + $i;
        }

        $fontObject = 3 + (2 * $pageCount);

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids ['.implode(' ', array_map(fn ($pageObject) => $pageObject.' 0 R', $pageObjects)).'] /Count '.$pageCount.' >>',
        ];

        foreach ($pageObjects as $index => $pageObject) {
            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.$pageWidth.' '.$pageHeight.'] /Resources << /Font << /F1 '.$fontObject.' 0 R >> >> /Contents '.$contentObjects[$index].' 0 R >>';
        }

        foreach ($chunks as $chunk) {
            $escaped = array_map(function ($line) {
                $text = utf8_decode((string) $line);
                $text = str_replace('\\', '\\\\', $text);
                $text = str_replace('(', '\\(', $text);

                return str_replace(')', '\\)', $text);
            }, $chunk);

            $stream = "BT\n/F1 9 Tf\n12 TL\n36 560 Td\n";
            foreach ($escaped as $line) {
                $stream .= '('.$line.") Tj\nT*\n";
            }
            $stream .= 'ET';

            $objects[] = '<< /Length '.strlen($stream)." >>\nstream\n".$stream."\nendstream";
        }

        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        $pdf .= 'trailer << /Size '.(count($objects) + 1).' /Root 1 0 R >>'."\n";
        $pdf .= 'startxref'."\n".$xrefOffset."\n%%EOF";

        return $pdf;
    }

    protected function movementFields(string $concept): array
    {
        if ($concept === 'inscription') {
            return [
                'payment_date' => 'inscription_payment_date',
                'payment_amount' => 'inscription_amount_paid',
                'refund_date' => 'inscription_refund_date',
                'refund_amount' => 'inscription_refund_amount',
            ];
        }

        return [
            'payment_date' => 'tuition_payment_date',
            'payment_amount' => 'tuition_amount_paid',
            'refund_date' => 'tuition_refund_date',
            'refund_amount' => 'tuition_refund_amount',
        ];
    }

    protected function conceptLabel(string $concept): string
    {
        return $concept === 'inscription' ? 'inscripción' : 'matrícula';
    }

    protected function truncateText(string $value, int $max): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value)) ?? '';

        if (mb_strlen($normalized) <= $max) {
            return $normalized;
        }

        return mb_substr($normalized, 0, $max - 3).'...';
    }

    protected function buildStyledReportPdf($rows, Request $request): string
    {
        $records = is_object($rows) && method_exists($rows, 'values') ? $rows->values()->all() : array_values((array) $rows);
        $pages = $this->buildPdfReportPages($records, $request);

        if ($pages === []) {
            $pages = [$this->buildPdfEmptyPage($request)];
        }

        $pageCount = count($pages);
        $pageObjects = [];
        $contentObjects = [];

        for ($i = 0; $i < $pageCount; $i++) {
            $pageObjects[] = 3 + $i;
            $contentObjects[] = 5 + $pageCount + $i;
        }

        $regularFontObject = 3 + $pageCount;
        $boldFontObject = 4 + $pageCount;

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids ['.implode(' ', array_map(fn ($pageObject) => $pageObject.' 0 R', $pageObjects)).'] /Count '.$pageCount.' >>',
        ];

        foreach ($pageObjects as $index => $pageObject) {
            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 792 612] /Resources << /Font << /F1 '.$regularFontObject.' 0 R /F2 '.$boldFontObject.' 0 R >> >> /Contents '.$contentObjects[$index].' 0 R >>';
        }

        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

        foreach ($pages as $pageCommands) {
            $objects[] = '<< /Length '.strlen($pageCommands)." >>\nstream\n".$pageCommands."\nendstream";
        }

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        $pdf .= 'trailer << /Size '.(count($objects) + 1).' /Root 1 0 R >>'."\n";
        $pdf .= 'startxref'."\n".$xrefOffset."\n%%EOF";

        return $pdf;
    }

    protected function buildPdfReportPages(array $records, Request $request): array
    {
        if ($records === []) {
            return [];
        }

        $chunks = array_chunk($records, 2);
        $pageCount = count($chunks);
        $totalCount = count($records);
        $pages = [];

        foreach ($chunks as $pageIndex => $chunk) {
            $pages[] = $this->buildPdfReportPage($chunk, $request, $pageIndex + 1, $pageCount, $totalCount);
        }

        return $pages;
    }

    protected function buildPdfEmptyPage(Request $request): string
    {
        $pageWidth = 792;
        $pageHeight = 612;

        $commands = [];
        $commands[] = $this->pdfRect(0, 0, $pageWidth, $pageHeight, '1 1 1 rg', '1 1 1 RG', 0);
        $commands[] = $this->pdfRect(24, 580, 744, 1, '0.82 0.82 0.82 rg', '0.82 0.82 0.82 RG', 0);
        $commands[] = $this->pdfRect(24, 534, 744, 1, '0.90 0.90 0.90 rg', '0.90 0.90 0.90 RG', 0);
        $commands[] = $this->pdfTextAt(40, 556, 'REPORTE DE INSCRIPCIONES', 18, 'F2', '0.12 0.12 0.12 rg');
        $commands[] = $this->pdfTextAt(40, 540, 'No hay registros para los filtros seleccionados.', 11, 'F1', '0.26 0.26 0.26 rg');
        $commands[] = $this->pdfTextAt(40, 520, 'Generado: '.Carbon::now()->format('d/m/Y H:i'), 9, 'F1', '0.42 0.42 0.42 rg');
        $commands[] = $this->pdfTextAt(24, 24, 'Sistema de inscripciones', 8, 'F1', '0.55 0.55 0.55 rg');

        return implode("\n", $commands);
    }

    protected function buildPdfReportPage(array $records, Request $request, int $pageNumber, int $pageCount, int $totalCount): string
    {
        $pageWidth = 792;
        $pageHeight = 612;
        $margin = 24;
        $cardWidth = $pageWidth - ($margin * 2);
        $headerHeight = 68;
        $summaryHeight = 30;
        $cardHeight = 174;
        $cardGap = 10;
        $firstCardTop = 472;
        $secondCardTop = $firstCardTop - $cardHeight - $cardGap;

        $commands = [];
        $commands[] = $this->pdfRect(0, 0, $pageWidth, $pageHeight, '1 1 1 rg', '1 1 1 RG', 0);
        $commands[] = $this->pdfRect($margin, $pageHeight - $headerHeight - 12, $cardWidth, $headerHeight, '1 1 1 rg', '0.80 0.80 0.80 RG', 0.6);
        $commands[] = $this->pdfRect($margin, $pageHeight - 16, $cardWidth, 1, '0.75 0.75 0.75 rg', '0.75 0.75 0.75 RG', 0);
        $commands[] = $this->pdfTextAt(40, 552, 'REPORTE DE INSCRIPCIONES', 18, 'F2', '0.12 0.12 0.12 rg');
        $commands[] = $this->pdfTextAt(40, 535, 'Vista ejecutiva con datos académicos, financieros y de contacto', 10, 'F1', '0.28 0.28 0.28 rg');
        $commands[] = $this->pdfTextAt(40, 520, 'Generado: '.Carbon::now()->format('d/m/Y H:i'), 9, 'F1', '0.42 0.42 0.42 rg');
        $commands[] = $this->pdfTextAt(590, 552, 'Página '.$pageNumber.' de '.$pageCount, 10, 'F2', '0.22 0.22 0.22 rg');

        $summaryText = 'Filtros: '.$this->pdfFilterSummary($request).' | Total de registros: '.$totalCount;
        $commands[] = $this->pdfRect($margin, 500, $cardWidth, $summaryHeight, '1 1 1 rg', '0.88 0.88 0.88 RG', 0.4);
        $commands[] = $this->pdfTextAt(40, 516, $this->truncateText($summaryText, 125), 9, 'F1', '0.30 0.30 0.30 rg');

        $topPositions = [$firstCardTop, $secondCardTop];
        foreach ($records as $index => $enrollment) {
            $commands[] = $this->buildPdfRecordCard($enrollment, $margin, $topPositions[$index], $cardWidth, $cardHeight, $index + 1);
        }

        $commands[] = $this->pdfTextAt(24, 18, 'Sistema de inscripciones', 8, 'F1', '0.55 0.55 0.55 rg');

        return implode("\n", $commands);
    }

    protected function buildPdfRecordCard($enrollment, float $x, float $top, float $width, float $height, int $recordNumber): string
    {
        $bottom = $top - $height;
        $commands = [];
        $commands[] = $this->pdfRect($x, $bottom, $width, $height, '1 1 1 rg', '0.85 0.85 0.85 RG', 0.6);
        $commands[] = $this->pdfRect($x, $top - 4, $width, 1, '0.82 0.82 0.82 rg', '0.82 0.82 0.82 RG', 0);
        $commands[] = $this->pdfTextAt($x + 14, $top - 22, str_pad((string) $recordNumber, 2, '0', STR_PAD_LEFT), 9, 'F2', '0.25 0.25 0.25 rg');

        $statusStyle = $this->pdfStatusStyle((string) $enrollment->student_status);
        $statusLabel = strtoupper((string) $enrollment->student_status);
        $statusWidth = max(58, (mb_strlen($statusLabel) * 4.2) + 16);
        $commands[] = $this->pdfTextAt($x + $width - $statusWidth - 16, $top - 22, $statusLabel, 8, 'F2', '0.25 0.25 0.25 rg');

        $fullName = trim(implode(' ', array_filter([
            $enrollment->first_name,
            $enrollment->middle_name,
            $enrollment->last_name,
            $enrollment->second_last_name,
        ])));
        $commands[] = $this->pdfTextAt($x + 16, $top - 44, $this->truncateText($fullName, 52), 13, 'F2', '0.10 0.10 0.10 rg');

        $commands[] = $this->pdfTextAt($x + 16, $top - 60, 'Documento: '.trim(($enrollment->document_type ?: '-').' '.($enrollment->document_number ?: '-')), 8, 'F1', '0.30 0.30 0.30 rg');
        $commands[] = $this->pdfTextAt($x + 250, $top - 60, 'Sexo: '.($enrollment->sex ?: '-'), 8, 'F1', '0.30 0.30 0.30 rg');
        $commands[] = $this->pdfTextAt($x + 360, $top - 60, 'Estado: '.strtoupper((string) $enrollment->student_status), 8, 'F1', '0.30 0.30 0.30 rg');
        $commands[] = $this->pdfTextAt($x + 540, $top - 60, 'Registro: '.$enrollment->created_at->format('d/m/Y H:i'), 8, 'F1', '0.30 0.30 0.30 rg');

        $leftX = $x + 16;
        $rightX = $x + 386;
        $fieldTop = $top - 84;
        $commands[] = $this->pdfFieldBlock('Programa', $this->truncateText((string) $enrollment->program, 36), $leftX, $fieldTop);
        $commands[] = $this->pdfFieldBlock('Periodo', $this->truncateText((string) $enrollment->period, 24), $leftX, $fieldTop - 26);
        $commands[] = $this->pdfFieldBlock('Sede', $this->truncateText((string) ($enrollment->campus ?: $this->extractCampusFromLegacy((string) $enrollment->campus_schedule)), 24), $leftX, $fieldTop - 52);

        $contactValue = trim(($enrollment->email ?: '-').' / '.($enrollment->mobile ?: '-').(($enrollment->phone ?: '') !== '' ? ' / '.$enrollment->phone : ''));
        $residenceValue = trim((optional($enrollment->residenceDepartment)->name ?: '-').' - '.(optional($enrollment->residenceMunicipality)->name ?: $enrollment->residence_city ?: '-'));
        $addressValue = $this->truncateText((string) ($enrollment->address ?: '-'), 34);
        $commands[] = $this->pdfFieldBlock('Contacto', $this->truncateText($contactValue, 48), $rightX, $fieldTop);
        $commands[] = $this->pdfFieldBlock('Residencia', $this->truncateText($residenceValue, 40), $rightX, $fieldTop - 26);
        $commands[] = $this->pdfFieldBlock('Dirección', $addressValue, $rightX, $fieldTop - 52);

        $boxY = $bottom + 34;
        $boxW = ($width - 36) / 2;
        $commands[] = $this->pdfMiniFinancialBox($x + 12, $boxY, $boxW, 26, 'Inscripción', $this->pdfMoney($enrollment->inscription_amount_paid), $this->pdfMoney($enrollment->inscription_refund_amount), number_format($enrollment->inscriptionNetAmount(), 2, ',', '.'), '1 1 1 rg', '0.88 0.88 0.88 rg');
        $commands[] = $this->pdfMiniFinancialBox($x + 24 + $boxW, $boxY, $boxW, 26, 'Matrícula', $this->pdfMoney($enrollment->tuition_amount_paid), $this->pdfMoney($enrollment->tuition_refund_amount), number_format($enrollment->tuitionNetAmount(), 2, ',', '.'), '1 1 1 rg', '0.88 0.88 0.88 rg');

        $auditText = 'Última edición: '.(($enrollment->personal_data_updated_at ? $enrollment->personal_data_updated_at->format('d/m/Y H:i') : 'Sin registro').' · '.(optional($enrollment->personalDataUpdatedBy)->name ?: 'Sin registro'));
        $commands[] = $this->pdfTextAt($x + 16, $bottom + 10, $this->truncateText($auditText, 96), 8, 'F1', '0.52 0.52 0.52 rg');

        return implode("\n", $commands);
    }

    protected function pdfFieldBlock(string $label, string $value, float $x, float $y): string
    {
        $commands = [];
        $commands[] = $this->pdfTextAt($x, $y, $label, 8, 'F2', '0.34 0.34 0.34 rg');
        $commands[] = $this->pdfTextAt($x, $y - 11, $value, 10, 'F1', '0.12 0.12 0.12 rg');

        return implode("\n", $commands);
    }

    protected function pdfMiniFinancialBox(float $x, float $y, float $width, float $height, string $title, string $paid, string $refund, string $balance, string $fillColor, string $strokeColor): string
    {
        $commands = [];
        $commands[] = $this->pdfRect($x, $y, $width, $height, '1 1 1 rg', '0.92 0.92 0.92 RG', 0.35);
        $commands[] = $this->pdfTextAt($x + 10, $y + 18, $title, 9, 'F2', '0.22 0.22 0.22 rg');
        $commands[] = $this->pdfTextAt($x + 10, $y + 9, 'Pagado: '.$paid.'  |  Devolución: '.$refund.'  |  Saldo: '.$balance, 7, 'F1', '0.24 0.24 0.24 rg');

        return implode("\n", $commands);
    }

    protected function pdfStatusStyle(string $status): array
    {
        return match ($status) {
            'matriculado' => [
                'fill' => '1 1 1 rg',
                'stroke' => '0.85 0.85 0.85 RG',
                'text' => '0.22 0.22 0.22 rg',
            ],
            'inscrito' => [
                'fill' => '1 1 1 rg',
                'stroke' => '0.85 0.85 0.85 RG',
                'text' => '0.22 0.22 0.22 rg',
            ],
            'retirado' => [
                'fill' => '1 1 1 rg',
                'stroke' => '0.85 0.85 0.85 RG',
                'text' => '0.22 0.22 0.22 rg',
            ],
            default => [
                'fill' => '1 1 1 rg',
                'stroke' => '0.85 0.85 0.85 RG',
                'text' => '0.22 0.22 0.22 rg',
            ],
        };
    }

    protected function pdfRect(float $x, float $y, float $width, float $height, string $fillColor, string $strokeColor, float $lineWidth = 1): string
    {
        $commands = [];
        $commands[] = 'q';
        $commands[] = $fillColor;
        $commands[] = $strokeColor;
        $commands[] = $lineWidth.' w';
        $commands[] = $x.' '.$y.' '.$width.' '.$height.' re B';
        $commands[] = 'Q';

        return implode("\n", $commands);
    }

    protected function pdfTextAt(float $x, float $y, string $text, int $fontSize = 10, string $font = 'F1', string $color = '0 0 0 rg'): string
    {
        return 'BT'."\n".$color."\n/".$font.' '.$fontSize.' Tf'."\n1 0 0 1 ".$x.' '.$y.' Tm'."\n(".$this->pdfEscapeText($text).') Tj'."\nET";
    }

    protected function pdfEscapeText(string $text): string
    {
        $text = utf8_decode($text);
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);

        return str_replace(')', '\\)', $text);
    }

    protected function pdfFilterSummary(Request $request): string
    {
        $parts = [];

        foreach (['search', 'period', 'program', 'campus', 'jornada', 'status', 'from_date', 'to_date'] as $filter) {
            if ($request->filled($filter)) {
                $parts[] = ucfirst(str_replace('_', ' ', $filter)).': '.$request->string($filter)->toString();
            }
        }

        return $parts === [] ? 'Sin filtros' : implode(' | ', $parts);
    }

    protected function pdfMoney($value): string
    {
        return $value !== null ? number_format((float) $value, 2, ',', '.') : '0,00';
    }

    protected function formOptions(): array
    {
        return [
            'periods' => PeriodOption::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->all(),
            'campuses' => CampusScheduleOption::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->all(),
            'jornadas' => JornadaOption::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->all(),
            'programs' => ProgramOption::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->all(),
            'departments' => Department::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'documentTypes' => [
                'Cédula de Ciudadanía',
                'Tarjeta de Identidad',
                'Cédula de Extranjería',
                'Pasaporte',
            ],
            'sexOptions' => [
                'Femenino',
                'Masculino',
                'Otro',
            ],
        ];
    }

    protected function extractCampusFromLegacy(string $value): string
    {
        $parts = explode(' - ', $value, 2);

        return trim($parts[0] ?? $value);
    }

    protected function extractJornadaFromLegacy(string $value): string
    {
        $parts = explode(' - ', $value, 2);

        return trim($parts[1] ?? '');
    }
}
