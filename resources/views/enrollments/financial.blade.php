@extends('layouts.app')

@section('title', 'Reporte financiero')
@section('badge', 'Financiero')

@section('content')
    <style>
        .financial-shell {
            display: grid;
            gap: 1.2rem;
        }

        .financial-summary {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .financial-stat {
            padding: 1rem 1.05rem;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #fff;
        }

        .financial-stat span {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
        }

        .financial-stat strong {
            display: block;
            margin-top: 0.25rem;
            font-size: 1.25rem;
            color: #0f172a;
        }

        .financial-table-wrap {
            overflow: auto;
            border: 1px solid #dbe4f0;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.05);
        }

        .financial-table {
            width: max-content;
            min-width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .financial-table th,
        .financial-table td {
            padding: 0.95rem 0.85rem;
            border-bottom: 1px solid #e2e8f0;
            background: #fff;
            vertical-align: top;
            text-align: left;
        }

        .financial-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f8fafc;
            color: #36527a;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .financial-table tbody tr:hover td {
            background: #fcfdff;
        }

        .financial-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #0f172a;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .financial-badge--payment {
            background: #ecfdf5;
            border-color: #bbf7d0;
            color: #166534;
        }

        .financial-badge--refund {
            background: #fff1f2;
            border-color: #fecdd3;
            color: #be123c;
        }

        .financial-badge--concept {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1d4ed8;
        }

        .financial-meta {
            color: #64748b;
            font-size: 0.82rem;
            line-height: 1.35;
        }

        .cell-copy {
            min-width: 120px;
            max-width: 220px;
            color: #0f172a;
            font-size: 0.92rem;
            line-height: 1.45;
            word-break: break-word;
        }

        .cell-copy strong {
            display: block;
            font-size: 0.95rem;
        }

        .cell-copy small,
        .mono-copy {
            font-variant-numeric: tabular-nums;
        }

        @media (max-width: 1100px) {
            .financial-summary {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .financial-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>

    <div class="financial-shell">
        <div class="page-head">
            <div>
                <h1 style="margin-bottom: 0.3rem;">Reporte financiero</h1>
                <p class="muted" style="margin: 0;">Movimientos actuales de inscripción y matrícula filtrados por fecha de
                    movimiento.</p>
            </div>
            <div class="actions-row">
                <a class="btn btn-sm btn-ghost btn-sm-nav" href="{{ route('enrollments.index', request()->query()) }}">Volver
                    a inscripciones</a>
                <a class="btn btn-sm btn-secondary btn-sm-nav"
                    href="{{ route('enrollments.financial.export.excel', request()->query(), false) }}">Exportar Excel</a>
                <a class="btn btn-sm btn-sm-nav"
                    href="{{ route('enrollments.financial.export.pdf', request()->query(), false) }}">Exportar PDF</a>
                <a class="btn btn-sm btn-secondary btn-sm-nav" href="{{ route('dashboard') }}">Dashboard</a>
            </div>
        </div>

        <div class="financial-summary">
            <div class="financial-stat"><span>Movimientos</span><strong>{{ $summary['movements'] }}</strong></div>
            <div class="financial-stat"><span>Pagos</span><strong>{{ $summary['payments'] }}</strong></div>
            <div class="financial-stat"><span>Devoluciones</span><strong>{{ $summary['refunds'] }}</strong></div>
            <div class="financial-stat">
                <span>Pagado</span><strong>${{ number_format($summary['amount_paid'], 0, ',', '.') }}</strong>
            </div>
            <div class="financial-stat">
                <span>Devuelto</span><strong>${{ number_format($summary['amount_refunded'], 0, ',', '.') }}</strong>
            </div>
            <div class="financial-stat">
                <span>Neto</span><strong>${{ number_format($summary['net_amount'], 0, ',', '.') }}</strong>
            </div>
        </div>

        <form method="GET" action="{{ route('enrollments.financial') }}">
            <div class="field-grid">
                <div class="field">
                    <label for="search">Buscar</label>
                    <input id="search" name="search" type="text" value="{{ request('search') }}"
                        placeholder="Nombre, documento o correo">
                </div>
                <div class="field">
                    <label for="period">Periodo</label>
                    <select id="period" name="period">
                        <option value="">Todos</option>
                        @foreach ($periods as $period)
                            <option value="{{ $period }}" {{ request('period') === $period ? 'selected' : '' }}>
                                {{ $period }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="field-grid">
                <div class="field">
                    <label for="campus">Sede</label>
                    <select id="campus" name="campus">
                        <option value="">Todas</option>
                        @foreach ($campuses as $campus)
                            <option value="{{ $campus }}" {{ request('campus') === $campus ? 'selected' : '' }}>
                                {{ $campus }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="jornada">Jornada</label>
                    <select id="jornada" name="jornada">
                        <option value="">Todas</option>
                        @foreach ($jornadas as $jornada)
                            <option value="{{ $jornada }}" {{ request('jornada') === $jornada ? 'selected' : '' }}>
                                {{ $jornada }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="field-grid">
                <div class="field">
                    <label for="program">Programa</label>
                    <select id="program" name="program">
                        <option value="">Todos</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program }}" {{ request('program') === $program ? 'selected' : '' }}>
                                {{ $program }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="status">Estado</label>
                    <select id="status" name="status">
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('status') === 'pendiente' ? 'selected' : '' }}>Pendiente
                        </option>
                        <option value="inscrito" {{ request('status') === 'inscrito' ? 'selected' : '' }}>Inscrito</option>
                        <option value="matriculado" {{ request('status') === 'matriculado' ? 'selected' : '' }}>Matriculado
                        </option>
                        <option value="retirado" {{ request('status') === 'retirado' ? 'selected' : '' }}>Retirado</option>
                    </select>
                </div>
                <div class="field">
                    <label for="payment_date_from">Fecha de movimiento desde</label>
                    <input id="payment_date_from" name="payment_date_from" type="date"
                        value="{{ request('payment_date_from') }}">
                </div>
                <div class="field">
                    <label for="payment_date_to">Fecha de movimiento hasta</label>
                    <input id="payment_date_to" name="payment_date_to" type="date"
                        value="{{ request('payment_date_to') }}">
                </div>
            </div>
            <div class="actions-row" style="margin-top: 1rem;">
                <button class="btn btn-sm btn-sm-nav" type="submit">Filtrar</button>
                <a class="btn btn-sm btn-secondary btn-sm-nav" href="{{ route('enrollments.financial') }}">Limpiar</a>
            </div>
        </form>

        <div class="financial-meta">
            Filtros aplicados: {{ $filtersSummary }}
        </div>

        <div class="financial-table-wrap">
            <table class="financial-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Concepto</th>
                        <th>Estudiante</th>
                        <th>Documento</th>
                        <th>Programa</th>
                        <th>Sede</th>
                        <th>Jornada</th>
                        <th>Valor</th>
                        <th>Neto actual</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>
                                <div class="cell-copy mono-copy">
                                    <strong>{{ $row['movement_date']->format('d/m/Y') }}</strong>
                                    <small>{{ $row['movement_date']->format('H:i') }}</small>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="financial-badge {{ $row['movement_type'] === 'payment' ? 'financial-badge--payment' : 'financial-badge--refund' }}">
                                    {{ $row['movement_type_label'] }}
                                </span>
                            </td>
                            <td>
                                <span class="financial-badge financial-badge--concept">{{ $row['concept_label'] }}</span>
                            </td>
                            <td class="cell-copy">{{ $row['student_name'] ?: 'Sin dato' }}</td>
                            <td class="cell-copy mono-copy">{{ $row['document_number'] ?: 'Sin dato' }}</td>
                            <td class="cell-copy">{{ $row['program'] ?: 'Sin dato' }}</td>
                            <td class="cell-copy">{{ $row['campus'] ?: 'Sin dato' }}</td>
                            <td class="cell-copy">{{ $row['jornada'] ?: 'Sin dato' }}</td>
                            <td class="cell-copy mono-copy">${{ number_format($row['movement_amount'], 0, ',', '.') }}</td>
                            <td class="cell-copy mono-copy">${{ number_format($row['net_amount'], 0, ',', '.') }}</td>
                            <td>
                                <span class="financial-badge">{{ $row['status'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="muted" style="padding: 1rem;">No hay movimientos para los filtros
                                seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
