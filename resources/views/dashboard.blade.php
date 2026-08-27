@extends('layouts.app')

@section('title', 'Dashboard')
@section('badge', 'Panel principal')

@push('styles')
    <style>
        .dashboard-page {
            display: grid;
            gap: 1rem;
        }

        .dashboard-page__head {
            align-items: center;
        }

        .dashboard-page__head h1 {
            margin: 0;
        }

        .dashboard-page__tools,
        .dashboard-period-filter,
        .dashboard-filters {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .dashboard-search {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            min-width: min(100%, 290px);
            padding: 0 0.7rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: white;
        }

        .dashboard-search input {
            min-height: 38px;
            padding-left: 0.2rem;
            border: 0;
        }

        .dashboard-search input:focus {
            box-shadow: none;
        }

        .dashboard-section,
        .dashboard-panel {
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card);
        }

        .dashboard-period-filter label {
            margin: 0;
        }

        .dashboard-period-filter select {
            width: 180px;
        }

        .dashboard-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.8rem;
        }

        .dashboard-stat-grid--four {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin: 1rem 0;
        }

        .dashboard-stat-card {
            min-height: 96px;
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
        }

        .dashboard-stat-card span {
            display: block;
            color: var(--muted);
            font-size: 0.8rem;
        }

        .dashboard-stat-card strong {
            display: block;
            margin-top: 0.55rem;
            color: var(--primary-dark);
            font-size: 1.6rem;
        }

        .dashboard-panel__heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .dashboard-panel h2 {
            margin-bottom: 0.3rem;
        }

        .dashboard-panel__heading p {
            margin: 0;
            color: var(--muted);
        }

        .dashboard-filters {
            margin: 1rem 0 0.5rem;
        }

        .dashboard-filters input,
        .dashboard-filters select {
            width: auto;
            min-width: 190px;
            min-height: 36px;
            padding: 0.5rem 0.7rem;
        }

        .dashboard-empty {
            padding: 1.5rem 0.8rem;
            color: var(--muted);
            text-align: center;
        }

        .dashboard-columns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .dashboard-simple-table {
            margin-top: 0.5rem;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        @media (max-width: 768px) {

            .dashboard-page__head,
            .dashboard-panel__heading {
                align-items: stretch;
                flex-direction: column;
            }

            .dashboard-stat-grid,
            .dashboard-stat-grid--four,
            .dashboard-columns {
                grid-template-columns: 1fr;
            }

            .dashboard-search,
            .dashboard-filters input,
            .dashboard-filters select,
            .dashboard-period-filter select,
            .dashboard-period-filter .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-page">
        <div class="page-head dashboard-page__head">
            <h1>Dashboard</h1>
            <div class="dashboard-page__tools">
                <label class="dashboard-search" for="dashboard-search">
                    <span aria-hidden="true">&#128269;</span>
                    <input id="dashboard-search" type="search" placeholder="Documento, nombre o correo...">
                </label>
                <span class="pill">{{ Auth::user()->role }} · <a href="#logout">Cerrar sesión</a></span>
            </div>
        </div>

        <section class="dashboard-section" aria-labelledby="period-filter-title">
            <div class="dashboard-period-filter">
                <label id="period-filter-title" for="academic-period">Período académico:</label>
                <select id="academic-period" name="academic_period">
                    <option value="">Seleccionar período</option>
                </select>
                <button class="btn btn-ghost" type="button">Usar período actual</button>
            </div>
        </section>

        <section class="dashboard-section" aria-labelledby="summary-title">
            <h2 id="summary-title" class="sr-only">Resumen general</h2>
            <div class="dashboard-stat-grid">
                @foreach (['Personas registradas', 'Registros de página web', 'Inscripción pendiente', 'Inscripción pagada', 'Matrícula pendiente', 'Matriculados', 'Retirados', 'Recaudo inscripción/matrícula'] as $stat)
                    <div class="dashboard-stat-card">
                        <span>{{ $stat }}</span>
                        <strong>-</strong>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="dashboard-section dashboard-panel" aria-labelledby="web-records-title">
            <div class="dashboard-panel__heading">
                <div>
                    <h2 id="web-records-title">&#127760; Registros de la página web</h2>
                    <p>Personas que diligencian el formulario y entran automáticamente como prospectos.</p>
                </div>
            </div>
            <div class="dashboard-stat-grid dashboard-stat-grid--four">
                @foreach (['Total registros web', 'Nuevos hoy', 'Con inscripción', 'Matriculados'] as $stat)
                    <div class="dashboard-stat-card"><span>{{ $stat }}</span><strong>-</strong></div>
                @endforeach
            </div>
            <div class="dashboard-filters">
                <input type="search" placeholder="Buscar nombre, documento">
                <select aria-label="Filtrar por período">
                    <option>Todos los períodos</option>
                </select>
            </div>
            <div class="table-wrap">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Registro</th>
                            <th>Estudiante</th>
                            <th>Documento</th>
                            <th>Programa</th>
                            <th>Período</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="dashboard-empty">No hay registros para mostrar.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="dashboard-section dashboard-panel" aria-labelledby="web-summary-title">
            <h2 id="web-summary-title">&#127760; Registros provenientes de la página web</h2>
            <div class="table-wrap">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Período</th>
                            <th>Registros web</th>
                            <th>Inscritos</th>
                            <th>Matriculados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" class="dashboard-empty">No hay información para mostrar.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="dashboard-columns">
            <section class="dashboard-panel" aria-labelledby="funnel-title">
                <h2 id="funnel-title">Embudo comercial</h2>
                <table class="table-modern dashboard-simple-table">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" class="dashboard-empty">No hay información para mostrar.</td>
                        </tr>
                    </tbody>
                </table>
            </section>
            <section class="dashboard-panel" aria-labelledby="movements-title">
                <h2 id="movements-title">Últimos movimientos</h2>
                <table class="table-modern dashboard-simple-table">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Concepto</th>
                            <th>Valor</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" class="dashboard-empty">No hay movimientos para mostrar.</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>

        <section class="dashboard-panel" aria-labelledby="followups-title">
            <h2 id="followups-title">Seguimientos pendientes</h2>
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Seguimiento</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4" class="dashboard-empty">No hay seguimientos pendientes.</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </div>
@endsection
