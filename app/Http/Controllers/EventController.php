<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\System;
use App\Models\Zone;
use App\Models\Event;
use App\Http\Resources\Event\EventResource;
use App\Http\Resources\Event\EventCollection;
use App\Models\Eventtype;
use App\Traits\EventHistoryService;

class EventController extends Controller
{
    /**
     * Show all records. If the request has the "today" key,
     * only records for the current will be returned. Records
     * for a specific date may be fetched by including a "date"
     * key containing a specific date. Note that keys "today"
     * and "date" may not exist simultaneously on the request.
     */
    public function index(Request $request) {
        if ($request->has('today') && $request->has('date')) {
            return response()->json(['error' => 'Only one of the keys today and date may be present on a request.'], 422);
        } else if ($request->has('today')) {
            $today = now()->format('Y-m-d');
            $events = Event::where('start_date', '<=', $today)
                            ->where('end_date', '>=', $today)->get();
            return new EventCollection($events);
        } else if ($request->filled('date')) {
            $validator = Validator::make($request->all(), [
                'date' => 'date_format:Y-m-d'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $events = Event::where('start_date', '<=', $request->input('date'))
                            ->where('end_date', '>=', $request->input('date'))->get();
            return new EventCollection($events);
        } else {
            $events = Event::all();
            return new EventCollection($events);
        }
    }

    /**
     * Shows a specific event using an id.
     */
    public function showEvent($id, Request $request) {
        $event = Event::where('id', $id)->firstOrFail();
        return new EventResource($event);
    }

    /**
     * Creates a new event. The event details must be contained on the
     * request's body for the method to work.
     */
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
            $zones = $request->input('zones');
            foreach ($zones as $zone) {
                $checkZone = $system->zones()->where('zone_prefix', $zone['prefix'])->firstOrFail();
            }

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

                EventHistoryService::addHistory(config('constants.datatypes.event'), $event->id, config('constants.status.create'));
            });

            return new EventResource($event);
        } catch (ModelNotFoundException $e) {
            $model = $e->getModel();

            if ($model == 'App\Models\Eventtype')
            return response()->json(['error' => 'etypeNot found.'], 404);

            if ($model == 'App\Models\System')
            return response()->json(['error' => 'systemNot found.'], 404);

            if ($model == 'App\Models\Zone')
            return response()->json(['error' => 'Zone Not found for insert.'], 404);
        }
    }

    /**
     * Edits an existing event using its id. The event
     * details must be contained on the request's
     * body for the method to work.
     */
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
            $zones = $request->input('zones');
            foreach ($zones as $zone) {
                $checkZone = $system->zones()->where('zone_prefix', $zone['prefix'])->firstOrFail();
            }

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

            DB::transaction(function () use ($system, $zones, $event, $eventTypes){
                $event->eventtypes()->detach();
                $event->zones()->detach();

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

                EventHistoryService::addHistory(config('constants.datatypes.event'), $event->id, config('constants.status.update'));
            });

            return new EventResource($event);
        } catch (ModelNotFoundException $e) {
            $model = $e->getModel();

            if ($model == 'App\Models\Event')
            return response()->json(['error' => 'Event not found.'], 404);

            if ($model == 'App\Models\Eventtype')
            return response()->json(['error' => 'etypeNot found.'], 404);

            if ($model == 'App\Models\System')
            return response()->json(['error' => 'systemNot found.'], 404);

            if ($model == 'App\Models\Zone')
            return response()->json(['error' => 'Zone Not found.'], 404);
        }
    }

    /**
     * Moves a specific event to the trash using its id. If the
     * request has the "permanent" key, the event is permanently
     * deleted instead.
     */
    public function deleteEvent($id, Request $request) {
        try {
            $username = auth()->user()->username;
            $event = Event::where('id', $id)->firstOrFail();

            if ($request->has('permanent')) {
                DB::transaction(function () use ($event, $username){
                    $event->eventtypes()->detach();
                    $event->zones()->detach();

                    $event->refresh();

                    $event->forceDelete();
                });

                return response()->json(['success' => 'Event with id #'.$id.' has been permanently deleted.'], 200);
            } else {
                DB::transaction(function () use ($event, $username){
                    $event->deleted_by = $username;
                    $event->save();
                    $event->refresh();
                    $event->delete();

                    EventHistoryService::addHistory(config('constants.datatypes.event'), $event->id, config('constants.status.delete'));
                });

                return response()->json(['success' => 'Event with id #'.$id.' has been moved to trash.'], 200);
            }
        } catch (ModelNotFoundException $e) {
            $model = $e->getModel();

            if ($model == 'App\Models\Event')
            return response()->json(['error' => 'Event not found.'], 404);
        }
    }
}
