<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:Administrador']);
    }

    public function index(Request $request)
    {
        $q = Department::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $s = $request->string('search');
                $query->where(function ($q2) use ($s) {
                    $q2->where('name','like',"%{$s}%")
                       ->orWhere('code','like',"%{$s}%")
                       ->orWhere('slug','like',"%{$s}%");
                });
            })
            ->orderBy('name');

        $departments = $q->paginate(10)->withQueryString();
        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(StoreDepartmentRequest $request)
    {
        Department::create($request->validated());
        return redirect()->route('admin.departments.index')->with('success','Departamento creado correctamente.');
    }

    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $department->update($request->validated());
        return redirect()->route('admin.departments.index')->with('success','Departamento actualizado.');
    }

    public function destroy(Department $department)
    {
        // Evitar borrar si hay usuarios (opcional, puedes ajustar)
        if ($department->users()->exists()) {
            return back()->with('error','No puedes eliminar un departamento con usuarios asignados.');
        }

        $department->delete();
        return redirect()->route('admin.departments.index')->with('success','Departamento eliminado.');
    }

    // Alternar activo/inactivo (opcional)
    public function toggle(Department $department)
    {
        $department->update(['is_active' => ! $department->is_active]);
        return back()->with('success','Estado actualizado.');
    }
}
