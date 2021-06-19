<?php
//done
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Models\System;


class SystemVersionController extends Controller
{
    public function showSystemVersions($id, Request $request) {
        try {
            $system = System::where('id', $id)->firstOrFail();
            $versions = $system->versions()->get();
            return new VersionCollection($versions);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'System with id '.$id.' not found.'], 404);
        }
    }
}
