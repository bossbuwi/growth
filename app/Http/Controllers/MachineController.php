<?php
//done
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use App\Models\Machine;
use App\Http\Resources\Machine\MachineResource;
use App\Http\Resources\Machine\MachineCollection;
use App\Http\Resources\System\SystemCollection;

class MachineController extends Controller
{
    public function index(Request $request) {
        $machines = Machine::all();
        return new MachineCollection($machines);
    }

    /**
     * Return a specific record.
     */
    public function showMachine($name, Request $request) {
        try {
            $machine = Machine::where('name', $name)->firstOrFail();
            return new MachineResource($machine);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Machine with name '.$name.' not found.'], 404);
        }
    }

    public function showSystemsOnMachine($name, Request $request) {
        try {
            $machine = Machine::where('name', $name)->firstOrFail();
            $systems = $machine->systems;
            return new SystemCollection($systems);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Machine with name '.$name.' not found.'], 404);
        }
    }

    /**
     * Creates a new record. A non-blank 'name' key is required to
     * execute the function.
     */
    public function createMachine(Request $request) {
        $username = auth()->user()->username;
        $superuser = auth()->user()->role->superuser;

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($superuser == true) {
            try {
                $machine = new Machine();
                $machine->name = $request->input('name');
                $machine->created_by = $username;
                $machine->last_modified_by = $username;
                if ($machine->save()) {
                    return new MachineResource($machine);
                } else {
                    return response()->json(['error' => 'Changes not saved. Please try again later.'], 500);
                }
            } catch (\Illuminate\Database\QueryException $e) {
                if(config('properties.db_connection') == 'pgsql') {
                    $errorCode = $e->errorInfo[0];
                    if ($errorCode == 23505)
                    return response()->json(['error' => 'A machine with name '.$request->input('name').' already exists '.
                        'either in the live or trashed records.'], 500);
                } else {
                    return response()->json(['error' => 'A database error occurred. This may be because a '.
                        'machine with name '.$request->input('name').' already exists either on the live or trashed records.'], 500);
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
    public function editMachine($name, Request $request) {
        $username = auth()->user()->username;
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            if ($request->filled('name')) {
                try {
                    $machine = Machine::where('name', $name)->firstOrFail();
                    $machine->name = $request->input('name');
                    $machine->last_modified_by = $username;

                    if ($name == $request->input('name')) {
                        return response()->json(['error' => 'The machine\'s new name is same as the old name.'], 500);
                    }

                    if ($machine->save()) {
                        return new MachineResource($machine);
                    } else {
                        return response()->json(['error' => 'Changes not saved. Please try again later.'], 500);
                    }
                } catch (ModelNotFoundException $e) {
                    return response()->json(['error' => 'Machine with name '.$name.' not found.'], 404);
                } catch (\Illuminate\Database\QueryException $e) {
                    if(config('properties.db_connection') == 'pgsql') {
                        $errorCode = $e->errorInfo[0];
                        if ($errorCode == 23505)
                        return response()->json(['error' => 'The new machine name already exists either on the live or trashed records.'], 500);
                    } else {
                        return response()->json(['error' => 'A database error occurred. This may be because the '.
                            'new machine name already exists either on the live or trashed records.'], 500);
                    }
                }
            } else {
                return response()->json(['error' => 'Mandatory name key not found.'], 400);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }

    public function deleteMachine($name, Request $request) {
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            try {
                $machine = Machine::where('name', $name)->firstOrFail();

                if (count($machine->systems()->get()) > 0)
                return response()->json(['error' => 'Machine with name '.$name.' has existing dependencies and cannot be deleted.'], 500);

                $machine->delete();
                return response()->json(['success' => 'Machine with name '.$name.' deleted permanently.'], 200);

            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Machine with name '.$name.' not found.'], 404);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }
}
