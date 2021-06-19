<?php
//done
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Machine;
use App\Models\System;
use App\Http\Resources\System\SystemResource;
use App\Http\Resources\System\SystemCollection;
use App\Models\Version;

class SystemController extends Controller
{
    public function index(Request $request) {
        $systems = System::all();
        return new SystemCollection($systems);
    }

    public function showSystem($id, Request $request) {
        try {
            $system = System::where('id', $id)->firstOrFail();
            return new SystemResource($system);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'System with id '.$id.' not found.'], 404);
        }
    }

    /**
     * Creates a new record.
     */
    public function createSystem(Request $request) {
        $validator = Validator::make($request->all(), [
            'machineName' => 'required',
            'globalPrefix' => 'required',
            'owners' => 'required',
            'url' => 'required',
            'usernames' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $username = auth()->user()->username;
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            try {
                $machine = Machine::where('name', $request->input('machineName'))->firstOrFail();

                $system = new System();
                $system->global_prefix = $request->input('globalPrefix');
                $system->description = $request->input('description');
                $system->owners = $request->input('owners');
                $system->url = $request->input('url');
                $system->usernames = $request->input('usernames');
                $system->password = $request->input('password');
                $system->created_by = $username;
                $system->last_modified_by = $username;

                $version = new Version();
                $version->name = $request->input('versionName');
                $version->release_date = $request->input('releaseDate');

                DB::transaction(function () use ($machine, $system, $version){
                    $machine->systems()->save($system);
                    $system->refresh();
                    $system->versions()->save($version);
                    $version->refresh();
                });

                return new SystemResource($system);
            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Machine with name '.$request->input('machineName').' does not exist.'], 404);
            } catch (\Illuminate\Database\QueryException $e) {
                if(config('properties.db_connection') == 'pgsql') {
                    $errorCode = $e->errorInfo[0];
                    if ($errorCode == 23505) {
                        return response()->json(['error' => 'A duplicate system or version already exists either in the live or trash records.'], 500);
                    } else {
                        return response()->json(['error' => 'Something\'s wrong. Please contact the database admin to investigate.']);
                    }
                } else {
                    return response()->json(['error' => 'A database error occurred. This may be because a '.
                        'duplicate system or version already exists either in the live or trash records.'], 500);
                }
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }

    /**
     * Edits an existing record. A non-blank 'name' key
     * is required to execute the function.
     */
    public function editSystem($id, Request $request) {
        $validator = Validator::make($request->all(), [
            'machineName' => 'required',
            'globalPrefix' => 'required',
            'owners' => 'required',
            'url' => 'required',
            'usernames' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $username = auth()->user()->username;
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            try {
                $machine = Machine::where('name', $request->input('machineName'))->firstOrFail();

                $system = System::where('id', $id)->firstOrFail();
                $system->global_prefix = $request->input('globalPrefix');
                $system->description = $request->input('description');
                $system->owners = $request->input('owners');
                $system->url = $request->input('url');
                $system->usernames = $request->input('usernames');
                $system->password = $request->input('password');
                $system->last_modified_by = $username;

                DB::transaction(function () use ($machine, $system){
                    $machine->systems()->save($system);
                    $system->refresh();
                });

                return new SystemResource($system);
            } catch (ModelNotFoundException $e) {
                $model = $e->getModel();

                if ($model == 'App\Models\Machine')
                return response()->json(['error' => 'Machine with name '.$request->input('machineName').' not found.'], 404);

                if ($model == 'App\Models\System')
                return response()->json(['error' => 'System with id '.$id.' not found.'], 404);
            } catch (\Illuminate\Database\QueryException $e) {
                if(config('properties.db_connection') == 'pgsql') {
                    $errorCode = $e->errorInfo[0];
                    if ($errorCode == 23505)
                    return response()->json(['error' => 'A system with the same name on the target machine already exists '.
                        'either on the live or trashed records.'], 500);
                } else {
                    return response()->json(['error' => 'A database error occurred. This may be because a '.
                        'system with the same name on the target machine already exists either on the live or trashed records.'], 500);
                }
                Log::error($e);
                return response()->json(['error' => 'An unknown error occured. Please contact the system admins to investigate.'], 500);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }

    public function deleteSystem($id, Request $request) {
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            try {
                $system = System::where('id', $id)->firstOrFail();

                if (count($system->events()->get()) > 0)
                return response()->json(['error' => 'System has dependent events and cannot be deleted.'], 500);

                if (count($system->zones()->get()) > 0)
                return response()->json(['error' => 'System has dependent zones and cannot be deleted.'], 500);

                DB::transaction(function () use ($system){
                    $versions = $system->versions()->delete();
                    $system->delete();
                });

                return response()->json(['success' => 'System with id '.$id.' has been moved to trash.'], 200);
            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'System with id '.$id.' not found.'], 404);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }

    public function changeSystemPassword() {

    }
}
