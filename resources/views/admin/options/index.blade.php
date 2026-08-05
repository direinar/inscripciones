@extends('layouts.app')

@section('title', $title)
@section('badge', 'Catálogos')

@section('content')
    <div class="grid" style="gap: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div>
                <h1 style="margin-bottom: 0.3rem;">{{ $title }}</h1>
                <p class="muted" style="margin: 0;">{{ $description }}</p>
            </div>
            <div class="actions-row">
                <a class="btn btn-sm btn-sm-nav" href="{{ route($routePrefix . '.create') }}">Nuevo registro</a>
                <a class="btn btn-sm btn-secondary btn-sm-nav" href="{{ route('admin') }}">Volver</a>
            </div>
        </div>

        @if (session('success'))
            <div class="flash success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="flash error">{{ session('error') }}</div>
        @endif

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 55%;">Nombre</th>
                        <th style="width: 15%;">Orden</th>
                        <th style="width: 15%;">Estado</th>
                        <th style="width: 15%;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->sort_order }}</td>
                            <td>
                                <span class="pill"
                                    style="background: {{ $item->is_active ? '#dcfce7' : '#fee2e2' }}; color: {{ $item->is_active ? '#166534' : '#991b1b' }};">
                                    {{ $item->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <div class="actions-row" style="gap: .5rem;">
                                    <a class="btn btn-sm btn-sm-action"
                                        href="{{ route($routePrefix . '.edit', $item) }}">Editar</a>
                                    <form action="{{ route($routePrefix . '.destroy', $item) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger btn-sm-action" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="muted">No hay registros aún.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
