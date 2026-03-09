<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Company;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    public function index()
    {
        $users = User::paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $companies = Company::all();

        $adminRoleId = $roles->firstWhere('name', 'admin')?->id ?? 1;

        return view('users.create', [
            'roles' => $roles,
            'companies' => $companies,
            'adminRoleId' => $adminRoleId,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'roles' => 'required|array',
            'is_sr' => 'nullable|boolean',
            'is_elite' => 'nullable|boolean',
            'position' => 'required|string',
            'department' => 'required|string',
        ]);

        // Create the user
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_sr' => $request->has('is_sr'),
            'is_elite' => $request->has('is_elite'),
            'position' => $request->position,
            'department' => $request->department,
        ]);

        // Attach roles
        $user->roles()->sync($request->roles);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }


    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $companies = Company::all();

        $adminRoleId = $roles->where('name', 'admin')->first()->id ?? 1;

        return view('users.edit', [
            'user' => $user,
            'roles' => $roles,
            'companies' => $companies,
            'adminRoleId' => $adminRoleId
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email'=> 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'roles' => 'required|array',
            'is_sr' => 'nullable|boolean',
            'is_elite' => 'nullable|boolean',
            'position' => 'required|string',
            'department' => 'required|string',
        ]);

        $data['is_sr'] = $request->has('is_sr') ? 1 : 0;
        $data['is_elite'] = $request->has('is_elite') ? 1 : 0;

        if(!empty($data['password'])){
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if(!empty($data['password'])){
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('users.index')->with('success', 'User updated with role.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted.');
    }
}
