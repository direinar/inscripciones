@extends('layouts.app')

@section('title', 'Reporte de inscripciones')
@section('badge', 'Inscripciones')

@section('content')
    <div class="grid" style="gap: 1.2rem;">
        <div class="page-head">
            <div>
                <h1 style="margin-bottom: 0.3rem;">Reporte de inscripciones</h1>
                <p class="muted" style="margin: 0;">Consulta los registros enviados desde el formulario público y exporta la
                    información filtrada.</p>
            </div>
            <div class="actions-row">
                <a class="btn btn-sm btn-ghost btn-sm-nav" href="{{ route('enrollments.create') }}" target="_blank">Ver
                    formulario</a>
                <a class="btn btn-sm btn-secondary btn-sm-nav"
                    href="{{ route('enrollments.export.excel', request()->query()) }}">Exportar
                    Excel</a>
                <a class="btn btn-sm btn-sm-nav" href="{{ route('enrollments.export.pdf', request()->query()) }}">Exportar
                    PDF</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="card" style="padding: 1rem 1.1rem;">
                <div class="muted" style="font-size: 0.85rem;">Total registros</div>
                <div style="font-size: 1.8rem; font-weight: 800; margin-top: 0.35rem;">{{ $summary['total'] }}</div>
            </div>
            <div class="card" style="padding: 1rem 1.1rem;">
                <div class="muted" style="font-size: 0.85rem;">Registros hoy</div>
                <div style="font-size: 1.8rem; font-weight: 800; margin-top: 0.35rem;">{{ $summary['today'] }}</div>
            </div>
            <div class="card" style="padding: 1rem 1.1rem;">
                <div class="muted" style="font-size: 0.85rem;">Registros este mes</div>
                <div style="font-size: 1.8rem; font-weight: 800; margin-top: 0.35rem;">{{ $summary['this_month'] }}</div>
            </div>
            <div class="card" style="padding: 1rem 1.1rem;">
                <div class="muted" style="font-size: 0.85rem;">Estado activo</div>
                <div style="font-size: 1.8rem; font-weight: 800; margin-top: 0.35rem;">{{ $summary['active'] }}</div>
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
                    <label for="from_date">Fecha desde</label>
                    <input id="from_date" name="from_date" type="date" value="{{ request('from_date') }}">
                </div>
                <div class="field">
                    <label for="to_date">Fecha hasta</label>
                    <input id="to_date" name="to_date" type="date" value="{{ request('to_date') }}">
                </div>
            </div>
            <div class="actions-row" style="margin-top: 1rem;">
                <button class="btn btn-sm btn-sm-nav" type="submit">Filtrar</button>
                <a class="btn btn-sm btn-secondary btn-sm-nav" href="{{ route('enrollments.index') }}">Limpiar</a>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Nombre completo</th>
                        <th>Programa</th>
                        <th class="hide-sm">Documento</th>
                        <th class="hide-md">Contacto</th>
                        <th class="hide-lg">Departamento</th>
                        <th class="hide-lg">Municipio</th>
                        <th>Estado</th>
                        <th>Pago inscripción</th>
                        <th>Pago matrícula</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enrollments as $enrollment)
                        <tr>
                            <td>{{ $enrollment->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <strong>{{ trim($enrollment->first_name . ' ' . $enrollment->middle_name . ' ' . $enrollment->last_name . ' ' . $enrollment->second_last_name) }}</strong>
                                <div class="muted" style="margin-top: 0.2rem;">{{ $enrollment->email }}</div>
                            </td>
                            <td>
                                <div>{{ $enrollment->program }}</div>
                                <div class="muted" style="margin-top: 0.2rem;">{{ $enrollment->period }} ·
                                    {{ $enrollment->campus ?: trim(explode(' - ', (string) $enrollment->campus_schedule, 2)[0] ?? '') }}
                                    ·
                                    {{ $enrollment->jornada ?: trim(explode(' - ', (string) $enrollment->campus_schedule, 2)[1] ?? '') }}
                                </div>
                            </td>
                            <td class="hide-sm">{{ $enrollment->document_type }}<br>{{ $enrollment->document_number }}
                            </td>
                            <td class="hide-md">{{ $enrollment->mobile }}</td>
                            <td class="hide-lg">
                                {{ optional($enrollment->residenceDepartment)->name ?: 'Sin departamento' }}</td>
                            <td class="hide-lg">
                                {{ optional($enrollment->residenceMunicipality)->name ?: ($enrollment->residence_city ?: 'Sin municipio') }}
                            </td>
                            <td>
                                <span class="pill"
                                    style="background: {{ $enrollment->student_status === 'activo' ? '#dcfce7' : '#fee2e2' }}; color: {{ $enrollment->student_status === 'activo' ? '#166534' : '#991b1b' }};">
                                    {{ ucfirst($enrollment->student_status) }}
                                </span>
                            </td>
                            <td>
                                @if ($enrollment->paid_inscription)
                                    <button class="btn btn-sm btn-sm-action btn-secondary" type="button" disabled>
                                        Pagado
                                    </button>
                                @else
                                    <form method="POST"
                                        action="{{ route('enrollments.payments.update', ['enrollment' => $enrollment->id] + request()->query()) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="paid_inscription" value="1">
                                        <button class="btn btn-sm btn-sm-action js-payment-btn"
                                            data-payment-label="inscripción" type="submit">
                                            Marcar pago
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td>
                                @if ($enrollment->paid_tuition)
                                    <button class="btn btn-sm btn-sm-action btn-secondary" type="button" disabled>
                                        Pagado
                                    </button>
                                @else
                                    <form method="POST"
                                        action="{{ route('enrollments.payments.update', ['enrollment' => $enrollment->id] + request()->query()) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="paid_tuition" value="1">
                                        <button class="btn btn-sm btn-sm-action js-payment-btn"
                                            data-payment-label="matrícula" type="submit">
                                            Marcar pago
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="muted" style="padding: 1rem;">No hay registros para los filtros
                                seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            if (typeof Swal === 'undefined') return;

            document.querySelectorAll('.js-payment-btn').forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();

                    var form = button.closest('form');
                    if (!form) return;

                    var label = button.dataset.paymentLabel || 'pago';

                    Swal.fire({
                        icon: 'question',
                        title: 'Confirmar acción',
                        text: '¿Deseas registrar el pago de ' + label + '?',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, registrar pago',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        focusCancel: true
                    }).then(function(result) {
                        if (!result.isConfirmed) {
                            return;
                        }

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: 'Procesando pago de ' + label + '...',
                            showConfirmButton: false,
                            timer: 900,
                            timerProgressBar: true
                        });

                        form.submit();
                    });
                });
            });
        })();
    </script>
@endpush
