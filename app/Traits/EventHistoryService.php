<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\EventsHistory;
use App\Models\Event;
use App\Models\Eventtype;
use App\Models\System;
use App\Models\Zone;

trait EventHistoryService
{
    //the codes below are working but there are some functions missing
    //first the status parameter needs to be saved on the database
    //second the relationships for the event type and zones from the base event
    //need to be transferred to the event history
    public static function addHistory(string $dataType, int $modelId, string $status)
    {
        $username = auth()->user()->username;

        if ($dataType === config('constants.datatypes.event')) {
            try {
                $event = Event::findOrFail($modelId);

                $newHistory = new EventsHistory();
                $newHistory->jira_case = $event->jira_case;
                $newHistory->api_used = $event->api_used;
                $newHistory->compiled_sources = $event->compiled_sources;
                $newHistory->feature_on = $event->feature_on;
                $newHistory->feature_off = $event->feature_off;
                $newHistory->details = $event->details;
                $newHistory->start_date = $event->start_date;
                $newHistory->end_date = $event->end_date;
                $newHistory->status = $status;
                $newHistory->executed_by = $username;
                $newHistory->executed_at = now();

                $system = System::where('id', $event->system->id)->firstOrFail();

                $newHistory->system_id = $system->id;

                $zones = $event->zones()->get();

                $eventTypes = $event->eventtypes()->get();

                DB::transaction(function () use ($event, $eventTypes, $zones, $newHistory){
                    $event->history()->save($newHistory);

                    foreach ($zones as $zone) {
                        $targetZone = Zone::where('zone_prefix', $zone->zone_prefix)->firstOrFail();

                        $newHistory->zones()->attach($targetZone->id);
                        $newHistory->refresh();
                    }

                    foreach ($eventTypes as $type) {
                        $targetType = Eventtype::where('event_code', $type->event_code)->firstOrFail();

                        $newHistory->eventtypes()->attach($targetType->id);
                        $newHistory->refresh();
                    }
                });
            } catch (ModelNotFoundException $e) {
                $model = $e->getModel();

                if ($model == 'App\Models\Eventtype')
                return response()->json(['error' => 'etypeNot found.'], 404);

                if ($model == 'App\Models\System')
                return response()->json(['error' => 'systemNot found.'], 404);

                if ($model == 'App\Models\Zone')
                return response()->json(['error' => 'Zone Not found for history.'], 404);
            }
        }
    }
}
