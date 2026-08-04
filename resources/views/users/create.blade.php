@extends('layouts.app')

@section('title', 'Crear usuario')
@section('badge', 'Nuevo')

@section('content')
    <div style="max-width: 560px;">
        <h1 style="margin-bottom: 0.3rem;">Crear usuario</h1>
        <p class="muted" style="margin-top: 0;">Completa los datos para registrar un nuevo usuario con rol asignado.</p>

        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="field-grid">
                <div class="field">
                    <label>Nombre</label>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="field-grid">
                <div class="field">
                    <label>Contraseña</label>
                    <input type="password" name="password" required>
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="field">
                    <label>Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" required>
                </div>
            </div>
            <div class="field">
                <label>Rol</label>
                <select name="role">
                    <option value="mercadeo">Mercadeo</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div class="actions-row" style="margin-top: 1rem;">
                <button class="btn btn-sm btn-sm-nav" type="submit">Guardar</button>
                <a class="btn btn-sm btn-secondary btn-sm-nav" href="{{ route('users.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
