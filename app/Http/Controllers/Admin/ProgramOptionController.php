<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\ProgramOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramOptionController extends Controller
{
    public function index(): View
    {
        return view('admin.options.index', [
            'title' => 'Programas',
            'description' => 'Administra los programas disponibles en el formulario público.',
            'routePrefix' => 'admin.program-options',
            'items' => ProgramOption::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.options.form', [
            'title' => 'Nuevo programa',
            'description' => 'Crea una opción de programa para el selector del formulario.',
            'routePrefix' => 'admin.program-options',
            'item' => new ProgramOption(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:program_options,name'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ProgramOption::create([
            'name' => trim($data['name']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.program-options.index')->with('success', 'Programa creado correctamente.');
    }

    public function edit(ProgramOption $programOption): View
    {
        return view('admin.options.form', [
            'title' => 'Editar programa',
            'description' => 'Actualiza una opción de programa del formulario.',
            'routePrefix' => 'admin.program-options',
            'item' => $programOption,
        ]);
    }

    public function update(Request $request, ProgramOption $programOption): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:program_options,name,' . $programOption->id],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $programOption->update([
            'name' => trim($data['name']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.program-options.index')->with('success', 'Programa actualizado correctamente.');
    }

    public function destroy(ProgramOption $programOption): RedirectResponse
    {
        if (Enrollment::where('program', $programOption->name)->exists()) {
            return back()->with('error', 'No se puede eliminar este programa porque ya está en uso en inscripciones.');
        }

        $programOption->delete();

        return redirect()->route('admin.program-options.index')->with('success', 'Programa eliminado correctamente.');
    }
}
