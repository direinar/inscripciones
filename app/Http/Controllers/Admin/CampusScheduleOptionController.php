<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusScheduleOption;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampusScheduleOptionController extends Controller
{
    public function index(): View
    {
        return view('admin.options.index', [
            'title' => 'Sedes y jornadas',
            'description' => 'Administra las sedes-jornada disponibles en el formulario público.',
            'routePrefix' => 'admin.campus-schedule-options',
            'items' => CampusScheduleOption::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.options.form', [
            'title' => 'Nueva sede-jornada',
            'description' => 'Crea una opción de sede-jornada para el selector del formulario.',
            'routePrefix' => 'admin.campus-schedule-options',
            'item' => new CampusScheduleOption(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:campus_schedule_options,name'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        CampusScheduleOption::create([
            'name' => trim($data['name']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.campus-schedule-options.index')->with('success', 'Sede-jornada creada correctamente.');
    }

    public function edit(CampusScheduleOption $campusScheduleOption): View
    {
        return view('admin.options.form', [
            'title' => 'Editar sede-jornada',
            'description' => 'Actualiza una opción de sede-jornada del formulario.',
            'routePrefix' => 'admin.campus-schedule-options',
            'item' => $campusScheduleOption,
        ]);
    }

    public function update(Request $request, CampusScheduleOption $campusScheduleOption): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:campus_schedule_options,name,' . $campusScheduleOption->id],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $campusScheduleOption->update([
            'name' => trim($data['name']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.campus-schedule-options.index')->with('success', 'Sede-jornada actualizada correctamente.');
    }

    public function destroy(CampusScheduleOption $campusScheduleOption): RedirectResponse
    {
        if (Enrollment::where('campus_schedule', $campusScheduleOption->name)->exists()) {
            return back()->with('error', 'No se puede eliminar esta sede-jornada porque ya está en uso en inscripciones.');
        }

        $campusScheduleOption->delete();

        return redirect()->route('admin.campus-schedule-options.index')->with('success', 'Sede-jornada eliminada correctamente.');
    }
}
