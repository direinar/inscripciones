@extends('layouts.app')

@section('title', 'Panel administrativo')
@section('badge', 'Administración')

@section('content')
    <div class="grid" style="gap: 1.2rem;">
        <div>
            <div class="pill">Administrador</div>
            <h1 style="margin: 0.7rem 0 0.35rem;">Panel administrativo</h1>
            <p class="muted" style="margin: 0;">Acceso concedido para {{ Auth::user()->name }}. Desde este espacio puedes
                supervisar la operación y acceder a la gestión del sistema.</p>
        </div>

        <div class="grid grid-2">
            <a class="card" href="{{ route('users.index') }}"
                style="text-decoration: none; color: inherit; padding: 1.25rem;">
                <h3 style="margin-bottom: 0.35rem;">Gestión de usuarios</h3>
                <p class="muted" style="margin: 0;">Crea, edita y controla los roles de acceso.</p>
            </a>

            <a class="card" href="{{ route('enrollments.index') }}"
                style="text-decoration: none; color: inherit; padding: 1.25rem;">
                <h3 style="margin-bottom: 0.35rem;">Reporte de inscripciones</h3>
                <p class="muted" style="margin: 0;">Revisa y exporta los registros enviados desde el formulario público.
                </p>
            </a>

            <a class="card" href="{{ route('admin.period-options.index') }}"
                style="text-decoration: none; color: inherit; padding: 1.25rem;">
                <h3 style="margin-bottom: 0.35rem;">Catálogo de periodos</h3>
                <p class="muted" style="margin: 0;">Crea y edita las opciones de periodo del formulario.</p>
            </a>

            <a class="card" href="{{ route('admin.campus-schedule-options.index') }}"
                style="text-decoration: none; color: inherit; padding: 1.25rem;">
                <h3 style="margin-bottom: 0.35rem;">Catálogo sede-jornada</h3>
                <p class="muted" style="margin: 0;">Gestiona las opciones de sede y jornada del formulario.</p>
            </a>

            <a class="card" href="{{ route('admin.program-options.index') }}"
                style="text-decoration: none; color: inherit; padding: 1.25rem;">
                <h3 style="margin-bottom: 0.35rem;">Catálogo de programas</h3>
                <p class="muted" style="margin: 0;">Administra los programas disponibles para inscripción.</p>
            </a>

            <a class="card" href="{{ route('dashboard') }}"
                style="text-decoration: none; color: inherit; padding: 1.25rem;">
                <h3 style="margin-bottom: 0.35rem;">Volver al dashboard</h3>
                <p class="muted" style="margin: 0;">Regresa al panel principal con el resumen general.</p>
            </a>
        </div>
    </div>
@endsection
