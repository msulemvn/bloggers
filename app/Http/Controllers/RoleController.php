<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\RoleResource;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response as symfonyResponse;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::with(['permissions:id'])->paginate();
        $allPermissions = Permission::select('id', 'name')->get();

        $roles->getCollection()->transform(function ($role) use ($allPermissions) {
            $role->setRelation('permissions', $allPermissions); // Set the permissions to $allPermissions
            return $role;
        });

        // foreach($roles as $role){
        //     <input type="hidden" name="role[]" value="$role->id">
        //     foreach($allPermissions as $permission){
        //         <input type="checkbox" name="permissions[$role->id][]">
        //     }
        // }

        dd($roles->toArray());


        $roles = RoleResource::collection($roles);
        return Inertia::render('Roles', [
            'roles' => $roles,
            'rolePermissions' => $rolePermissions
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $permissions = $request->permissions;
        $role->name = $request->name;
        if ($permissions) {
            $collection = collect($permissions);
            $permissions = $collection->where('selected', true)->pluck('name')->toArray();
            $role->syncPermissions($permissions);
        }
        $role->save();

        logActivity(request: $request, description: "User updated roles", showable: false);
        return apiResponse(message: "Roles and permissions updated successfully");
    }

    public function store(Request $request)
    {
        $role = Role::create(['name' => $request->name]);
        logActivity(request: $request, description: "User created a new role", showable: true);
        return apiResponse(message: "Role created successfully", statusCode: symfonyResponse::HTTP_CREATED);
    }

    public function destroy(Request $request, Role $role)
    {

        logActivity(request: $request, description: "User deleted a post", showable: true);
        return apiResponse(message: "Post deleted successfully", statusCode: symfonyResponse::HTTP_NO_CONTENT);
    }
}
