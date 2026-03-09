<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function index()
    {
        $roles = Role::with('permissions')->orderByDesc('id')->paginate(15);
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|alpha_dash|unique:roles,name',
            'label' => 'nullable|string|max:255',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name'=>$data['name'],'label'=>$data['label'] ?? null]);
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('roles.index')->with('success','Role created.');
    }

    public function show(Role $role)
    {
        $role->load('permissions','users');
        return view('roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $role->load('permissions');
        return view('roles.edit', compact('role','permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => "required|alpha_dash|unique:roles,name,{$role->id}",
            'label' => 'nullable|string|max:255',
            'permissions' => 'array'
        ]);

        $role->update(['name'=>$data['name'],'label'=>$data['label'] ?? null]);
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('roles.index')->with('success','Role updated.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success','Role deleted.');
    }
}
