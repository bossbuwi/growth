<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\System;
use App\Http\Resources\System\SystemResource;
use App\Http\Resources\System\SystemCollection;
use App\Models\Version;
use App\Http\Resources\System\VersionResource;
use App\Models\Zone;
use App\Http\Resources\Zone\ZoneResource;
use App\Models\Event;
use App\Http\Resources\Event\EventResource;
use App\Http\Resources\Event\EventCollection;
use App\Models\Eventtype;


class EventController extends Controller
{
    public function index(Request $request) {
        if ($request->has('today')) {

        } else if ($request->filled('date')) {
            $validator = Validator::make($request->all(), [
                'date' => 'date_format:Y-m-d'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
        } else {
            $events = Event::all();
            return new EventCollection($events);
        }
    }

    public function showEvent($id, Request $request) {
        $event = Event::where('id', $id)->firstOrFail();
        return new EventResource($event);
    }

    public function createEvent(Request $request) {
        $body = json_decode($request->getContent(), true);
        Log::error($body);
        $validator = Validator::make($body, [
            'system' => 'required',
            'type' => 'required',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d|after_or_equal:startDate',
            'zoneQty' => 'required|numeric',
            'zones' => 'required|array',
            'zones.*.prefix' => 'required|distinct'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $username = auth()->user()->username;
            $system = System::where('global_prefix', $request->input('system'))->firstOrFail();
            $type = Eventtype::where('event_code', $request->input('type'))->firstOrFail();

            $event = new Event();
            $event->jira_case = $request->input('jiraCase');
            $event->api_used = $request->input('apiUsed');
            $event->compiled_sources = $request->input('compiledSources');
            $event->start_date = $request->input('startDate');
            $event->end_date = $request->input('endDate');
            $event->feature_on = $request->input('featureOn');
            $event->feature_off = $request->input('featureOff');
            $event->details = $request->input('details');
            $event->created_by = $username;
            $event->last_modified_by = $username;

            $zones = $request->input('zones');
            Log::error($zones);

            DB::transaction(function () use ($system, $type, $zones, $event){
                $system->events()->save($event);
                $system->refresh();
                $event->eventtypes()->attach($type->id);
                $event->refresh();
            });

            return new EventResource($event);
        } catch (ModelNotFoundException $e) {
            $model = $e->getModel();

                if ($model == 'App\Models\Eventtype')
                return response()->json(['error' => 'etypeNot found.'], 404);

                if ($model == 'App\Models\System')
                return response()->json(['error' => 'systemNot found.'], 404);
        }
    }

    public function editEvent($id, Request $request) {

    }

    public function deleteEvent($id, Request $request) {

    }
}
