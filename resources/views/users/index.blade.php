@extends('layouts.app')

@section('title', 'Gestión de usuarios')
@section('badge', 'Usuarios')

@section('content')
    <div class="grid" style="gap: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div>
                <h1 style="margin-bottom: 0.3rem;">Gestión de usuarios</h1>
                <p class="muted" style="margin: 0;">Administra los accesos al sistema y los roles asignados.</p>
            </div>
            <div class="actions-row">
                <a class="btn btn-sm btn-sm-nav" href="{{ route('users.create') }}">Nuevo usuario</a>
                <a class="btn btn-sm btn-secondary btn-sm-nav" href="{{ route('dashboard') }}">Volver</a>
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
                        <th class="col-name">Nombre</th>
                        <th class="col-email">Email</th>
                        <th class="col-role">Rol</th>
                        <th class="col-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="pill">{{ $user->role }}</span></td>
                            <td>
                                <div class="actions-row" style="gap: .5rem;">
                                    <a class="btn btn-sm btn-sm-action" href="{{ route('users.edit', $user) }}">Editar</a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger btn-sm-action" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
