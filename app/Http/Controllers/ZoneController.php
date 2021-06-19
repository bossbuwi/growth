<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use App\Models\System;
use App\Models\Zone;
use App\Http\Resources\Zone\ZoneResource;


class ZoneController extends Controller
{
    public function index() {

    }

    public function addZoneToSystem($id, Request $request) {
        $username = auth()->user()->username;
        $superuser = auth()->user()->role->superuser;

        $validator = Validator::make($request->all(), [
            'zonePrefix' => 'required',
            'zoneName' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($superuser == true) {
            try {
                $system = System::where('id', $id)->firstOrFail();

                $zone = new Zone();
                $zone->zone_prefix = $request->input('zonePrefix');
                $zone->zone_name = $request->input('zoneName');
                $zone->created_by = $username;
                $zone->last_modified_by = $username;

                if ($system->zones()->save($zone)) {
                    return new ZoneResource($zone);
                } else {
                    return response()->json(['error' => 'There is a problem saving the data. Please try again.'], 500);
                }
            } catch (ModelNotFoundException $e) {
                $model = $e->getModel();

                if ($model == 'App\Models\System')
                return response()->json(['error' => 'System with name '.$id.' not found.'], 404);
            } catch (\Illuminate\Database\QueryException $e) {
                if(config('properties.db_connection') == 'pgsql') {
                    $errorCode = $e->errorInfo[0];
                    if ($errorCode == 23505)
                    return response()->json(['error' => 'A zone with the same prefix on the target system already exists '.
                        'either on the live or trashed records.'], 500);
                } else {
                    return response()->json(['error' => 'A database error occurred. This may be because a '.
                        'zone with the same prefix on the target system already exists either on the live or trashed records.'], 500);
                }
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }

    public function editZoneOnSystem($id, $zonePrefix, Request $request) {
        $username = auth()->user()->username;
        $superuser = auth()->user()->role->superuser;

        $validator = Validator::make($request->all(), [
            'zonePrefix' => 'required',
            'zoneName' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($superuser == true) {
            try {
                $system = System::where('id', $id)->firstOrFail();

                $zone = $system->zones()->where('zone_prefix', $zonePrefix)->firstOrFail();

                $zone->zone_prefix = $request->input('zonePrefix');
                $zone->zone_name = $request->input('zoneName');
                $zone->last_modified_by = $username;

                if ($system->zones()->save($zone)) {
                    return new ZoneResource($zone);
                } else {
                    return response()->json(['error' => 'There is a problem saving the data. Please try again.'], 500);
                }
            } catch (ModelNotFoundException $e) {
                $model = $e->getModel();

                if ($model == 'App\Models\System')
                return response()->json(['error' => 'System with id '.$id.' not found.'], 404);

                if ($model == 'App\Models\Zone')
                return response()->json(['error' => 'Zone with name '.$zonePrefix.' not found on target system.'], 404);
            } catch (\Illuminate\Database\QueryException $e) {
                if(config('properties.db_connection') == 'pgsql') {
                    $errorCode = $e->errorInfo[0];
                    if ($errorCode == 23505)
                    return response()->json(['error' => 'A zone with the same prefix on the target system already exists '.
                        'either on the live or trashed records.'], 500);
                } else {
                    return response()->json(['error' => 'A database error occurred. This may be because a '.
                        'zone with the same prefix on the target system already exists either on the live or trashed records.'], 500);
                }
                Log::error($e);
                return response()->json(['error' => 'An unexpected error occured.'], 500);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }

    public function removeZoneFromSystem($id, $zonePrefix, Request $request) {
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            try {
                $system = System::where('id', $id)->firstOrFail();
                $zone = $system->zones()->where('zone_prefix', $zonePrefix)->firstOrFail();

                if (count($zone->events()->get()) > 0)
                return response()->json(['error' => 'System with name '.$system->global_prefix.' has dependencies and cannot be deleted.'], 422);

                $zone->delete();
                return response()->json(['success' => 'Zone with prefix '.$zonePrefix.' has been deleted permanently.'], 200);
            } catch (ModelNotFoundException $e) {
                $model = $e->getModel();

                if ($model == 'App\Models\System')
                return response()->json(['error' => 'System with id '.$id.' not found.'], 404);

                if ($model == 'App\Models\Zone')
                return response()->json(['error' => 'Zone with name '.$zonePrefix.' not found on system with id '.$id.'.'], 404);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }
}
