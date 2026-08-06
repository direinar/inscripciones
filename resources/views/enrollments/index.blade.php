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
                <div class="muted" style="font-size: 0.85rem;">Retirados</div>
                <div style="font-size: 1.8rem; font-weight: 800; margin-top: 0.35rem;">{{ $summary['retired'] }}</div>
            </div>
            <div class="card" style="padding: 1rem 1.1rem;">
                <div class="muted" style="font-size: 0.85rem;">Pagos inscripción</div>
                <div style="font-size: 1.2rem; font-weight: 800; margin-top: 0.35rem;">
                    ${{ number_format($summary['inscription_paid'], 0, ',', '.') }}</div>
            </div>
            <div class="card" style="padding: 1rem 1.1rem;">
                <div class="muted" style="font-size: 0.85rem;">Pagos matrícula</div>
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
                    <label for="payment_date_from">Fecha de pago desde</label>
                    <input id="payment_date_from" name="payment_date_from" type="date"
                        value="{{ request('payment_date_from') }}">
                </div>
                <div class="field">
                    <label for="payment_date_to">Fecha de pago hasta</label>
                    <input id="payment_date_to" name="payment_date_to" type="date"
                        value="{{ request('payment_date_to') }}">
                </div>
            </div>
            <div class="actions-row" style="margin-top: 1rem;">
                <button class="btn btn-sm btn-sm-nav" type="submit">Filtrar</button>
                <a class="btn btn-sm btn-secondary btn-sm-nav" href="{{ route('enrollments.index') }}">Limpiar</a>
            </div>
        </form>

        <div class="table-wrap">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Programa</th>
                        <th>Estado</th>
                        <th>Últimos movimientos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enrollments as $enrollment)
                        <tr>
                            <td>
                                <div class="person-block">
                                    <div class="person-name">
                                        {{ trim($enrollment->first_name . ' ' . $enrollment->middle_name . ' ' . $enrollment->last_name . ' ' . $enrollment->second_last_name) }}
                                    </div>
                                    <div class="person-meta">{{ $enrollment->email }}</div>
                                    <div class="person-meta">{{ $enrollment->document_type }}
                                        {{ $enrollment->document_number }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="program-block">
                                    <strong>{{ $enrollment->program }}</strong>
                                    <div class="muted" style="margin-top: 0.22rem;">{{ $enrollment->period }} ·
                                        {{ $enrollment->campus ?: trim(explode(' - ', (string) $enrollment->campus_schedule, 2)[0] ?? '') }}
                                        ·
                                        {{ $enrollment->jornada ?: trim(explode(' - ', (string) $enrollment->campus_schedule, 2)[1] ?? '') }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="pill"
                                    style="background: {{ $enrollment->student_status === 'matriculado' || $enrollment->student_status === 'inscrito' ? '#dcfce7' : ($enrollment->student_status === 'retirado' ? '#fef3c7' : '#fee2e2') }}; color: {{ $enrollment->student_status === 'matriculado' || $enrollment->student_status === 'inscrito' ? '#166534' : ($enrollment->student_status === 'retirado' ? '#92400e' : '#991b1b') }};">
                                    {{ ucfirst($enrollment->student_status) }}
                                </span>
                            </td>
                            <td>
                                <div class="movement-stack">
                                    <div class="movement-item">
                                        <span>Inscripción</span>
                                        <strong>{{ $enrollment->inscription_payment_date ? $enrollment->inscription_payment_date->format('d/m/Y') : 'Sin registro' }}</strong>
                                        <small>{{ $enrollment->inscription_amount_paid !== null ? '$' . number_format((float) $enrollment->inscription_amount_paid, 0, ',', '.') : 'Sin valor' }}</small>
                                    </div>
                                    <div class="movement-item">
                                        <span>Matrícula</span>
                                        <strong>{{ $enrollment->tuition_payment_date ? $enrollment->tuition_payment_date->format('d/m/Y') : 'Sin registro' }}</strong>
                                        <small>{{ $enrollment->tuition_amount_paid !== null ? '$' . number_format((float) $enrollment->tuition_amount_paid, 0, ',', '.') : 'Sin valor' }}</small>
                                    </div>
                                    <div class="movement-item">
                                        <span>Devolución</span>
                                        <strong>{{ $enrollment->refund_date ? $enrollment->refund_date->format('d/m/Y') : 'Sin registro' }}</strong>
                                        <small>{{ $enrollment->refund_amount !== null ? '$' . number_format((float) $enrollment->refund_amount, 0, ',', '.') : 'Sin valor' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="action-group">
                                    <button class="action-pill action-pill--inscription js-payment-trigger" type="button"
                                        data-action="inscription" data-label="inscripción"
                                        data-title="Registrar pago de inscripción"
                                        data-description="Completa la fecha y el valor para registrar el pago de inscripción."
                                        data-submit-label="Registrar pago"
                                        data-route="{{ route('enrollments.payments.update', ['enrollment' => $enrollment->id] + request()->query()) }}"
                                        data-date-name="inscription_payment_date"
                                        data-amount-name="inscription_amount_paid" data-flag-name="paid_inscription"
                                        data-default-date="{{ $enrollment->inscription_payment_date ? $enrollment->inscription_payment_date->toDateString() : now()->toDateString() }}"
                                        data-default-amount="{{ $enrollment->inscription_amount_paid !== null ? (string) $enrollment->inscription_amount_paid : '' }}">
                                        Inscripción
                                    </button>
                                    <button class="action-pill action-pill--tuition js-payment-trigger" type="button"
                                        data-action="tuition" data-label="matrícula"
                                        data-title="Registrar pago de matrícula"
                                        data-description="Completa la fecha y el valor para registrar el pago de matrícula."
                                        data-submit-label="Registrar pago"
                                        data-route="{{ route('enrollments.payments.update', ['enrollment' => $enrollment->id] + request()->query()) }}"
                                        data-date-name="tuition_payment_date" data-amount-name="tuition_amount_paid"
                                        data-flag-name="paid_tuition"
                                        data-default-date="{{ $enrollment->tuition_payment_date ? $enrollment->tuition_payment_date->toDateString() : now()->toDateString() }}"
                                        data-default-amount="{{ $enrollment->tuition_amount_paid !== null ? (string) $enrollment->tuition_amount_paid : '' }}">
                                        Matrícula
                                    </button>
                                    <button class="action-pill action-pill--refund js-payment-trigger" type="button"
                                        data-action="refund" data-label="devolución" data-title="Registrar devolución"
                                        data-description="Completa la fecha y el valor para registrar una devolución."
                                        data-submit-label="Registrar devolución"
                                        data-route="{{ route('enrollments.payments.update', ['enrollment' => $enrollment->id] + request()->query()) }}"
                                        data-date-name="refund_date" data-amount-name="refund_amount"
                                        data-default-date="{{ $enrollment->refund_date ? $enrollment->refund_date->toDateString() : now()->toDateString() }}"
                                        data-default-amount="{{ $enrollment->refund_amount !== null ? (string) $enrollment->refund_amount : '' }}">
                                        Devolución
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted" style="padding: 1rem;">No hay registros para los filtros
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

        <div class="payment-modal" id="paymentModal" aria-hidden="true">
            <div class="payment-modal__backdrop" data-close-modal></div>
            <div class="payment-modal__card" role="dialog" aria-modal="true" aria-labelledby="paymentModalTitle">
                <button class="payment-modal__close" type="button" data-close-modal aria-label="Cerrar modal">×</button>
                <div class="payment-modal__eyebrow">Acción rápida</div>
                <h3 id="paymentModalTitle">Registrar movimiento</h3>
                <p id="paymentModalDescription" class="muted">Completa los datos para continuar.</p>
                <form id="paymentModalForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="payment_flag" value="">
                    <div class="field-grid modal-grid">
                        <div class="field">
                            <label for="paymentModalDate">Fecha</label>
                            <input id="paymentModalDate" name="payment_date" type="date" required>
                        </div>
                        <div class="field">
                            <label for="paymentModalAmount">Valor</label>
                            <input id="paymentModalAmount" name="payment_amount" type="number" min="0"
                                step="0.01" placeholder="0.00" required>
                        </div>
                    </div>
                    <div id="paymentModalError" class="payment-modal__error" role="alert"></div>
                    <div class="actions-row actions-row--end">
                        <button class="btn btn-ghost" type="button" data-close-modal>Cancelar</button>
                        <button class="btn" id="paymentModalSubmit" type="submit">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            var modal = document.getElementById('paymentModal');
            var form = document.getElementById('paymentModalForm');
            var title = document.getElementById('paymentModalTitle');
            var description = document.getElementById('paymentModalDescription');
            var submit = document.getElementById('paymentModalSubmit');
            var dateInput = document.getElementById('paymentModalDate');
            var amountInput = document.getElementById('paymentModalAmount');
            var errorBox = document.getElementById('paymentModalError');
            var flagInput = form ? form.querySelector('input[name="payment_flag"]') : null;

            if (!modal || !form || !dateInput || !amountInput || !flagInput) return;

            function closeModal() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
                errorBox.textContent = '';
                form.reset();
            }

            document.querySelectorAll('.js-payment-trigger').forEach(function(button) {
                button.addEventListener('click', function() {
                    var action = button.dataset.action || 'inscription';
                    var label = button.dataset.label || 'movimiento';
                    var titleText = button.dataset.title || 'Registrar movimiento';
                    var descriptionText = button.dataset.description || 'Completa los datos.';
                    var submitLabel = button.dataset.submitLabel || 'Registrar';
                    var route = button.dataset.route || '';
                    var dateName = button.dataset.dateName || 'payment_date';
                    var amountName = button.dataset.amountName || 'payment_amount';
                    var flagName = button.dataset.flagName || '';
                    var defaultDate = button.dataset.defaultDate || '';
                    var defaultAmount = button.dataset.defaultAmount || '';

                    title.textContent = titleText;
                    description.textContent = descriptionText;
                    submit.textContent = submitLabel;
                    form.action = route;
                    dateInput.name = dateName;
                    amountInput.name = amountName;
                    dateInput.value = defaultDate;
                    amountInput.value = defaultAmount;

                    if (flagName) {
                        flagInput.name = flagName;
                        flagInput.value = '1';
                    } else {
                        flagInput.name = 'payment_flag';
                        flagInput.value = '';
                    }

                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('modal-open');
                    setTimeout(function() {
                        dateInput.focus();
                    }, 60);
                });
            });

            document.querySelectorAll('[data-close-modal]').forEach(function(element) {
                element.addEventListener('click', function(event) {
                    event.preventDefault();
                    closeModal();
                });
            });

            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            form.addEventListener('submit', function(event) {
                event.preventDefault();

                if (!dateInput.value || !amountInput.value) {
                    errorBox.textContent = 'Completa la fecha y el valor antes de continuar.';
                    return;
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'question',
                        title: 'Confirmar acción',
                        text: '¿Deseas registrar el movimiento de ' + (title.textContent ||
                            'este registro') + '?',
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
        })();
    </script>
@endpush
