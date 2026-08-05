<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\JornadaOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JornadaOptionController extends Controller
{
    public function index(): View
    {
        return view('admin.options.index', [
            'title' => 'Jornadas',
            'description' => 'Administra las jornadas disponibles en el formulario público.',
            'routePrefix' => 'admin.jornada-options',
            'items' => JornadaOption::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.options.form', [
            'title' => 'Nueva jornada',
            'description' => 'Crea una jornada para el selector del formulario.',
            'routePrefix' => 'admin.jornada-options',
            'item' => new JornadaOption(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:jornada_options,name'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        JornadaOption::create([
            'name' => trim($data['name']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.jornada-options.index')->with('success', 'Jornada creada correctamente.');
    }

    public function edit(JornadaOption $jornadaOption): View
    {
        return view('admin.options.form', [
            'title' => 'Editar jornada',
            'description' => 'Actualiza una jornada del formulario.',
            'routePrefix' => 'admin.jornada-options',
            'item' => $jornadaOption,
        ]);
    }

    public function update(Request $request, JornadaOption $jornadaOption): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:jornada_options,name,' . $jornadaOption->id],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $jornadaOption->update([
            'name' => trim($data['name']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.jornada-options.index')->with('success', 'Jornada actualizada correctamente.');
    }

    public function destroy(JornadaOption $jornadaOption): RedirectResponse
    {
        if (Enrollment::where('jornada', $jornadaOption->name)->exists()) {
            return back()->with('error', 'No se puede eliminar esta jornada porque ya está en uso en inscripciones.');
        }

        $jornadaOption->delete();

        return redirect()->route('admin.jornada-options.index')->with('success', 'Jornada eliminada correctamente.');
    }
}
