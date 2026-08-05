@extends('layouts.app')

@section('title', 'Dashboard')
@section('badge', 'Panel principal')

@section('content')
    <div class="grid" style="gap: 1.2rem;">
        <div class="grid grid-2">
            <div style="padding: 1rem 0;">
                <div class="pill">Bienvenido</div>
                <h1 style="margin: 0.6rem 0 0.4rem;">Hola, {{ Auth::user()->name }}</h1>
                <p class="muted" style="margin: 0;">Tu rol actual es <strong>{{ Auth::user()->role }}</strong>. Desde aquí
                    puedes administrar el sistema y consultar la información principal.</p>
            </div>
            <div class="actions-row" style="justify-content: flex-end; align-items: center;">
                <a class="btn btn-sm btn-sm-nav" href="{{ route('enrollments.index') }}">Inscripciones</a>
                @if (Auth::user()->isAdmin())
                    <a class="btn btn-sm btn-sm-nav" href="{{ route('users.index') }}">Usuarios</a>
                    <a class="btn btn-sm btn-sm-nav" href="{{ route('admin.period-options.index') }}">Catálogos</a>
                    <a class="btn btn-sm btn-ghost btn-sm-nav" href="{{ route('admin') }}">Panel admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button class="btn btn-sm btn-secondary btn-sm-nav" type="submit">Cerrar sesión</button>
                </form>
            </div>
        </div>

        <div class="grid grid-2">
            <a class="card" href="{{ route('enrollments.index') }}"
                style="text-decoration: none; color: inherit; padding: 1.25rem;">
                <h3 style="margin-bottom: 0.35rem;">Reporte de inscripciones</h3>
                <p class="muted" style="margin: 0;">Consulta los registros recibidos y exporta la información a CSV.</p>
            </a>

            <a class="card" href="{{ route('enrollments.create') }}" target="_blank"
                style="text-decoration: none; color: inherit; padding: 1.25rem;">
                <h3 style="margin-bottom: 0.35rem;">Formulario público</h3>
                <p class="muted" style="margin: 0;">Abre el formulario público que diligencian los aspirantes.</p>
            </a>
        </div>
    </div>
@endsection
