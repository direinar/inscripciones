@extends('layouts.app')

@section('title', $title)
@section('badge', 'Catálogos')

@section('content')
    <div style="max-width: 620px;">
        <h1 style="margin-bottom: 0.3rem;">{{ $title }}</h1>
        <p class="muted" style="margin-top: 0;">{{ $description }}</p>

        <form method="POST"
            action="{{ $item->exists ? route($routePrefix . '.update', $item) : route($routePrefix . '.store') }}">
            @csrf
            @if ($item->exists)
                @method('PUT')
            @endif

            <div class="field">
                <label for="name">Nombre</label>
                <input id="name" type="text" name="name" value="{{ old('name', $item->name) }}" maxlength="150"
                    required>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field-grid">
                <div class="field">
                    <label for="sort_order">Orden</label>
                    <input id="sort_order" type="number" min="0" max="9999" name="sort_order"
                        value="{{ old('sort_order', $item->sort_order ?? 0) }}" required>
                    @error('sort_order')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="field" style="display: flex; flex-direction: column; justify-content: flex-end;">
                    <label for="is_active" style="margin-bottom: 0; display: inline-flex; align-items: center; gap: .5rem;">
                        <input id="is_active" type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $item->is_active) ? 'checked' : '' }}
                            style="width: 16px; min-height: auto;">
                        Activo en formulario público
                    </label>
                </div>
            </div>

            <div class="actions-row" style="margin-top: 1rem;">
                <button class="btn btn-sm btn-sm-nav" type="submit">{{ $item->exists ? 'Actualizar' : 'Guardar' }}</button>
                <a class="btn btn-sm btn-secondary btn-sm-nav" href="{{ route($routePrefix . '.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
