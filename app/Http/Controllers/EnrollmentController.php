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
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
            'birth_date' => ['required', 'date', 'before:today'],
            'address' => ['required', 'string', 'max:255'],
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
        $data['residence_city'] = $selectedMunicipality->name;
        $data['neighborhood'] = null;
        $data['campus_schedule'] = $data['campus'] . ' - ' . $data['jornada'];

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
        $enrollments = (clone $query)
            ->with(['residenceDepartment:id,name', 'residenceMunicipality:id,name'])
            ->paginate(10)
            ->appends($request->query());

        return view('enrollments.index', [
            'enrollments' => $enrollments,
            'summary' => [
                'total' => (clone $query)->count(),
                'today' => (clone $query)->whereDate('created_at', today()->toDateString())->count(),
                'this_month' => (clone $query)
                    ->whereMonth('created_at', today()->month)
                    ->whereYear('created_at', today()->year)
                    ->count(),
                'pending' => (clone $query)->where('student_status', 'pendiente')->count(),
                'inscribed' => (clone $query)->where('student_status', 'inscrito')->count(),
                'matriculated' => (clone $query)->where('student_status', 'matriculado')->count(),
                'retired' => (clone $query)->where('student_status', 'retirado')->count(),
                'inscription_paid' => (float) ((clone $query)->where('paid_inscription', true)->sum('inscription_amount_paid') ?? 0),
                'tuition_paid' => (float) ((clone $query)->where('paid_tuition', true)->sum('tuition_amount_paid') ?? 0),
                'refunds' => (float) ((clone $query)->whereNotNull('refund_amount')->sum('refund_amount') ?? 0),
            ],
            ...$this->formOptions(),
        ]);
    }

    public function updatePayments(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate([
            'paid_inscription' => ['nullable', 'boolean'],
            'paid_tuition' => ['nullable', 'boolean'],
            'inscription_payment_date' => ['nullable', 'date'],
            'inscription_amount_paid' => ['nullable', 'numeric', 'min:0'],
            'tuition_payment_date' => ['nullable', 'date'],
            'tuition_amount_paid' => ['nullable', 'numeric', 'min:0'],
            'refund_date' => ['nullable', 'date'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $updated = false;

        if (array_key_exists('paid_inscription', $data) || array_key_exists('inscription_payment_date', $data) || array_key_exists('inscription_amount_paid', $data)) {
            $date = $request->input('inscription_payment_date');
            $amount = $request->input('inscription_amount_paid');

            if ($request->boolean('paid_inscription') && (blank($date) || blank($amount))) {
                return redirect()->route('enrollments.index', $request->query())->with('error', 'Para registrar la inscripción debes completar la fecha y el valor pagado.');
            }

            if ($request->boolean('paid_inscription')) {
                $enrollment->paid_inscription = true;
                $updated = true;
            }
        }

        if (array_key_exists('paid_tuition', $data) || array_key_exists('tuition_payment_date', $data) || array_key_exists('tuition_amount_paid', $data)) {
            $date = $request->input('tuition_payment_date');
            $amount = $request->input('tuition_amount_paid');

            if ($request->boolean('paid_tuition') && (blank($date) || blank($amount))) {
                return redirect()->route('enrollments.index', $request->query())->with('error', 'Para registrar la matrícula debes completar la fecha y el valor pagado.');
            }

            if ($request->boolean('paid_tuition')) {
                $enrollment->paid_tuition = true;
                $updated = true;
            }
        }

        if (array_key_exists('inscription_payment_date', $data)) {
            $enrollment->inscription_payment_date = $data['inscription_payment_date'];
        }

        if (array_key_exists('inscription_amount_paid', $data)) {
            $enrollment->inscription_amount_paid = $data['inscription_amount_paid'];
        }

        if (array_key_exists('tuition_payment_date', $data)) {
            $enrollment->tuition_payment_date = $data['tuition_payment_date'];
        }

        if (array_key_exists('tuition_amount_paid', $data)) {
            $enrollment->tuition_amount_paid = $data['tuition_amount_paid'];
        }

        if (array_key_exists('refund_date', $data) || array_key_exists('refund_amount', $data)) {
            $date = $request->input('refund_date');
            $amount = $request->input('refund_amount');

            if ((filled($date) || filled($amount)) && (blank($date) || blank($amount))) {
                return redirect()->route('enrollments.index', $request->query())->with('error', 'Para registrar una devolución debes completar la fecha y el valor devuelto.');
            }

            if (array_key_exists('refund_date', $data)) {
                $enrollment->refund_date = $data['refund_date'];
            }

            if (array_key_exists('refund_amount', $data)) {
                $enrollment->refund_amount = $data['refund_amount'];
            }
        }

        if (!$updated && !array_key_exists('inscription_payment_date', $data) && !array_key_exists('inscription_amount_paid', $data) && !array_key_exists('tuition_payment_date', $data) && !array_key_exists('tuition_amount_paid', $data) && !array_key_exists('refund_date', $data) && !array_key_exists('refund_amount', $data)) {
            return redirect()->route('enrollments.index', $request->query())->with('success', 'No se realizaron cambios.');
        }

        $enrollment->syncStudentStatus();
        $enrollment->save();

        return redirect()->route('enrollments.index', $request->query())->with('success', 'Información actualizada correctamente.');
    }

    public function export(Request $request): StreamedResponse
    {
        return $this->exportExcel($request);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $query = $this->filteredQuery($request);
        $filename = 'reporte-inscripciones-' . Carbon::now()->format('Ymd-His') . '.xls';

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
                'Pago inscripcion',
                'Pago matricula',
                'Fecha pago inscripción',
                'Valor pago inscripción',
                'Fecha pago matrícula',
                'Valor pago matrícula',
                'Fecha devolución',
                'Valor devolución',
                'Estado',
                'Fecha registro',
            ];

            echo '<Row>';
            foreach ($header as $column) {
                echo '<Cell><Data ss:Type="String">' . e($column) . '</Data></Cell>';
            }
            echo '</Row>';

            foreach ($query->cursor() as $enrollment) {
                $row = [
                    $enrollment->period,
                    $enrollment->campus ?: $this->extractCampusFromLegacy((string) $enrollment->campus_schedule),
                    $enrollment->jornada ?: $this->extractJornadaFromLegacy((string) $enrollment->campus_schedule),
                    $enrollment->program,
                    trim($enrollment->first_name . ' ' . $enrollment->middle_name),
                    trim($enrollment->last_name . ' ' . $enrollment->second_last_name),
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
                    $enrollment->paid_inscription ? 'Pagado' : 'Pendiente',
                    $enrollment->paid_tuition ? 'Pagado' : 'Pendiente',
                    optional($enrollment->inscription_payment_date)->format('Y-m-d'),
                    $enrollment->inscription_amount_paid !== null ? number_format((float) $enrollment->inscription_amount_paid, 2, ',', '.') : '',
                    optional($enrollment->tuition_payment_date)->format('Y-m-d'),
                    $enrollment->tuition_amount_paid !== null ? number_format((float) $enrollment->tuition_amount_paid, 2, ',', '.') : '',
                    optional($enrollment->refund_date)->format('Y-m-d'),
                    $enrollment->refund_amount !== null ? number_format((float) $enrollment->refund_amount, 2, ',', '.') : '',
                    $enrollment->student_status,
                    $enrollment->created_at->format('Y-m-d H:i:s'),
                ];

                echo '<Row>';
                foreach ($row as $column) {
                    echo '<Cell><Data ss:Type="String">' . e((string) ($column ?? '')) . '</Data></Cell>';
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
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');

        $lines = [
            'REPORTE DE INSCRIPCIONES',
            'Fecha de generacion: ' . $timestamp,
            str_repeat('-', 120),
            'Fecha | Nombre | Documento | Programa | Sede | Jornada | Depto | Municipio | Pago Inscripcion | Pago Matricula | Fecha Pago Inscripción | Valor Pago Inscripción | Fecha Pago Matrícula | Valor Pago Matrícula | Fecha Devolución | Valor Devolución | Estado',
            str_repeat('-', 120),
        ];

        foreach ($rows as $enrollment) {
            $name = trim($enrollment->first_name . ' ' . $enrollment->middle_name . ' ' . $enrollment->last_name . ' ' . $enrollment->second_last_name);
            $campus = $enrollment->campus ?: $this->extractCampusFromLegacy((string) $enrollment->campus_schedule);
            $jornada = $enrollment->jornada ?: $this->extractJornadaFromLegacy((string) $enrollment->campus_schedule);
            $department = optional($enrollment->residenceDepartment)->name ?? '';
            $municipality = optional($enrollment->residenceMunicipality)->name ?: (string) $enrollment->residence_city;
            $line = implode(' | ', [
                $enrollment->created_at->format('Y-m-d'),
                $this->truncateText($name, 28),
                $enrollment->document_number,
                $this->truncateText($enrollment->program, 24),
                $this->truncateText($campus, 18),
                $this->truncateText($jornada, 18),
                $this->truncateText($department, 16),
                $this->truncateText($municipality, 18),
                $enrollment->paid_inscription ? 'Pagado' : 'Pendiente',
                $enrollment->paid_tuition ? 'Pagado' : 'Pendiente',
                optional($enrollment->inscription_payment_date)->format('Y-m-d'),
                $enrollment->inscription_amount_paid !== null ? number_format((float) $enrollment->inscription_amount_paid, 2, ',', '.') : '',
                optional($enrollment->tuition_payment_date)->format('Y-m-d'),
                $enrollment->tuition_amount_paid !== null ? number_format((float) $enrollment->tuition_amount_paid, 2, ',', '.') : '',
                optional($enrollment->refund_date)->format('Y-m-d'),
                $enrollment->refund_amount !== null ? number_format((float) $enrollment->refund_amount, 2, ',', '.') : '',
                strtoupper((string) $enrollment->student_status),
            ]);

            $lines[] = $line;
        }

        if ($rows->isEmpty()) {
            $lines[] = 'No hay registros para los filtros seleccionados.';
        }

        $pdfContent = $this->buildSimplePdf($lines);
        $filename = 'reporte-inscripciones-' . Carbon::now()->format('Ymd-His') . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    protected function filteredQuery(Request $request): Builder
    {
        $query = Enrollment::query()
            ->with(['residenceDepartment:id,name', 'residenceMunicipality:id,name'])
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
            $query->where('created_at', '>=', $request->string('from_date')->toString() . ' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->string('to_date')->toString() . ' 23:59:59');
        }

        if ($request->filled('payment_date_from')) {
            $query->where(function ($builder) use ($request) {
                $builder
                    ->whereDate('inscription_payment_date', '>=', $request->string('payment_date_from')->toString())
                    ->orWhereDate('tuition_payment_date', '>=', $request->string('payment_date_from')->toString());
            });
        }

        if ($request->filled('payment_date_to')) {
            $query->where(function ($builder) use ($request) {
                $builder
                    ->whereDate('inscription_payment_date', '<=', $request->string('payment_date_to')->toString())
                    ->orWhereDate('tuition_payment_date', '<=', $request->string('payment_date_to')->toString());
            });
        }

        return $query;
    }

    protected function truncateText(string $value, int $max): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value)) ?? '';

        if (mb_strlen($normalized) <= $max) {
            return $normalized;
        }

        return mb_substr($normalized, 0, $max - 3) . '...';
    }

    protected function buildSimplePdf(array $lines): string
    {
        $linesPerPage = 48;
        $chunks = array_chunk($lines, $linesPerPage);

        $objects = [];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';

        $pageCount = count($chunks);
        $firstPageObject = 3;
        $fontObject = $firstPageObject + $pageCount;
        $firstContentObject = $fontObject + 1;

        $kids = [];
        for ($i = 0; $i < $pageCount; $i++) {
            $pageObject = $firstPageObject + $i;
            $contentObject = $firstContentObject + $i;
            $kids[] = $pageObject . ' 0 R';

            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 ' . $fontObject . ' 0 R >> >> /Contents ' . $contentObject . ' 0 R >>';
        }

        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        foreach ($chunks as $chunk) {
            $escaped = array_map(function ($line) {
                $text = utf8_decode((string) $line);
                $text = str_replace('\\', '\\\\', $text);
                $text = str_replace('(', '\\(', $text);
                return str_replace(')', '\\)', $text);
            }, $chunk);

            $stream = "BT\n/F1 9 Tf\n12 TL\n36 760 Td\n";
            foreach ($escaped as $line) {
                $stream .= '(' . $line . ") Tj\nT*\n";
            }
            $stream .= 'ET';

            $objects[] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
        }

        $objects[1] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $pageCount . ' >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= 'trailer << /Size ' . (count($objects) + 1) . ' /Root 1 0 R >>' . "\n";
        $pdf .= 'startxref' . "\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
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
