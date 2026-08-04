<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
            'period' => ['required', 'string', 'max:100'],
            'campus_schedule' => ['required', 'string', 'max:150'],
            'program' => ['required', 'string', 'max:150'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'second_last_name' => ['nullable', 'string', 'max:100'],
            'document_type' => ['required', 'string', 'max:100'],
            'document_number' => ['required', 'string', 'max:50'],
            'sex' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'mobile' => ['required', 'string', 'max:30'],
            'birth_date' => ['required', 'date', 'before:today'],
            'address' => ['required', 'string', 'max:255'],
            'residence_city' => ['required', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
        ]);

        $data['paid_inscription'] = false;
        $data['paid_tuition'] = false;
        $data['student_status'] = 'pendiente';

        Enrollment::create($data);

        return redirect()->route('enrollments.create')->with('success', 'Tu inscripción fue enviada correctamente.');
    }

    public function index(Request $request)
    {
        $enrollments = $this->filteredQuery($request)->get();

        return view('enrollments.index', [
            'enrollments' => $enrollments,
            'summary' => [
                'total' => DB::table('enrollments')->count(),
                'today' => DB::table('enrollments')->whereDate('created_at', today()->toDateString())->count(),
                'this_month' => DB::table('enrollments')
                    ->whereMonth('created_at', today()->month)
                    ->whereYear('created_at', today()->year)
                    ->count(),
                'active' => DB::table('enrollments')->where('student_status', 'activo')->count(),
            ],
            ...$this->formOptions(),
        ]);
    }

    public function updatePayments(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate([
            'paid_inscription' => ['nullable', 'boolean'],
            'paid_tuition' => ['nullable', 'boolean'],
        ]);

        $updated = false;

        if (array_key_exists('paid_inscription', $data) && (bool) $data['paid_inscription'] && !$enrollment->paid_inscription) {
            $enrollment->paid_inscription = true;
            $updated = true;
        }

        if (array_key_exists('paid_tuition', $data) && (bool) $data['paid_tuition'] && !$enrollment->paid_tuition) {
            $enrollment->paid_tuition = true;
            $updated = true;
        }

        if (!$updated) {
            return redirect()->route('enrollments.index', $request->query())->with('success', 'Este pago ya estaba registrado.');
        }

        $enrollment->syncStudentStatus();
        $enrollment->save();

        return redirect()->route('enrollments.index', $request->query())->with('success', 'Pagos actualizados correctamente.');
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
                'Sede - jornada',
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
                'Municipio',
                'Barrio',
                'Pago inscripcion',
                'Pago matricula',
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
                    $enrollment->campus_schedule,
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
                    $enrollment->residence_city,
                    $enrollment->neighborhood,
                    $enrollment->paid_inscription ? 'Pagado' : 'Pendiente',
                    $enrollment->paid_tuition ? 'Pagado' : 'Pendiente',
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
            'Fecha | Nombre | Documento | Programa | Pago Inscripcion | Pago Matricula | Estado',
            str_repeat('-', 120),
        ];

        foreach ($rows as $enrollment) {
            $name = trim($enrollment->first_name . ' ' . $enrollment->middle_name . ' ' . $enrollment->last_name . ' ' . $enrollment->second_last_name);
            $line = implode(' | ', [
                $enrollment->created_at->format('Y-m-d'),
                $this->truncateText($name, 28),
                $enrollment->document_number,
                $this->truncateText($enrollment->program, 24),
                $enrollment->paid_inscription ? 'Pagado' : 'Pendiente',
                $enrollment->paid_tuition ? 'Pagado' : 'Pendiente',
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
        $query = Enrollment::query()->latest();

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

        foreach (['period', 'program', 'campus_schedule'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->string($filter)->toString());
            }
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->string('from_date')->toString() . ' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->string('to_date')->toString() . ' 23:59:59');
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
            'periods' => [
                '2026-1',
                '2026-2',
                '2027-1',
            ],
            'campusSchedules' => [
                'Principal - Diurna',
                'Principal - Fin de Semana',
                'Principal - Única',
            ],
            'programs' => [
                'TL Agente de Tránsito',
                'TL Auxiliar Administrativo',
                'TL Auxiliar en Seguridad y Salud en el Trabajo',
                'TL Sistemas Informáticos',
            ],
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
            'cities' => [
                'Barranquilla',
                'Soledad',
                'Malambo',
                'Galapa',
                'Puerto Colombia',
            ],
            'neighborhoods' => [
                'Centro',
                'Las Moras',
                'Villa Estadio',
                'Ciudadela 20 de Julio',
                'Los Robles',
                'El Parque',
            ],
        ];
    }
}
