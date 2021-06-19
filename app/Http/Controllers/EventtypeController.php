<?php
//needs to show event types based on role
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Eventtype;
use App\Http\Resources\Eventtype\EventtypeResource;
use App\Http\Resources\Eventtype\EventtypeCollection;
use App\Models\Role;

class EventtypeController extends Controller
{
    public function index() {
        $eventTypes = Eventtype::all();
        return new EventtypeCollection($eventTypes);
    }

    public function showEventType($eventCode) {
        try {
            $eventType = Eventtype::where('event_code', $eventCode)->firstOrFail();
            return new EventtypeResource($eventType);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Event type not found.'], 404);
        }
    }

    public function createEventType(Request $request) {
        $validator = Validator::make($request->all(), [
            'eventCode' => 'required',
            'name' => 'required',
            'exclusive' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $username = auth()->user()->username;
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            try {
                $eventType = new Eventtype();
                $eventType->event_code = $request->input('eventCode');
                $eventType->name = $request->input('name');
                $eventType->exclusive = $request->input('exclusive');
                $eventType->created_by = $username;
                $eventType->last_modified_by = $username;

                if ($eventType->save()) {
                    return new EventtypeResource($eventType);
                } else {
                    return response()->json(['error' => 'Event type not saved. Please try again later.'], 500);
                }
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

    public function editEventType($eventCode, Request $request) {
        $validator = Validator::make($request->all(), [
            'eventCode' => 'required',
            'name' => 'required',
            'exclusive' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $username = auth()->user()->username;
        $superuser = auth()->user()->role->superuser;

        if ($superuser) {
            try {
                $eventType = Eventtype::where('event_code', $eventCode)->firstOrFail();
                $eventType->event_code = $request->input('eventCode');
                $eventType->name = $request->input('name');
                $eventType->exclusive = $request->input('exclusive');
                $eventType->last_modified_by = $username;

                if ($eventType->save()) {
                    return new EventtypeResource($eventType);
                } else {
                    return response()->json(['error' => 'Event type not saved. Please try again later.'], 500);
                }
            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Not found.'], 404);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }

    public function deleteEventType($eventCode, Request $request) {
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            try {
                $eventType = Eventtype::where('event_code', $eventCode)->firstOrFail();

                if (count($eventType->events()->get()) > 0)
                return 'has dependencies';

                DB::transaction(function () use ($eventType){
                    $eventType->roles()->detach();
                    $eventType->delete();
                });

                return response()->json(['success' => 'Deleted.'], 200);
            } catch (\Throwable $th) {
                Log::error($th);
                return response()->json(['error' => 'Something\'s wrong.'], 500);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }

    public function assignRoleToEventType($eventCode, $role) {
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            try {
                $eventType = Eventtype::where('event_code', $eventCode)->firstOrFail();
                $targetRole = Role::where('role', $role)->firstOrFail();
                $eventType->roles()->attach($targetRole->id);
                return response()->json(['success' => 'Role added to event type.'], 200);
            } catch (ModelNotFoundException $e) {
                $model = $e->getModel();

                if ($model == 'App\Models\Eventtype')
                return response()->json(['error' => 'Event type not found.'], 404);

                if ($model == 'App\Models\Role')
                return response()->json(['error' => 'Role not found.'], 404);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }

    public function removeRoleFromEventType($eventCode, $role) {
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            try {
                $eventType = Eventtype::where('event_code', $eventCode)->firstOrFail();
                $targetRole = Role::where('role', $role)->firstOrFail();
                $eventType->roles()->detach($targetRole->id);
                return response()->json(['success' => 'Role removed from event type.'], 200);
            } catch (ModelNotFoundException $e) {
                $model = $e->getModel();

                if ($model == 'App\Models\Eventtype')
                return response()->json(['error' => 'Not found.'], 404);

                if ($model == 'App\Models\Eventtype')
                return response()->json(['error' => 'Not found.'], 404);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }
}
