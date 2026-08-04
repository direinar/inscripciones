@extends('layouts.app')

@section('title', 'Editar usuario')
@section('badge', 'Editar')

@section('content')
    <div style="max-width: 560px;">
        <h1 style="margin-bottom: 0.3rem;">Editar usuario</h1>
        <p class="muted" style="margin-top: 0;">Actualiza los datos del usuario y su rol de acceso.</p>

        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')
            <div class="field-grid">
                <div class="field">
                    <label>Nombre</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="field-grid">
                <div class="field">
                    <label>Nueva contraseña</label>
                    <input type="password" name="password">
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="field">
                    <label>Confirmar contraseña</label>
                    <input type="password" name="password_confirmation">
                </div>
            </div>
            <div class="field">
                <label>Rol</label>
                <select name="role">
                    <option value="mercadeo" {{ old('role', $user->role) === 'mercadeo' ? 'selected' : '' }}>Mercadeo
                    </option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrador
                    </option>
                </select>
            </div>
            <div class="actions-row" style="margin-top: 1rem;">
                <button class="btn btn-sm btn-sm-nav" type="submit">Actualizar</button>
                <a class="btn btn-sm btn-secondary btn-sm-nav" href="{{ route('users.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
