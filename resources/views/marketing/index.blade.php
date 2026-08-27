@extends('layouts.app')

@section('title', 'Mercadeo')
@section('badge', 'Mercadeo')

@push('styles')
    <style>
        .marketing-page {
            display: grid;
            gap: 1rem;
        }

        .marketing-panel {
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card);
            min-width: 0;
        }

        .marketing-panel__heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .marketing-panel__heading h2 {
            margin: 0;
            font-size: 1rem;
        }

        .marketing-tools {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            min-width: 0;
        }

        .marketing-tools select {
            width: auto;
            min-width: 92px;
            min-height: 36px;
            padding: 0.5rem 0.7rem;
        }

        .marketing-table-wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
        }

        .marketing-table {
            width: 100%;
            min-width: 0;
            table-layout: fixed;
        }

        .marketing-table th,
        .marketing-table td {
            padding: 0.72rem 0.55rem;
            font-size: 0.78rem;
            overflow-wrap: anywhere;
            word-break: normal;
        }

        .marketing-table th:nth-child(1),
        .marketing-table td:nth-child(1) {
            width: 9%;
        }

        .marketing-table th:nth-child(2),
        .marketing-table td:nth-child(2) {
            width: 15%;
        }

        .marketing-table th:nth-child(3),
        .marketing-table td:nth-child(3) {
            width: 10%;
        }

        .marketing-table th:nth-child(4),
        .marketing-table td:nth-child(4) {
            width: 9%;
        }

        .marketing-table th:nth-child(5),
        .marketing-table td:nth-child(5) {
            width: 25%;
        }

        .marketing-table th:nth-child(6),
        .marketing-table td:nth-child(6) {
            width: 10%;
        }

        .marketing-table th:nth-child(7),
        .marketing-table td:nth-child(7) {
            width: 22%;
        }

        .marketing-table th {
            background: #f8fafc;
            color: #36527a;
            font-size: 0.7rem;
        }

        .marketing-person {
            color: var(--text);
            font-weight: 600;
        }

        .marketing-note {
            max-width: 360px;
            overflow-wrap: anywhere;
            color: var(--text);
        }

        .marketing-result {
            display: inline-flex;
            padding: 0.24rem 0.55rem;
            border-radius: 999px;
            background: #ffedd5;
            color: #c2410c;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .marketing-result--done {
            background: #dcfce7;
            color: #15803d;
        }

        .marketing-actions {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
        }

        .marketing-action {
            min-height: 30px;
            padding: 0.4rem 0.55rem;
            border: 0;
            border-radius: 7px;
            color: #12305d;
            background: #e8eef7;
            font-size: 0.7rem;
            font-weight: 700;
            text-decoration: none;
            text-align: center;
        }

        .marketing-action--primary {
            color: white;
            background: #0f8a61;
        }

        .marketing-empty {
            padding: 1.25rem;
            color: var(--muted);
            text-align: center;
        }

        @media (max-width: 900px) {
            .marketing-panel__heading {
                align-items: stretch;
                flex-direction: column;
            }

            .marketing-tools .btn {
                width: auto;
                min-width: 0;
            }

        }
    </style>
@endpush

@section('content')
    <div class="marketing-page">
        <div class="page-head">
            <h1>Mercadeo</h1>
            <div class="dashboard-page__tools">
                <label class="dashboard-search" for="marketing-global-search">
                    <span aria-hidden="true">&#128269;</span>
                    <input id="marketing-global-search" type="search" placeholder="Documento, nombre o correo...">
                </label>
                <span class="pill">{{ Auth::user()->role }} · <a href="#logout">Cerrar sesi&oacute;n</a></span>
            </div>
        </div>

        <section class="marketing-panel" aria-labelledby="marketing-title">
            <div class="marketing-panel__heading">
                <h2 id="marketing-title">Seguimiento de mercadeo</h2>
            </div>

            <form method="GET" action="{{ route('marketing.index') }}">
                <div class="marketing-tools">
                    <button class="btn btn-sm" type="button" title="Registro de gestiones no configurado">
                        + Registrar gestión
                    </button>
                    <select name="status" aria-label="Estado del seguimiento">
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('status') === 'pendiente' ? 'selected' : '' }}>Pendiente
                        </option>
                        <option value="inscrito" {{ request('status') === 'inscrito' ? 'selected' : '' }}>Realizado</option>
                        <option value="matriculado" {{ request('status') === 'matriculado' ? 'selected' : '' }}>Realizado
                        </option>
                    </select>
                </div>
            </form>

            <div class="marketing-table-wrap">
                <table class="table-modern marketing-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Persona</th>
                            <th>Gestión</th>
                            <th>Resultado</th>
                            <th>Observación</th>
                            <th>Asesor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($enrollments as $enrollment)
                            @php
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
                                $isDone = in_array($enrollment->student_status, ['inscrito', 'matriculado'], true);
                            @endphp
                            <tr>
                                <td>{{ $enrollment->created_at?->format('Y-m-d') }}</td>
                                <td class="marketing-person">{{ $fullName ?: 'Sin dato' }}</td>
                                <td>Registro web</td>
                                <td>
                                    <span class="marketing-result {{ $isDone ? 'marketing-result--done' : '' }}">
                                        {{ $isDone ? 'Realizado' : 'Pendiente' }}
                                    </span>
                                </td>
                                <td class="marketing-note">
                                    {{ $isDone ? 'Inscripci&oacute;n registrada; continuar seguimiento' : 'Enviar informaci&oacute;n y hacer seguimiento' }}
                                </td>
                                <td>Sin asignar</td>
                                <td>
                                    <div class="marketing-actions">
                                        <button class="marketing-action" type="button" disabled
                                            title="Seguimientos no configurados">
                                            &#128222; Registrar seguimiento
                                        </button>
                                        <a class="marketing-action marketing-action--primary"
                                            href="{{ route('enrollments.index', ['search' => $enrollment->document_number]) }}">
                                            &#128196; Inscribir
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="marketing-empty">No hay registros para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($enrollments->hasPages())
            <div class="pagination-wrap" aria-label="Paginaci&oacute;n">
                {{ $enrollments->links() }}
            </div>
        @endif
    </div>
@endsection
