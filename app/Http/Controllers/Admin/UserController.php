<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:Administrador']);
    }

    public function index()
    {
        $users = User::with(['department','roles'])->orderBy('name')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get(['id','name']);
        $roles = Role::orderBy('name')->get(['id','name']); // (Administrador, Rector, Encargado de departamento, Usuario)
        return view('admin.users.create', compact('departments','roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => ['required','string','max:255'],
            'email'          => ['required','email','max:255','unique:users,email'],
            'password'       => ['required','string','min:6'],
            'department_id'  => ['nullable','exists:departments,id'],
            'role'           => ['required','string','exists:roles,name'],
        ]);

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'department_id' => $request->department_id,
        ]);

        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users.index')->with('success','Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $departments = Department::orderBy('name')->get(['id','name']);
        $roles = Role::orderBy('name')->get(['id','name']);
        $currentRole = $user->roles()->pluck('name')->first(); // uno principal para el select
        return view('admin.users.edit', compact('user','departments','roles','currentRole'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'           => ['required','string','max:255'],
            'email'          => ['required','email','max:255',"unique:users,email,{$user->id}"],
            'password'       => ['nullable','string','min:6'],
            'department_id'  => ['nullable','exists:departments,id'],
            'role'           => ['required','string','exists:roles,name'],
        ]);

        $data = [
            'name'          => $request->name,
            'email'         => $request->email,
            'department_id' => $request->department_id,
        ];
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users.index')->with('success','Usuario actualizado.');
    }
}
