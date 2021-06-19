<?php

namespace App\Http\Controllers;

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


class TrashSystemController extends Controller
{
    /**
     * Returns only trashed records.
     */
    public function showTrash() {
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            $systems = System::onlyTrashed()->get();
            return new SystemCollection($systems);
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }

    /**
     * Restores all trashed records.
     */
    public function restoreTrash() {
        $superuser = auth()->user()->role->superuser;
        $errors = [];

        if ($superuser == true) {
            $systems = System::onlyTrashed()->get();

            if (count($systems) > 0) {
                foreach ($systems as $system) {
                    $system->deleted_by = null;
                    if ($system->save()) {
                        $system->restore();
                    } else {
                        array_push($errors, 'Failed to restore system with id '.$system->id.' from trash.');
                    }
                }

                if (count($errors) > 0) {
                    return response()->json(['success' => 'All trashed records successfully restored.', 'error' => $errors], 200);
                } else if(count($errors) > 0 && count($errors) == count($systems)) {
                    return response()->json(['error' => 'Encountered errrors while restoring trashed records. No records restored.'], 500);
                } else {
                    return response()->json(['success' => 'All trashed records successfully restored.'], 200);
                }
            } else {
                return response()->json(['error' => 'No trashed records found.'], 404);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }

    /**
     * Restores a trashed record.
     */
    public function recoverTrash($id, Request $request) {
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            try {
                $system = System::onlyTrashed()->where('id', $id)->firstOrFail();
                $system->deleted_by = null;
                if ($system->save()) {
                    $system->restore();
                    $restoredSystem = System::where('id', $id)->firstOrFail();
                    return new SystemResource($restoredSystem);
                } else {
                    return response()->json(['error' => 'Encountered an error while restoring system with id '.$id.' from trash.'], 500);
                }
            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'System with id '.$id.' not found on trash.'], 404);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }

    /**
     * Permanently deletes all trashed records.
     */
    public function clearTrash() {
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            $systems = System::onlyTrashed()->get();
            if (count($systems) > 0) {
                foreach ($systems as $system) {
                    $system->forceDelete();
                }
                return response()->json(['success' => 'All trashed records deleted permanently.'], 200);
            } else {
                return response()->json(['error' => 'No trashed records found.'], 404);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }

    /**
     * Permanently delete a trashed record.
     */
    public function deleteTrash($id, Request $request) {
        $superuser = auth()->user()->role->superuser;

        if ($superuser == true) {
            try {
                $system = System::onlyTrashed()->where('id', $id)->firstOrFail();
                $system->forceDelete();
                return response()->json(['success' => 'System with id '.$id.' deleted permanently.'], 200);
            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'System with id '.$id.' not found on trash.'], 404);
            }
        } else {
            return response()->json(['error' => 'Requesting user does not have enough authority.'], 401);
        }
    }
}
