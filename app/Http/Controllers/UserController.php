<?php
//done
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Role;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;

class UserController extends Controller
{
    /**
     * Returns all user records.
     */
    public function index() {
        $users = User::all();
        return new UserCollection($users);
    }

    /**
     * Returns the details of a specific user.
     */
    public function getUser($username) {
        try {
            $user = User::where('username', $username)->firstOrFail();
            return new UserResource($user);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found.'], 404);
        }
    }

    /**
     * Modify a user's role.
     */
    public function modifyRole($username, Request $request) {
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            if ($request->filled('role')) {
                try {
                    $role = Role::where('role', $request->input('role'))->firstOrFail();
                    $user = User::where('username', $username)->firstOrFail();
                    Log::error($user);
                    if (!$role->superuser && !$user->role->superuser) {
                        if ($role->users()->save($user)) {
                            $user->refresh();
                            return new UserResource($user);
                        } else {
                            return response()->json(['error' => 'Changes not saved. Please try again later.'], 500);
                        }
                    } else {
                        return response()->json(['error' => 'SuperUser role cannot be modified through the REST endpoint.'], 403);
                    }
                } catch (ModelNotFoundException $e) {
                    return response()->json(['error' => 'User or role not found.'], 404);
                }
            } else {
                return response()->json(['error' => 'Mandatory role key not found.'], 400);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }
}
