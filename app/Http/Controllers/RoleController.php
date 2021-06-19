<?php
//done
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Models\Role;
use App\Http\Resources\Role\RoleCollection;
use App\Http\Resources\Role\RoleResource;
use App\Http\Resources\User\UserCollection;

class RoleController extends Controller
{
    /**
     * Return all roles.
     */
    public function index() {
        $roles = Role::all();
        return new RoleCollection($roles);
    }

    public function showRole($name) {
        try {
            $role = Role::where('role', $name)->firstOrFail();
            return new RoleResource($role);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Role with name '.$name.' not found.'], 404);
        }
    }

    public function showUsersWithRole($name) {
        try {
            $role = Role::where('role', $name)->firstOrFail();
            $users = $role->users;
            return new UserCollection($users);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Role with name '.$name.' not found.'], 404);
        }
    }
}
