<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $access = $request->user()->roles->flatMap->permissions->pluck('name')->unique();
        $roles = Role::with('permissions:id,name')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->all(),
                ];
            });
        $perms = Permission::select('id', 'name')->get();
        $users = User::select('id', 'name')->with('roles:id,name')->get();

        return Inertia::render('Permissions', compact('access', 'roles', 'perms', 'users'));
    }
}
