<?php

namespace App\Http\Controllers;

use App\Http\Resources\Event\EventCollection;
use App\Http\Resources\Event\EventResource;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Machine;
use App\Http\Resources\Machine\MachineResource;
use App\Http\Resources\Machine\MachineCollection;
use App\Models\System;
use App\Http\Resources\System\SystemResource;
use App\Http\Resources\System\SystemCollection;


class TrashEventController extends Controller
{
    public function indexTrash() {
        $events = Event::onlyTrashed()->get();
        return new EventCollection($events);
    }

    public function showTrash($id, Request $request) {
        try {
            $event = Event::onlyTrashed()->where('id', $id)->firstOrFail();
            return new EventResource($event);

        } catch (ModelNotFoundException $e) {
            $model = $e->getModel();

            if ($model == 'App\Models\Event')
            return response()->json(['error' => 'Event not found.'], 404);
        }
    }

    public function restoreTrash($id, Request $request) {
        try {
            if ($id != 0) {
                $event = Event::onlyTrashed()->where('id', $id)->firstOrFail();
                DB::transaction(function () use ($event){
                    $event->deleted_by = null;
                    $event->save();
                    $event->refresh();
                    $event->restore();
                });
                return response()->json(['success' => 'Event restored.'], 200);
            } else {
                $events = Event::onlyTrashed()->get();
                if (count($events) > 0) {
                    foreach ($events as $item) {
                        DB::transaction(function () use ($item){
                            $item->deleted_by = null;
                            $item->save();
                            $item->refresh();
                            $item->restore();
                        });
                    }
                    return response()->json(['success' => 'All trashed records restored.'], 200);
                } else {
                    return response()->json(['error' => 'No trashed records found.'], 404);
                }
            }
        } catch (ModelNotFoundException $e) {
            $model = $e->getModel();

            if ($model == 'App\Models\Event')
            return response()->json(['error' => 'Event not found.'], 404);
        }
    }

    public function cleanTrash($id, Request $request) {
        try {
            if ($id != 0) {
                $event = Event::onlyTrashed()->where('id', $id)->firstOrFail();
                DB::transaction(function () use ($event){
                    $event->eventtypes()->detach();
                    $event->zones()->detach();

                    $event->refresh();

                    $event->forceDelete();
                });
                return response()->json(['success' => 'Event permanently deleted.'], 200);
            } else {
                $events = Event::onlyTrashed()->get();
                if (count($events) > 0) {
                    foreach ($events as $item) {
                        DB::transaction(function () use ($item){
                            $item->eventtypes()->detach();
                            $item->zones()->detach();

                            $item->refresh();

                            $item->forceDelete();
                        });
                    }
                    return response()->json(['success' => 'All trashed records deleted permanently.'], 200);
                } else {
                    return response()->json(['error' => 'No trashed records found.'], 404);
                }
            }
        } catch (ModelNotFoundException $e) {
            $model = $e->getModel();

            if ($model == 'App\Models\Event')
            return response()->json(['error' => 'Event not found.'], 404);
        }
    }
}
