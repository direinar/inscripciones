@extends('layouts.app')

@section('title', 'Reporte de inscripciones')
@section('badge', 'Inscripciones')

@section('content')
    <style>
        .records-shell {
            display: grid;
            gap: 1.2rem;
        }

        .records-table-wrap {
            overflow: auto;
            border: 1px solid rgba(203, 213, 225, 0.9);
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.06);
        }

        .records-table {
            width: max-content;
            min-width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .records-table th,
        .records-table td {
            padding: 1rem 0.85rem;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid #e2e8f0;
            background: #fff;
        }

        .records-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f8fafc;
            color: #36527a;
            font-size: 0.78rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .records-table tbody tr:hover td {
            background: #fcfdff;
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
        .muted-copy {
            color: #64748b;
            font-size: 0.82rem;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 110px;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: capitalize;
        }

        .status-chip--pendiente {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-chip--inscrito {
            background: #e0f2fe;
            color: #075985;
        }

        .status-chip--matriculado {
            background: #dcfce7;
            color: #166534;
        }

        .status-chip--retirado {
            background: #fef3c7;
            color: #92400e;
        }

        .column-trigger {
            width: 100%;
            min-height: 64px;
            padding: 0.85rem 0.95rem;
            border-radius: 16px;
            border: 1px solid #dbe4f0;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            text-align: left;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .column-trigger:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
        }

        .column-trigger--indigo {
            border-color: #c7d2fe;
            background: linear-gradient(180deg, #eef2ff 0%, #ffffff 100%);
        }

        .column-trigger--teal {
            border-color: #99f6e4;
            background: linear-gradient(180deg, #ecfeff 0%, #ffffff 100%);
        }

        .column-trigger--slate {
            border-color: #cbd5e1;
            background: linear-gradient(180deg, #f1f5f9 0%, #ffffff 100%);
        }

        .column-trigger__eyebrow {
            display: block;
            color: #334155;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .column-trigger__title {
            display: block;
            margin-top: 0.2rem;
            color: #0f172a;
            font-size: 0.98rem;
            font-weight: 800;
        }

        .column-trigger__meta {
            display: block;
            margin-top: 0.15rem;
            color: #64748b;
            font-size: 0.82rem;
        }

        .concept-card {
            width: 100%;
            min-height: 64px;
            padding: 0.85rem 0.95rem;
            border-radius: 16px;
            border: 1px solid #dbe4f0;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            display: grid;
            gap: 0.6rem;
        }

        .concept-card--indigo {
            border-color: #c7d2fe;
            background: linear-gradient(180deg, #eef2ff 0%, #ffffff 100%);
        }

        .concept-card--teal {
            border-color: #99f6e4;
            background: linear-gradient(180deg, #ecfeff 0%, #ffffff 100%);
        }

        .concept-card__actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }

        .movement-modal__list {
            display: grid;
            gap: 0.65rem;
            margin: 0.65rem 0 1rem;
        }

        .movement-modal__item,
        .payment-modal__summary-card {
            padding: 0.85rem 0.95rem;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #f8fafc;
        }

        .movement-modal__item span,
        .payment-modal__summary-card span {
            display: block;
            color: #64748b;
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .movement-modal__item strong,
        .payment-modal__summary-card strong {
            display: block;
            margin-top: 0.15rem;
            color: #0f172a;
            font-size: 1rem;
        }

        .movement-modal__item small {
            display: block;
            margin-top: 0.15rem;
            color: #64748b;
            font-size: 0.82rem;
        }

        .payment-modal__summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.65rem;
            margin: 0.2rem 0 1rem;
        }

        .mini-btn--neutral {
            background: #f8fafc;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .mini-btn {
            min-height: 38px;
            border: 1px solid transparent;
            border-radius: 12px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
        }

        .mini-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
        }

        .mini-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .mini-btn--pay {
            background: #0f766e;
            color: #fff;
        }

        .mini-btn--refund {
            background: #fff1f2;
            color: #be123c;
            border-color: #fecdd3;
        }

        .mono-copy {
            font-variant-numeric: tabular-nums;
        }

        @media (max-width: 768px) {

            .payment-modal__summary,
            .concept-card__actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <div class="records-shell">
        <div class="page-head">
            <div>
                <h1 style="margin-bottom: 0.3rem;">Reporte de inscripciones</h1>
                <p class="muted" style="margin: 0;">Consulta todos los campos del registro, revisa saldos netos y gestiona
                    inscripción y matrícula con devoluciones por separado.</p>
            </div>
            <div class="actions-row">
                <a class="btn btn-sm btn-ghost btn-sm-nav" href="{{ route('enrollments.create') }}" target="_blank">Ver
                    formulario</a>
                <a class="btn btn-sm btn-secondary btn-sm-nav"
                    href="{{ route('enrollments.export.excel', request()->query(), false) }}">Exportar
                    Excel</a>
                <a class="btn btn-sm btn-sm-nav"
                    href="{{ route('enrollments.export.pdf', request()->query(), false) }}">Exportar
                    PDF</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="card" style="padding: 1rem 1.1rem;">
                <div class="muted" style="font-size: 0.85rem;">Total registros</div>
                <div style="font-size: 1.8rem; font-weight: 800; margin-top: 0.35rem;">{{ $summary['total'] }}</div>
            </div>
            <div class="card" style="padding: 1rem 1.1rem;">
                <div class="muted" style="font-size: 0.85rem;">Pendientes</div>
                <div style="font-size: 1.8rem; font-weight: 800; margin-top: 0.35rem;">{{ $summary['pending'] }}</div>
            </div>
            <div class="card" style="padding: 1rem 1.1rem;">
                <div class="muted" style="font-size: 0.85rem;">Inscritos</div>
                <div style="font-size: 1.8rem; font-weight: 800; margin-top: 0.35rem;">{{ $summary['inscribed'] }}</div>
            </div>
            <div class="card" style="padding: 1rem 1.1rem;">
                <div class="muted" style="font-size: 0.85rem;">Matriculados</div>
                <div style="font-size: 1.8rem; font-weight: 800; margin-top: 0.35rem;">{{ $summary['matriculated'] }}</div>
            </div>
            <div class="card" style="padding: 1rem 1.1rem;">
                <div class="muted" style="font-size: 0.85rem;">Con devoluciones</div>
                <div style="font-size: 1.8rem; font-weight: 800; margin-top: 0.35rem;">{{ $summary['with_refunds'] }}</div>
            </div>
            <div class="card" style="padding: 1rem 1.1rem;">
                <div class="muted" style="font-size: 0.85rem;">Saldo inscripción</div>
                <div style="font-size: 1.2rem; font-weight: 800; margin-top: 0.35rem;">
                    ${{ number_format($summary['inscription_paid'], 0, ',', '.') }}</div>
            </div>
            <div class="card" style="padding: 1rem 1.1rem;">
                <div class="muted" style="font-size: 0.85rem;">Saldo matrícula</div>
                <div style="font-size: 1.2rem; font-weight: 800; margin-top: 0.35rem;">
                    ${{ number_format($summary['tuition_paid'], 0, ',', '.') }}</div>
            </div>
            <div class="card" style="padding: 1rem 1.1rem;">
                <div class="muted" style="font-size: 0.85rem;">Devoluciones</div>
                <div style="font-size: 1.2rem; font-weight: 800; margin-top: 0.35rem;">
                    ${{ number_format($summary['refunds'], 0, ',', '.') }}</div>
            </div>
        </div>

        <form method="GET" action="{{ route('enrollments.index') }}">
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
                    <label for="from_date">Fecha de registro desde</label>
                    <input id="from_date" name="from_date" type="date" value="{{ request('from_date') }}">
                </div>
                <div class="field">
                    <label for="to_date">Fecha de registro hasta</label>
                    <input id="to_date" name="to_date" type="date" value="{{ request('to_date') }}">
                </div>
            </div>
            <div class="field-grid">
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
                <a class="btn btn-sm btn-secondary btn-sm-nav" href="{{ route('enrollments.index') }}">Limpiar</a>
            </div>
        </form>

        <div class="records-table-wrap">
            <table class="records-table">
                <thead>
                    <tr>
                        <th>Registro</th>
                        <th>Nombre completo</th>
                        <th>Tipo documento</th>
                        <th>Número documento</th>
                        <th>Sexo</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Celular</th>
                        <th>Fecha nacimiento</th>
                        <th>Dirección</th>
                        <th>Departamento</th>
                        <th>Municipio</th>
                        <th>Periodo</th>
                        <th>Sede</th>
                        <th>Jornada</th>
                        <th>Programa</th>
                        <th>Estado</th>
                        <th>Movimientos</th>
                        <th>Inscripción</th>
                        <th>Matrícula</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enrollments as $enrollment)
                        @php
                            $statusClass = match ($enrollment->student_status) {
                                'matriculado' => 'status-chip status-chip--matriculado',
                                'inscrito' => 'status-chip status-chip--inscrito',
                                'retirado' => 'status-chip status-chip--retirado',
                                default => 'status-chip status-chip--pendiente',
                            };

                            $campus =
                                $enrollment->campus ?:
                                trim(explode(' - ', (string) $enrollment->campus_schedule, 2)[0] ?? '');
                            $jornada =
                                $enrollment->jornada ?:
                                trim(explode(' - ', (string) $enrollment->campus_schedule, 2)[1] ?? '');
                            $latestRefundDate = collect([
                                $enrollment->inscription_refund_date,
                                $enrollment->tuition_refund_date,
                            ])
                                ->filter()
                                ->sortDesc()
                                ->first();
                            $inscriptionBalance = $enrollment->inscriptionNetAmount();
                            $tuitionBalance = $enrollment->tuitionNetAmount();
                            $inscriptionGrossDisplay =
                                (float) ($enrollment->inscription_refund_amount ?? 0) > 0
                                    ? 0
                                    : (float) ($enrollment->inscription_amount_paid ?? 0);
                            $tuitionGrossDisplay =
                                (float) ($enrollment->tuition_refund_amount ?? 0) > 0
                                    ? 0
                                    : (float) ($enrollment->tuition_amount_paid ?? 0);
                            $fullName = trim(
                                implode(
                                    ' ',
                                    array_filter([
                                        $enrollment->first_name,
                                        $enrollment->middle_name,
                                        $enrollment->last_name,
                                        $enrollment->second_last_name,
                                    ]),
                                ),
                            );
                        @endphp
                        <tr>
                            <td>
                                <div class="cell-copy mono-copy">
                                    <strong>{{ $enrollment->created_at->format('d/m/Y') }}</strong>
                                    <small>{{ $enrollment->created_at->format('H:i') }}</small>
                                </div>
                            </td>
                            <td class="cell-copy">{{ $fullName ?: 'Sin dato' }}</td>
                            <td class="cell-copy">{{ $enrollment->document_type }}</td>
                            <td class="cell-copy mono-copy">{{ $enrollment->document_number }}</td>
                            <td class="cell-copy">{{ $enrollment->sex }}</td>
                            <td class="cell-copy">{{ $enrollment->email }}</td>
                            <td class="cell-copy mono-copy">{{ $enrollment->phone ?: 'Sin dato' }}</td>
                            <td class="cell-copy mono-copy">{{ $enrollment->mobile }}</td>
                            <td class="cell-copy mono-copy">
                                {{ $enrollment->birth_date ? $enrollment->birth_date->format('d/m/Y') : 'Sin dato' }}</td>
                            <td class="cell-copy">{{ $enrollment->address ?: 'Sin dato' }}</td>
                            <td class="cell-copy">{{ optional($enrollment->residenceDepartment)->name ?: 'Sin dato' }}
                            </td>
                            <td class="cell-copy">
                                {{ optional($enrollment->residenceMunicipality)->name ?: ($enrollment->residence_city ?: 'Sin dato') }}
                            </td>
                            <td class="cell-copy">{{ $enrollment->period }}</td>
                            <td class="cell-copy">{{ $campus }}</td>
                            <td class="cell-copy">{{ $jornada }}</td>
                            <td>
                                <div class="cell-copy">
                                    <strong>{{ $enrollment->program }}</strong>
                                </div>
                            </td>
                            <td>
                                <span class="{{ $statusClass }}">
                                    {{ ucfirst($enrollment->student_status) }}
                                </span>
                            </td>
                            <td>
                                <button class="column-trigger column-trigger--slate js-movement-trigger" type="button"
                                    data-student="{{ trim($enrollment->first_name . ' ' . $enrollment->middle_name . ' ' . $enrollment->last_name . ' ' . $enrollment->second_last_name) }}"
                                    data-inscription-date="{{ $enrollment->inscription_payment_date ? $enrollment->inscription_payment_date->format('d/m/Y') : 'Sin registro' }}"
                                    data-inscription-gross="{{ number_format((float) ($enrollment->inscription_amount_paid ?? 0), 0, ',', '.') }}"
                                    data-inscription-refund="{{ number_format((float) ($enrollment->inscription_refund_amount ?? 0), 0, ',', '.') }}"
                                    data-inscription-net="{{ number_format($inscriptionBalance, 0, ',', '.') }}"
                                    data-tuition-date="{{ $enrollment->tuition_payment_date ? $enrollment->tuition_payment_date->format('d/m/Y') : 'Sin registro' }}"
                                    data-tuition-gross="{{ number_format((float) ($enrollment->tuition_amount_paid ?? 0), 0, ',', '.') }}"
                                    data-tuition-refund="{{ number_format((float) ($enrollment->tuition_refund_amount ?? 0), 0, ',', '.') }}"
                                    data-tuition-net="{{ number_format($tuitionBalance, 0, ',', '.') }}"
                                    data-refund-date="{{ $latestRefundDate ? $latestRefundDate->format('d/m/Y') : 'Sin registro' }}"
                                    data-refund-total="{{ number_format($enrollment->totalRefundAmount(), 0, ',', '.') }}"
                                    data-status="{{ ucfirst($enrollment->student_status) }}">
                                    <span class="column-trigger__eyebrow">Movimientos</span>
                                    <span class="column-trigger__title">Ver detalle</span>
                                    <span class="column-trigger__meta">Última devolución
                                        {{ $latestRefundDate ? $latestRefundDate->format('d/m/Y') : 'Sin registro' }}</span>
                                </button>
                            </td>
                            <td>
                                <div class="concept-card concept-card--indigo">
                                    <span class="column-trigger__eyebrow">Inscripción</span>
                                    <span class="column-trigger__meta">
                                        Saldo ${{ number_format($inscriptionBalance, 0, ',', '.') }} · Bruto
                                        ${{ number_format($inscriptionGrossDisplay, 2, ',', '.') }}
                                    </span>
                                    <div class="concept-card__actions">
                                        <button class="mini-btn mini-btn--pay js-concept-trigger" type="button"
                                            data-movement-type="payment" data-concept-label="inscripción"
                                            data-title="Inscripción"
                                            data-description="Registra o ajusta el valor del pago de inscripción."
                                            data-saldo="{{ number_format($inscriptionBalance, 0, ',', '.') }}"
                                            data-saldo-raw="{{ number_format($inscriptionBalance, 2, '.', '') }}"
                                            data-bruto="{{ number_format($inscriptionGrossDisplay, 2, ',', '.') }}"
                                            data-devuelto="{{ number_format((float) ($enrollment->inscription_refund_amount ?? 0), 0, ',', '.') }}"
                                            data-payment-date="{{ $enrollment->inscription_payment_date ? $enrollment->inscription_payment_date->toDateString() : now()->toDateString() }}"
                                            data-refund-date="{{ $enrollment->inscription_refund_date ? $enrollment->inscription_refund_date->toDateString() : now()->toDateString() }}"
                                            data-payment-amount="{{ $enrollment->inscription_amount_paid !== null ? (string) $enrollment->inscription_amount_paid : '' }}"
                                            data-route="{{ route('enrollments.payments.update', ['enrollment' => $enrollment->id] + request()->query()) }}">
                                            Pago
                                        </button>
                                        <button class="mini-btn mini-btn--refund js-concept-trigger" type="button"
                                            data-movement-type="refund" data-concept-label="inscripción"
                                            data-title="Inscripción"
                                            data-description="Registra una devolución sobre inscripción."
                                            data-saldo="{{ number_format($inscriptionBalance, 0, ',', '.') }}"
                                            data-saldo-raw="{{ number_format($inscriptionBalance, 2, '.', '') }}"
                                            data-bruto="{{ number_format($inscriptionGrossDisplay, 2, ',', '.') }}"
                                            data-devuelto="{{ number_format((float) ($enrollment->inscription_refund_amount ?? 0), 0, ',', '.') }}"
                                            data-payment-date="{{ $enrollment->inscription_payment_date ? $enrollment->inscription_payment_date->toDateString() : now()->toDateString() }}"
                                            data-refund-date="{{ $enrollment->inscription_refund_date ? $enrollment->inscription_refund_date->toDateString() : now()->toDateString() }}"
                                            data-payment-amount="{{ $enrollment->inscription_amount_paid !== null ? (string) $enrollment->inscription_amount_paid : '' }}"
                                            data-route="{{ route('enrollments.payments.update', ['enrollment' => $enrollment->id] + request()->query()) }}"
                                            {{ $inscriptionBalance <= 0 ? 'disabled' : '' }}>
                                            Devolución
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="concept-card concept-card--teal">
                                    <span class="column-trigger__eyebrow">Matrícula</span>
                                    <span class="column-trigger__meta">
                                        Saldo ${{ number_format($tuitionBalance, 0, ',', '.') }} · Bruto
                                        ${{ number_format($tuitionGrossDisplay, 2, ',', '.') }}
                                    </span>
                                    <div class="concept-card__actions">
                                        <button class="mini-btn mini-btn--pay js-concept-trigger" type="button"
                                            data-movement-type="payment" data-concept-label="matrícula"
                                            data-title="Matrícula"
                                            data-description="Registra o ajusta el valor del pago de matrícula."
                                            data-saldo="{{ number_format($tuitionBalance, 0, ',', '.') }}"
                                            data-saldo-raw="{{ number_format($tuitionBalance, 2, '.', '') }}"
                                            data-bruto="{{ number_format($tuitionGrossDisplay, 2, ',', '.') }}"
                                            data-devuelto="{{ number_format((float) ($enrollment->tuition_refund_amount ?? 0), 0, ',', '.') }}"
                                            data-payment-date="{{ $enrollment->tuition_payment_date ? $enrollment->tuition_payment_date->toDateString() : now()->toDateString() }}"
                                            data-refund-date="{{ $enrollment->tuition_refund_date ? $enrollment->tuition_refund_date->toDateString() : now()->toDateString() }}"
                                            data-payment-amount="{{ $enrollment->tuition_amount_paid !== null ? (string) $enrollment->tuition_amount_paid : '' }}"
                                            data-route="{{ route('enrollments.payments.update', ['enrollment' => $enrollment->id] + request()->query()) }}">
                                            Pago
                                        </button>
                                        <button class="mini-btn mini-btn--refund js-concept-trigger" type="button"
                                            data-movement-type="refund" data-concept-label="matrícula"
                                            data-title="Matrícula"
                                            data-description="Registra una devolución sobre matrícula."
                                            data-saldo="{{ number_format($tuitionBalance, 0, ',', '.') }}"
                                            data-saldo-raw="{{ number_format($tuitionBalance, 2, '.', '') }}"
                                            data-bruto="{{ number_format($tuitionGrossDisplay, 2, ',', '.') }}"
                                            data-devuelto="{{ number_format((float) ($enrollment->tuition_refund_amount ?? 0), 0, ',', '.') }}"
                                            data-payment-date="{{ $enrollment->tuition_payment_date ? $enrollment->tuition_payment_date->toDateString() : now()->toDateString() }}"
                                            data-refund-date="{{ $enrollment->tuition_refund_date ? $enrollment->tuition_refund_date->toDateString() : now()->toDateString() }}"
                                            data-payment-amount="{{ $enrollment->tuition_amount_paid !== null ? (string) $enrollment->tuition_amount_paid : '' }}"
                                            data-route="{{ route('enrollments.payments.update', ['enrollment' => $enrollment->id] + request()->query()) }}"
                                            {{ $tuitionBalance <= 0 ? 'disabled' : '' }}>
                                            Devolución
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <button class="mini-btn mini-btn--neutral js-edit-person-trigger" type="button"
                                    data-route="{{ route('enrollments.personal-data.update', ['enrollment' => $enrollment->id] + request()->query()) }}"
                                    data-first-name="{{ $enrollment->first_name }}"
                                    data-middle-name="{{ $enrollment->middle_name }}"
                                    data-last-name="{{ $enrollment->last_name }}"
                                    data-second-last-name="{{ $enrollment->second_last_name }}"
                                    data-document-type="{{ $enrollment->document_type }}"
                                    data-document-number="{{ $enrollment->document_number }}"
                                    data-sex="{{ $enrollment->sex }}" data-email="{{ $enrollment->email }}"
                                    data-mobile="{{ $enrollment->mobile }}" data-phone="{{ $enrollment->phone }}"
                                    data-birth-date="{{ $enrollment->birth_date ? $enrollment->birth_date->toDateString() : '' }}"
                                    data-address="{{ $enrollment->address }}"
                                    data-audit-by="{{ optional($enrollment->personalDataUpdatedBy)->name ?: 'Sin registro' }}"
                                    data-audit-at="{{ $enrollment->personal_data_updated_at ? $enrollment->personal_data_updated_at->format('d/m/Y H:i') : 'Sin registro' }}">
                                    Editar persona
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="21" class="muted" style="padding: 1rem;">No hay registros para los filtros
                                seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($enrollments->hasPages())
            <div class="pagination-wrap" aria-label="Paginación">
                {{ $enrollments->links() }}
            </div>
        @endif

        <div class="payment-modal" id="movementModal" aria-hidden="true">
            <div class="payment-modal__backdrop" data-close-movement-modal></div>
            <div class="payment-modal__card" role="dialog" aria-modal="true" aria-labelledby="movementModalTitle">
                <button class="payment-modal__close" type="button" data-close-movement-modal
                    aria-label="Cerrar modal">×</button>
                <div class="payment-modal__eyebrow">Detalle</div>
                <h3 id="movementModalTitle">Movimientos del registro</h3>
                <p id="movementModalDescription" class="muted">Resumen de pagos, devoluciones y saldos netos por
                    concepto.</p>
                <div class="movement-modal__list">
                    <div class="movement-modal__item">
                        <span>Inscripción</span>
                        <strong id="movementInscriptionDate">Sin registro</strong>
                        <small id="movementInscriptionMeta">Bruto $0 · Devuelto $0 · Neto $0</small>
                    </div>
                    <div class="movement-modal__item">
                        <span>Matrícula</span>
                        <strong id="movementTuitionDate">Sin registro</strong>
                        <small id="movementTuitionMeta">Bruto $0 · Devuelto $0 · Neto $0</small>
                    </div>
                    <div class="movement-modal__item">
                        <span>Devolución total</span>
                        <strong id="movementRefundDate">Sin registro</strong>
                        <small id="movementRefundMeta">Total devuelto $0 · Estado Pendiente</small>
                    </div>
                </div>
                <div class="actions-row actions-row--end">
                    <button class="btn btn-ghost" type="button" data-close-movement-modal>Cerrar</button>
                </div>
            </div>
        </div>

        <div class="payment-modal" id="paymentModal" aria-hidden="true">
            <div class="payment-modal__backdrop" data-close-payment-modal></div>
            <div class="payment-modal__card" role="dialog" aria-modal="true" aria-labelledby="paymentModalTitle">
                <button class="payment-modal__close" type="button" data-close-payment-modal
                    aria-label="Cerrar modal">×</button>
                <div class="payment-modal__eyebrow">Gestión rápida</div>
                <h3 id="paymentModalTitle">Gestionar concepto</h3>
                <p id="paymentModalDescription" class="muted">Completa los datos para continuar.</p>
                <p id="paymentModalHelper" class="muted" style="margin-top: -0.4rem;"></p>
                <div class="payment-modal__summary" id="paymentModalSummary" hidden>
                    <div class="payment-modal__summary-card">
                        <span>Saldo</span>
                        <strong id="paymentModalBalance">$0</strong>
                    </div>
                    <div class="payment-modal__summary-card">
                        <span>Bruto</span>
                        <strong id="paymentModalGross">$0</strong>
                    </div>
                    <div class="payment-modal__summary-card">
                        <span>Devuelto</span>
                        <strong id="paymentModalRefunded">$0</strong>
                    </div>
                </div>
                <form id="paymentModalForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="movement_type" value="payment">
                    <input type="hidden" name="concept" value="inscription">
                    <div class="field-grid modal-grid">
                        <div class="field">
                            <label for="paymentModalDate">Fecha</label>
                            <input id="paymentModalDate" name="movement_date" type="date" required>
                        </div>
                        <div class="field">
                            <label for="paymentModalAmountDisplay">Valor</label>
                            <input id="paymentModalAmountDisplay" type="text" inputmode="decimal" placeholder="0,00"
                                autocomplete="off" required>
                            <input id="paymentModalAmount" name="movement_amount" type="hidden">
                        </div>
                    </div>
                    <div id="paymentModalError" class="payment-modal__error" role="alert"></div>
                    <div class="actions-row actions-row--end">
                        <button class="btn btn-ghost" type="button" data-close-payment-modal>Cancelar</button>
                        <button class="btn" id="paymentModalSubmit" type="submit">Guardar movimiento</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="payment-modal" id="editPersonModal" aria-hidden="true">
            <div class="payment-modal__backdrop" data-close-edit-person-modal></div>
            <div class="payment-modal__card" role="dialog" aria-modal="true" aria-labelledby="editPersonTitle">
                <button class="payment-modal__close" type="button" data-close-edit-person-modal
                    aria-label="Cerrar modal">×</button>
                <div class="payment-modal__eyebrow">Gestión de persona</div>
                <h3 id="editPersonTitle">Editar datos personales</h3>
                <p class="muted">Actualiza la información principal del registro seleccionado.</p>
                <p id="editPersonAuditInfo" class="muted" style="margin-top: -0.25rem;">Última edición: Sin registro.
                </p>

                <form id="editPersonForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="field-grid modal-grid">
                        <div class="field">
                            <label for="edit_first_name">Primer nombre</label>
                            <input id="edit_first_name" name="first_name" type="text" required>
                        </div>
                        <div class="field">
                            <label for="edit_middle_name">Segundo nombre</label>
                            <input id="edit_middle_name" name="middle_name" type="text">
                        </div>
                    </div>
                    <div class="field-grid modal-grid">
                        <div class="field">
                            <label for="edit_last_name">Primer apellido</label>
                            <input id="edit_last_name" name="last_name" type="text" required>
                        </div>
                        <div class="field">
                            <label for="edit_second_last_name">Segundo apellido</label>
                            <input id="edit_second_last_name" name="second_last_name" type="text">
                        </div>
                    </div>
                    <div class="field-grid modal-grid">
                        <div class="field">
                            <label for="edit_document_type">Tipo documento</label>
                            <input id="edit_document_type" name="document_type" type="text" required>
                        </div>
                        <div class="field">
                            <label for="edit_document_number">Número documento</label>
                            <input id="edit_document_number" name="document_number" type="text" required>
                        </div>
                    </div>
                    <div class="field-grid modal-grid">
                        <div class="field">
                            <label for="edit_sex">Sexo</label>
                            <input id="edit_sex" name="sex" type="text" required>
                        </div>
                        <div class="field">
                            <label for="edit_birth_date">Fecha nacimiento</label>
                            <input id="edit_birth_date" name="birth_date" type="date">
                        </div>
                    </div>
                    <div class="field-grid modal-grid">
                        <div class="field">
                            <label for="edit_email">Correo</label>
                            <input id="edit_email" name="email" type="email" required>
                        </div>
                        <div class="field">
                            <label for="edit_mobile">Celular</label>
                            <input id="edit_mobile" name="mobile" type="text" required>
                        </div>
                    </div>
                    <div class="field-grid modal-grid">
                        <div class="field">
                            <label for="edit_phone">Teléfono</label>
                            <input id="edit_phone" name="phone" type="text">
                        </div>
                        <div class="field">
                            <label for="edit_address">Dirección</label>
                            <input id="edit_address" name="address" type="text">
                        </div>
                    </div>

                    <div class="actions-row actions-row--end" style="margin-top: 1rem;">
                        <button class="btn btn-ghost" type="button" data-close-edit-person-modal>Cancelar</button>
                        <button class="btn" type="submit">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            var movementModal = document.getElementById('movementModal');
            var movementInscriptionDate = document.getElementById('movementInscriptionDate');
            var movementInscriptionMeta = document.getElementById('movementInscriptionMeta');
            var movementTuitionDate = document.getElementById('movementTuitionDate');
            var movementTuitionMeta = document.getElementById('movementTuitionMeta');
            var movementRefundDate = document.getElementById('movementRefundDate');
            var movementRefundMeta = document.getElementById('movementRefundMeta');

            function closeMovementModal() {
                if (!movementModal) return;
                movementModal.classList.remove('is-open');
                movementModal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
            }

            document.querySelectorAll('.js-movement-trigger').forEach(function(button) {
                button.addEventListener('click', function() {
                    if (!movementModal) return;

                    movementInscriptionDate.textContent = button.dataset.inscriptionDate ||
                        'Sin registro';
                    movementInscriptionMeta.textContent = 'Bruto $' + (button.dataset
                            .inscriptionGross || '0') +
                        ' · Devuelto $' + (button.dataset.inscriptionRefund || '0') +
                        ' · Neto $' + (button.dataset.inscriptionNet || '0');

                    movementTuitionDate.textContent = button.dataset.tuitionDate || 'Sin registro';
                    movementTuitionMeta.textContent = 'Bruto $' + (button.dataset.tuitionGross || '0') +
                        ' · Devuelto $' + (button.dataset.tuitionRefund || '0') +
                        ' · Neto $' + (button.dataset.tuitionNet || '0');

                    movementRefundDate.textContent = button.dataset.refundDate || 'Sin registro';
                    movementRefundMeta.textContent = 'Total devuelto $' + (button.dataset.refundTotal ||
                            '0') +
                        ' · Estado ' + (button.dataset.status || 'Pendiente');

                    movementModal.classList.add('is-open');
                    movementModal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('modal-open');
                });
            });

            document.querySelectorAll('[data-close-movement-modal]').forEach(function(element) {
                element.addEventListener('click', function(event) {
                    event.preventDefault();
                    closeMovementModal();
                });
            });

            var modal = document.getElementById('paymentModal');
            var form = document.getElementById('paymentModalForm');
            var title = document.getElementById('paymentModalTitle');
            var description = document.getElementById('paymentModalDescription');
            var submit = document.getElementById('paymentModalSubmit');
            var dateInput = document.getElementById('paymentModalDate');
            var amountInput = document.getElementById('paymentModalAmount');
            var amountDisplayInput = document.getElementById('paymentModalAmountDisplay');
            var errorBox = document.getElementById('paymentModalError');
            var helper = document.getElementById('paymentModalHelper');
            var summary = document.getElementById('paymentModalSummary');
            var balanceBox = document.getElementById('paymentModalBalance');
            var grossBox = document.getElementById('paymentModalGross');
            var refundedBox = document.getElementById('paymentModalRefunded');
            var conceptTriggers = document.querySelectorAll('.js-concept-trigger');
            var movementTypeInput = form ? form.querySelector('input[name="movement_type"]') : null;
            var conceptInput = form ? form.querySelector('input[name="concept"]') : null;
            var activeTrigger = null;

            if (!modal || !form || !dateInput || !amountInput || !amountDisplayInput || !movementTypeInput ||
                !conceptInput) return;

            function parseAmount(value) {
                var raw = String(value || '').trim();
                if (!raw) return null;

                var cleaned = raw.replace(/[^\d,.-]/g, '');
                var hasComma = cleaned.indexOf(',') !== -1;
                var hasDot = cleaned.indexOf('.') !== -1;

                if (hasComma && hasDot) {
                    if (cleaned.lastIndexOf(',') > cleaned.lastIndexOf('.')) {
                        cleaned = cleaned.replace(/\./g, '').replace(',', '.');
                    } else {
                        cleaned = cleaned.replace(/,/g, '');
                    }
                } else if (hasComma) {
                    cleaned = cleaned.replace(/\./g, '').replace(',', '.');
                } else {
                    cleaned = cleaned.replace(/,/g, '');
                }

                var parsed = Number(cleaned);
                return Number.isFinite(parsed) ? parsed : null;
            }

            function formatAmount(numberValue) {
                return new Intl.NumberFormat('es-CO', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(numberValue);
            }

            function setAmountValue(rawValue) {
                var parsed = parseAmount(rawValue);

                if (parsed === null) {
                    amountDisplayInput.value = '';
                    amountInput.value = '';
                    return;
                }

                amountInput.value = parsed.toFixed(2);
                amountDisplayInput.value = formatAmount(parsed);
            }

            amountDisplayInput.addEventListener('input', function() {
                var parsed = parseAmount(amountDisplayInput.value);

                if (parsed === null) {
                    amountInput.value = '';
                    return;
                }

                amountInput.value = parsed.toFixed(2);
            });

            amountDisplayInput.addEventListener('blur', function() {
                var parsed = parseAmount(amountDisplayInput.value);

                if (parsed === null) {
                    amountDisplayInput.value = '';
                    amountInput.value = '';
                    return;
                }

                amountInput.value = parsed.toFixed(2);
                amountDisplayInput.value = formatAmount(parsed);
            });

            function updateSummary(trigger) {
                if (!summary || !balanceBox || !grossBox || !refundedBox) return;

                if (!trigger) {
                    summary.hidden = true;
                    return;
                }

                summary.hidden = false;
                balanceBox.textContent = '$' + (trigger.dataset.saldo || '0');
                grossBox.textContent = '$' + (trigger.dataset.bruto || '0');
                refundedBox.textContent = '$' + (trigger.dataset.devuelto || '0');
            }

            function getConceptKey(trigger) {
                if (!trigger) return 'inscription';

                var label = (trigger.dataset.conceptLabel || '').toLowerCase();
                return label === 'matrícula' ? 'tuition' : 'inscription';
            }

            function getConceptLabel(trigger) {
                return (trigger && trigger.dataset.conceptLabel) ? trigger.dataset.conceptLabel : 'inscripción';
            }

            function setModalMode(options) {
                var mode = options || {};
                var conceptLabel = getConceptLabel(activeTrigger);
                var movementType = mode.movementType || 'payment';
                var conceptKey = mode.concept || getConceptKey(activeTrigger);
                var resolvedAmount = mode.defaultAmount || '';

                if (movementType === 'payment' && (resolvedAmount === '0' || resolvedAmount === '0.00')) {
                    resolvedAmount = '';
                }

                movementTypeInput.value = movementType;
                conceptInput.value = conceptKey;
                title.textContent = movementType === 'refund' ?
                    'Registrar devolución de ' + conceptLabel :
                    'Registrar pago de ' + conceptLabel;
                description.textContent = movementType === 'refund' ?
                    'Ingresa la devolución y el sistema descontará el valor del saldo neto.' :
                    'Actualiza el valor pagado y conserva consistencia con devoluciones acumuladas.';
                submit.textContent = movementType === 'refund' ? 'Guardar devolución' : 'Guardar movimiento';
                helper.textContent = mode.helper || '';
                dateInput.value = mode.defaultDate || '';
                setAmountValue(resolvedAmount);
                errorBox.textContent = '';

                if (mode.showSummary) {
                    updateSummary(mode.trigger || null);
                } else {
                    updateSummary(null);
                }
            }

            function closeModal() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
                errorBox.textContent = '';
                helper.textContent = '';
                updateSummary(null);
                form.reset();
                amountDisplayInput.value = '';
                amountInput.value = '';
                activeTrigger = null;
            }

            conceptTriggers.forEach(function(button) {
                button.addEventListener('click', function() {
                    activeTrigger = button;
                    form.action = button.dataset.route || '';

                    var conceptKey = getConceptKey(button);
                    var movementType = button.dataset.movementType || 'payment';
                    var isRefund = movementType === 'refund';
                    setModalMode({
                        movementType: movementType,
                        concept: conceptKey,
                        defaultDate: isRefund ? (button.dataset.refundDate || '') : (button
                            .dataset
                            .paymentDate || ''),
                        defaultAmount: isRefund ? (button.dataset.saldoRaw || '') : (button
                            .dataset
                            .paymentAmount || ''),
                        helper: isRefund ?
                            'Disponible para devolver: $' + (button.dataset.saldo || '0') +
                            '.' : 'Bruto actual: $' + (button.dataset.bruto || '0') +
                            ' · Devuelto acumulado: $' + (button.dataset.devuelto || '0') + '.',
                        showSummary: true,
                        trigger: button
                    });

                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('modal-open');
                    setTimeout(function() {
                        dateInput.focus();
                    }, 60);
                });
            });

            document.querySelectorAll('[data-close-payment-modal]').forEach(function(element) {
                element.addEventListener('click', function(event) {
                    event.preventDefault();
                    closeModal();
                });
            });

            if (movementModal) {
                movementModal.addEventListener('click', function(event) {
                    if (event.target === movementModal) {
                        closeMovementModal();
                    }
                });
            }

            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            form.addEventListener('submit', function(event) {
                event.preventDefault();
                errorBox.textContent = '';

                var parsedAmount = parseAmount(amountDisplayInput.value);
                if (parsedAmount !== null) {
                    amountInput.value = parsedAmount.toFixed(2);
                }

                var amountValue = Number(amountInput.value);

                if (!dateInput.value || !amountDisplayInput.value.trim()) {
                    errorBox.textContent = 'Completa la fecha y el valor antes de continuar.';
                    return;
                }

                if (!Number.isFinite(amountValue) || amountValue <= 0) {
                    errorBox.textContent = 'El valor debe ser mayor que 0 para continuar.';
                    return;
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'question',
                        title: 'Confirmar acción',
                        text: '¿Deseas guardar ' + (title.textContent || 'este movimiento') + '?',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, guardar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        focusCancel: true
                    }).then(function(result) {
                        if (!result.isConfirmed) {
                            return;
                        }

                        form.submit();
                    });
                } else {
                    form.submit();
                }
            });

            var editPersonModal = document.getElementById('editPersonModal');
            var editPersonForm = document.getElementById('editPersonForm');
            var editPersonAuditInfo = document.getElementById('editPersonAuditInfo');

            function closeEditPersonModal() {
                if (!editPersonModal || !editPersonForm) return;
                editPersonModal.classList.remove('is-open');
                editPersonModal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
                editPersonForm.reset();
            }

            document.querySelectorAll('.js-edit-person-trigger').forEach(function(button) {
                button.addEventListener('click', function() {
                    if (!editPersonModal || !editPersonForm) return;

                    editPersonForm.action = button.dataset.route || '';
                    editPersonForm.querySelector('input[name="first_name"]').value = button.dataset
                        .firstName || '';
                    editPersonForm.querySelector('input[name="middle_name"]').value = button.dataset
                        .middleName || '';
                    editPersonForm.querySelector('input[name="last_name"]').value = button.dataset
                        .lastName || '';
                    editPersonForm.querySelector('input[name="second_last_name"]').value = button
                        .dataset
                        .secondLastName || '';
                    editPersonForm.querySelector('input[name="document_type"]').value = button.dataset
                        .documentType || '';
                    editPersonForm.querySelector('input[name="document_number"]').value = button.dataset
                        .documentNumber || '';
                    editPersonForm.querySelector('input[name="sex"]').value = button.dataset.sex || '';
                    editPersonForm.querySelector('input[name="email"]').value = button.dataset.email ||
                        '';
                    editPersonForm.querySelector('input[name="mobile"]').value = button.dataset
                        .mobile || '';
                    editPersonForm.querySelector('input[name="phone"]').value = button.dataset.phone ||
                        '';
                    editPersonForm.querySelector('input[name="birth_date"]').value = button.dataset
                        .birthDate || '';
                    editPersonForm.querySelector('input[name="address"]').value = button.dataset
                        .address ||
                        '';

                    if (editPersonAuditInfo) {
                        editPersonAuditInfo.textContent = 'Última edición por: ' + (button.dataset
                            .auditBy || 'Sin registro') + ' · ' + (button.dataset.auditAt ||
                            'Sin registro') + '.';
                    }

                    editPersonModal.classList.add('is-open');
                    editPersonModal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('modal-open');
                });
            });

            document.querySelectorAll('[data-close-edit-person-modal]').forEach(function(element) {
                element.addEventListener('click', function(event) {
                    event.preventDefault();
                    closeEditPersonModal();
                });
            });

            if (editPersonModal) {
                editPersonModal.addEventListener('click', function(event) {
                    if (event.target === editPersonModal) {
                        closeEditPersonModal();
                    }
                });
            }
        })();
    </script>
@endpush
