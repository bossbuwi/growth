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


class TrashEventController extends Controller
{
    public function showTrash() {

    }

    public function restoreTrash() {

    }

    public function cleanTrash() {

    }
}
