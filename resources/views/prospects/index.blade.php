@extends('layouts.app')

@section('title', 'Prospectos')
@section('badge', 'Prospectos')

@push('styles')
    <style>
        .prospects-page {
            display: grid;
            gap: 1rem;
        }

        .prospects-page h1,
        .prospects-panel h2 {
            margin: 0;
        }

        .prospects-panel {
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card);
        }

        .prospects-panel__heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .prospects-filters {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .prospects-filters input,
        .prospects-filters select {
            width: auto;
            min-width: 180px;
            min-height: 36px;
            padding: 0.5rem 0.7rem;
        }

        .prospects-table-wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
        }

        .prospects-table {
            min-width: 920px;
        }

        .prospect-person {
            display: grid;
            gap: 0.15rem;
        }

        .prospect-person strong {
            color: var(--text);
        }

        .prospect-person small {
            color: var(--muted);
        }

        .prospect-status {
            display: inline-flex;
            padding: 0.25rem 0.55rem;
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .prospect-status--matriculado {
            background: #dcfce7;
            color: #15803d;
        }

        .prospect-status--retirado {
            background: #fee2e2;
            color: #b91c1c;
        }

        .prospect-status--inscrito {
            background: #dbeafe;
            color: #2563eb;
        }

        .prospects-empty {
            padding: 1.5rem;
            color: var(--muted);
            text-align: center;
        }

        .prospect-modal {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .prospect-modal.is-open {
            display: flex;
        }

        .prospect-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
        }

        .prospect-modal__dialog {
            position: relative;
            z-index: 1;
            width: min(100%, 610px);
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
            padding: 1.5rem;
            border-radius: 14px;
            background: white;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.22);
        }

        .prospect-modal__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .prospect-modal__header h2 {
            margin: 0;
        }

        .prospect-modal__close {
            width: 30px;
            height: 30px;
            border: 0;
            border-radius: 50%;
            background: #eef2f7;
            color: var(--text);
            cursor: pointer;
            font-size: 1rem;
        }

        .prospect-modal__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.7rem 1rem;
        }

        .prospect-modal__field {
            margin: 0;
        }

        .prospect-modal__field label {
            margin-bottom: 0.3rem;
            color: var(--muted);
            font-size: 0.78rem;
        }

        .prospect-modal__field input,
        .prospect-modal__field select {
            min-height: 36px;
            padding: 0.5rem 0.65rem;
        }

        .prospect-modal__actions {
            display: flex;
            justify-content: flex-start;
            margin-top: 1rem;
        }

        body.prospect-modal-open {
            overflow: hidden;
        }

        @media (max-width: 900px) {
            .prospects-panel__heading {
                align-items: stretch;
                flex-direction: column;
            }

            .prospects-filters input,
            .prospects-filters select,
            .prospects-filters .btn {
                width: 100%;
            }

            .prospect-modal__grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="prospects-page">
        <div class="page-head">
            <h1>Prospectos</h1>
            <div class="dashboard-page__tools">
                <label class="dashboard-search" for="prospects-global-search">
                    <span aria-hidden="true">&#128269;</span>
                    <input id="prospects-global-search" type="search" placeholder="Documento, nombre o correo...">
                </label>
                <span class="pill">{{ Auth::user()->role }} · <a href="#logout">Cerrar sesión</a></span>
            </div>
        </div>

        <section class="prospects-panel" aria-labelledby="prospects-table-title">
            <div class="prospects-panel__heading">
                <h2 id="prospects-table-title">Prospectos y estudiantes</h2>
            </div>

            <form class="prospects-filters" method="GET" action="{{ route('prospects.index') }}">
                <input type="search" name="search" value="{{ request('search') }}"
                    placeholder="Buscar por nombre, documento">
                <select name="status" aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    @foreach ([
            'prospecto' => 'Prospecto',
            'contactado' => 'Contactado',
            'interesado' => 'Interesado',
            'inscripcion_pendiente' => 'Inscripción pendiente',
            'inscrito' => 'Inscripción pagada',
            'matricula_pendiente' => 'Matrícula pendiente',
            'matriculado' => 'Matriculado',
            'retirado' => 'Retirado',
        ] as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-sm" type="submit">Filtrar</button>
            </form>

            <div class="prospects-table-wrap">
                <table class="table-modern prospects-table">
                    <thead>
                        <tr>
                            <th>Persona</th>
                            <th>Programa</th>
                            <th>Período</th>
                            <th>Teléfono</th>
                            <th>Asesor</th>
                            <th>Estado</th>
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
                                $statusClass = match ($enrollment->student_status) {
                                    'matriculado' => 'prospect-status prospect-status--matriculado',
                                    'inscrito' => 'prospect-status prospect-status--inscrito',
                                    'retirado' => 'prospect-status prospect-status--retirado',
                                    default => 'prospect-status',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="prospect-person">
                                        <strong>{{ $fullName ?: 'Sin dato' }}</strong>
                                        <small>{{ $enrollment->document_number }} ·
                                            {{ $enrollment->residence_city }}</small>
                                    </div>
                                </td>
                                <td>{{ $enrollment->program }}</td>
                                <td>{{ $enrollment->period }}</td>
                                <td>{{ $enrollment->mobile ?: $enrollment->phone ?: 'Sin dato' }}</td>
                                <td>Sin asignar</td>
                                <td><span class="{{ $statusClass }}">{{ ucfirst($enrollment->student_status) }}</span>
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-ghost"
                                        href="{{ route('enrollments.index', ['search' => $enrollment->document_number]) }}">
                                        &#128100; Asignar asesor
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="prospects-empty">No hay prospectos para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($enrollments->hasPages())
            <div class="pagination-wrap" aria-label="Paginación">
                {{ $enrollments->links() }}
            </div>
        @endif

        <div class="prospect-modal" id="newProspectModal" aria-hidden="true">
            <div class="prospect-modal__backdrop" data-close-prospect-modal></div>
            <div class="prospect-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="newProspectModalTitle">
                <div class="prospect-modal__header">
                    <h2 id="newProspectModalTitle">Nuevo prospecto</h2>
                    <button class="prospect-modal__close" type="button" data-close-prospect-modal
                        aria-label="Cerrar modal">&times;</button>
                </div>

                <div class="prospect-modal__grid">
                    <div class="prospect-modal__field">
                        <label for="prospect-name">Nombres y apellidos</label>
                        <input id="prospect-name" type="text">
                    </div>
                    <div class="prospect-modal__field">
                        <label for="prospect-document">Documento</label>
                        <input id="prospect-document" type="text">
                    </div>
                    <div class="prospect-modal__field">
                        <label for="prospect-phone">Teléfono / WhatsApp</label>
                        <input id="prospect-phone" type="text">
                    </div>
                    <div class="prospect-modal__field">
                        <label for="prospect-email">Correo</label>
                        <input id="prospect-email" type="email">
                    </div>
                    <div class="prospect-modal__field">
                        <label for="prospect-city">Municipio</label>
                        <input id="prospect-city" type="text">
                    </div>
                    <div class="prospect-modal__field">
                        <label for="prospect-period">Período académico</label>
                        <select id="prospect-period">
                            @forelse ($periods as $period)
                                <option value="{{ $period }}">{{ $period }}</option>
                            @empty
                                <option value="">Sin períodos disponibles</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="prospect-modal__field">
                        <label for="prospect-program">Programa</label>
                        <select id="prospect-program">
                            @forelse ($programs as $program)
                                <option value="{{ $program }}">{{ $program }}</option>
                            @empty
                                <option value="">Sin programas disponibles</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="prospect-modal__field">
                        <label for="prospect-advisor">Asesor</label>
                        <input id="prospect-advisor" type="text">
                    </div>
                    <div class="prospect-modal__field">
                        <label for="prospect-source">Fuente</label>
                        <select id="prospect-source">
                            <option value="whatsapp">WhatsApp</option>
                            <option value="facebook">Facebook</option>
                            <option value="instagram">Instagram</option>
                            <option value="pagina_web">Página web</option>
                            <option value="formulario_web">Formulario web</option>
                            <option value="referido">Referido</option>
                            <option value="feria_visita">Feria / visita</option>
                        </select>
                    </div>
                    <div class="prospect-modal__field">
                        <label for="prospect-status">Estado</label>
                        <select id="prospect-status">
                            <option value="prospecto">Prospecto</option>
                            <option value="contactado">Contactado</option>
                            <option value="interesado">Interesado</option>
                            <option value="inscripcion_pendiente">Inscripción pendiente</option>
                            <option value="inscrito">Inscripción pagada</option>
                            <option value="matricula_pendiente">Matrícula pendiente</option>
                            <option value="matriculado">Matriculado</option>
                            <option value="retirado">Retirado</option>
                        </select>
                    </div>
                </div>

                <div class="prospect-modal__actions">
                    <button class="btn" type="button" data-save-prospect>Guardar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@endpush
