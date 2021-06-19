<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\EventtypeController;
use App\Http\Controllers\EventController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * Route group for authenticating into the app. The guarded and
 * open routes are handled at the AuthController class.
 */
Route::group([
    //tested
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    //login to the api (open) - /api/auth/login
    Route::post('/login', [AuthController::class, 'login']);
    //register to the api (open) - /api/auth/register
    Route::post('/register', [AuthController::class, 'register']);
    //logout from the api (guarded) - /api/auth/register
    Route::post('/logout', [AuthController::class, 'logout']);
    //show the profile of the user currently logged in (guarded) - /api/auth/user
    Route::get('/user', [AuthController::class, 'userProfile']);
    //login using an HttpOnly cookie (open) - /api/auth/autologin
    Route::post('/autologin', [AuthController::class, 'autologin']);
    //default redirect route for endpoints that requires a login auth to access (guarded) - /api/auth/noaccess
    Route::get('/noaccess', [AuthController::class, 'noAccess'])->name('noaccess');
});

/**
 * Guarded route group for the user model.
 */
Route::group([
    //tested
    'middleware' => 'auth:api',
    'prefix' => 'users'
], function () {
    //shows all users - /api/users/index
    Route::get('/index', [UserController::class, 'index']);
    //shows a specific user - /api/users/username
    Route::get('/{username}', [UserController::class, 'getUser'])->whereAlphaNumeric('username');
    //modifies a user's role - /api/users/role/username
    Route::put('/role/{username}', [UserController::class, 'modifyRole']);
});

/**
 * Guarded route group for the role model.
 */
Route::group([
    //tested
    'middleware' => 'auth:api',
    'prefix' => 'roles'
], function () {
    Route::get('/index', [RoleController::class, 'index']);
    Route::get('/role/{name}', [RoleController::class, 'showRole'])->whereAlpha('name');
    Route::get('/role/{name}/users', [RoleController::class, 'showUsersWithRole'])->whereAlpha('name');
});

/**
 * Open route group for the machine model.
 */
Route::group([
    //tested
    'prefix' => 'machines'
], function () {
    //show all records - /api/machines/index
    Route::get('/index', [MachineController::class, 'index']);
    Route::get('/machine/{name}', [MachineController::class, 'showMachine'])->whereAlphaNumeric('name');
    Route::get('/machine/{name}/systems', [MachineController::class, 'showSystemsOnMachine'])->whereAlphaNumeric('name');
});

/**
 * Guarded route group for the machine model.
 */
Route::group([
    //tested
    'middleware' => 'auth:api',
    'prefix' => 'machines'
], function () {
    //create a new record - /api/machines/machine
    Route::post('/machine', [MachineController::class, 'createMachine']);
    //edit a record - /api/machines/machine/name
    Route::put('/machine/{name}', [MachineController::class, 'editMachine'])->whereAlphaNumeric('name');
    //move a record to trash - /api/machines/machine/name
    Route::delete('/machine/{name}', [MachineController::class, 'deleteMachine'])->whereAlphaNumeric('name');
});

/**
 * Open route group for the system model.
 */
Route::group([
    //tested
    'prefix' => 'systems'
], function () {
    //show all records - /api/systems/index
    Route::get('/index', [SystemController::class, 'index']);
    Route::get('/system/{id}', [SystemController::class, 'showSystem'])->whereNumber('id');
});

/**
 * Guarded route group for the system model.
 */
Route::group([
    //tested
    'middleware' => 'auth:api',
    'prefix' => 'systems'
], function () {
    //create a new record - /api/systems/system
    Route::post('/system', [SystemController::class, 'createSystem']);
    //attach new zones to a system - /api/systems/system/name/zone
    Route::post('/system/{id}/zone', [ZoneController::class, 'addZoneToSystem'])->whereNumber('id');
    //edit a system - /api/systems/system/id
    Route::put('/system/{id}', [SystemController::class, 'editSystem'])->whereNumber('id');
    //edit a zone - /api/systems/system/id/zone/name
    Route::put('/system/{id}/zone/{zonePrefix}', [ZoneController::class, 'editZoneOnSystem'])->whereNumber('id')->whereAlphaNumeric('zonePrefix');
    //move a system to trash - /api/systems/system/id
    Route::delete('/system/{id}', [SystemController::class, 'deleteSystem'])->whereNumber('id');
    //delete a zone from a system - /api/systems/system/id/zone/name
    Route::delete('/system/{id}/zone/{zonePrefix}', [ZoneController::class, 'removeZoneFromSystem'])->whereNumber('id')->whereAlphaNumeric('zonePrefix');
});

/**
 * Open route group for the event type model.
 */
Route::group([
    //tested
    'prefix' => 'event-types'
], function () {
    //show all records - /api/event-types/index
    Route::get('/index', [EventtypeController::class, 'index']);
    //show specific event type - /api/event-types/type/code
    Route::get('/type/{code}', [EventtypeController::class, 'showEventType'])->whereAlphaNumeric('code');
    //create a new record - /api/systems/system
    Route::post('/type', [EventtypeController::class, 'createEventType']);

    Route::put('/type/{code}', [EventtypeController::class, 'editEventType'])->whereAlphaNumeric('code');
    Route::delete('/type/{code}', [EventtypeController::class, 'deleteEventType'])->whereAlphaNumeric('code');
    Route::post('/type/{code}/{role}', [EventtypeController::class, 'assignRoleToEventType'])->whereAlphaNumeric('code', 'role');
    Route::delete('/type/{code}/{role}', [EventtypeController::class, 'removeRoleFromEventType'])->whereAlphaNumeric('code', 'role');
});

/**
 * Open route group for the event model.
 */
Route::group([
    //tested
    'prefix' => 'events'
], function () {
    //show all records - /api/events/index
    Route::get('/index', [EventController::class, 'index']);
    //show specific event type - /api/events/id
    Route::get('/event/{id}', [EventController::class, 'showEvent'])->whereNumber('id');
    //create event - /api/events/event
    Route::post('/event', [EventController::class, 'createEvent']);
});
