<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\PeriodOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeriodOptionController extends Controller
{
    public function index(): View
    {
        return view('admin.options.index', [
            'title' => 'Periodos',
            'description' => 'Administra los periodos disponibles en el formulario público.',
            'routePrefix' => 'admin.period-options',
            'items' => PeriodOption::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.options.form', [
            'title' => 'Nuevo periodo',
            'description' => 'Crea una opción de periodo para el selector del formulario.',
            'routePrefix' => 'admin.period-options',
            'item' => new PeriodOption(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:period_options,name'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        PeriodOption::create([
            'name' => trim($data['name']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.period-options.index')->with('success', 'Periodo creado correctamente.');
    }

    public function edit(PeriodOption $periodOption): View
    {
        return view('admin.options.form', [
            'title' => 'Editar periodo',
            'description' => 'Actualiza una opción de periodo del formulario.',
            'routePrefix' => 'admin.period-options',
            'item' => $periodOption,
        ]);
    }

    public function update(Request $request, PeriodOption $periodOption): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:period_options,name,' . $periodOption->id],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $periodOption->update([
            'name' => trim($data['name']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.period-options.index')->with('success', 'Periodo actualizado correctamente.');
    }

    public function destroy(PeriodOption $periodOption): RedirectResponse
    {
        if (Enrollment::where('period', $periodOption->name)->exists()) {
            return back()->with('error', 'No se puede eliminar este periodo porque ya está en uso en inscripciones.');
        }

        $periodOption->delete();

        return redirect()->route('admin.period-options.index')->with('success', 'Periodo eliminado correctamente.');
    }
}
