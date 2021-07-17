<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Configuration;
use App\Http\Resources\Configuration\ConfigurationCollection;


class ConfigurationController extends Controller
{
    public function index(Request $request) {
        $configs = Configuration::all();
        return new ConfigurationCollection($configs);
    }

    public function showConfiguration($id, Request $request) {

    }

    public function editConfiguration($id, Request $request) {

    }
}
