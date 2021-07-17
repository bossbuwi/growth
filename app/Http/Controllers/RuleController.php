<?php
//done
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Rule;
use App\Models\System;
use App\Http\Resources\Zone\RuleCollection;


class RuleController extends Controller
{
    public function index(Request $request) {
        $rules = Rule::all();
        return new RuleCollection($rules);
    }
}
