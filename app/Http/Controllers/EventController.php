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
        $validator = Validator::make($body, [
            'system' => 'required',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d|after_or_equal:startDate',
            'types' => 'required|array',
            'types.*.eventCode' => 'required|distinct',
            'zones' => 'required|array',
            'zones.*.prefix' => 'required|distinct'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $username = auth()->user()->username;

            $system = System::where('global_prefix', $request->input('system'))->firstOrFail();

            $eventTypes = $request->input('types');
            foreach ($eventTypes as $eventType) {
                $targetEventType = Eventtype::where('event_code', $eventType['eventCode'])->firstOrFail();
                if ($targetEventType->exclusive == true && count($eventTypes) > 1) {
                    return response()->json(['error' => $targetEventType->name.' is exclusive and may not be '.
                    'paired with another event type.'], 422);
                }
            }

            $event = new Event();
            $event->jira_case = $body['jiraCase'];
            $event->api_used = $body['apiUsed'];
            $event->compiled_sources = $body['compiledSources'];
            $event->start_date = $body['startDate'];
            $event->end_date = $body['endDate'];
            $event->feature_on = $body['featureOn'];
            $event->feature_off = $body['featureOff'];
            $event->details = $body['details'];
            $event->created_by = $username;
            $event->last_modified_by = $username;

            $zones = $request->input('zones');

            DB::transaction(function () use ($system, $zones, $event, $eventTypes){
                $system->events()->save($event);
                $system->refresh();
                $event->refresh();

                foreach ($zones as $zone) {
                    $targetZone = Zone::where('zone_prefix', $zone['prefix'])->firstOrFail();
                    
                    $event->zones()->attach($targetZone->id);
                    $event->refresh();
                }

                foreach ($eventTypes as $eventType) {
                    $targetType = Eventtype::where('event_code', $eventType['eventCode'])->firstOrFail();
                    
                    $event->eventtypes()->attach($targetType->id);
                    $event->refresh();
                }
            });

            return new EventResource($event);
        } catch (ModelNotFoundException $e) {
            $model = $e->getModel();

            if ($model == 'App\Models\Eventtype')
            return response()->json(['error' => 'etypeNot found.'], 404);

            if ($model == 'App\Models\System')
            return response()->json(['error' => 'systemNot found.'], 404);

            if ($model == 'App\Models\Zone')
            return response()->json(['error' => 'Zone Not found.'], 404);
        }
    }

    public function editEvent($id, Request $request) {
        $body = json_decode($request->getContent(), true);
        $validator = Validator::make($body, [
            'system' => 'required',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d|after_or_equal:startDate',
            'types' => 'required|array',
            'types.*.eventCode' => 'required|distinct',
            'zones' => 'required|array',
            'zones.*.prefix' => 'required|distinct'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $username = auth()->user()->username;

            $system = System::where('global_prefix', $request->input('system'))->firstOrFail();

            $eventTypes = $request->input('types');
            foreach ($eventTypes as $eventType) {
                $targetEventType = Eventtype::where('event_code', $eventType['eventCode'])->firstOrFail();
                if ($targetEventType->exclusive == true && count($eventTypes) > 1) {
                    return response()->json(['error' => $targetEventType->name.' is exclusive and may not be '.
                    'paired with another event type.'], 422);
                }
            }

            $event = Event::where('id', $id)->firstOrFail();
            $event->jira_case = $body['jiraCase'];
            $event->api_used = $body['apiUsed'];
            $event->compiled_sources = $body['compiledSources'];
            $event->start_date = $body['startDate'];
            $event->end_date = $body['endDate'];
            $event->feature_on = $body['featureOn'];
            $event->feature_off = $body['featureOff'];
            $event->details = $body['details'];
            $event->last_modified_by = $username;

            $zones = $request->input('zones');

            DB::transaction(function () use ($system, $zones, $event, $eventTypes){
                $system->events()->save($event);
                $system->refresh();
                $event->refresh();

                foreach ($zones as $zone) {
                    $targetZone = Zone::where('zone_prefix', $zone['prefix'])->firstOrFail();
                    
                    $event->zones()->attach($targetZone->id);
                    $event->refresh();
                }

                foreach ($eventTypes as $eventType) {
                    $targetType = Eventtype::where('event_code', $eventType['eventCode'])->firstOrFail();
                    
                    $event->eventtypes()->attach($targetType->id);
                    $event->refresh();
                }
            });

            return new EventResource($event);
        } catch (ModelNotFoundException $e) {
            $model = $e->getModel();

            if ($model == 'App\Models\Eventtype')
            return response()->json(['error' => 'etypeNot found.'], 404);

            if ($model == 'App\Models\System')
            return response()->json(['error' => 'systemNot found.'], 404);

            if ($model == 'App\Models\Zone')
            return response()->json(['error' => 'Zone Not found.'], 404);
        }
    }

    public function deleteEvent($id, Request $request) {

    }
}
