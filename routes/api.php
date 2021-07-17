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
use App\Http\Controllers\TrashEventController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\RuleController;

header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, Accept,charset,boundary,Content-Length');
header('Access-Control-Allow-Origin: *');

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
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    //login to the api (open) - /api/auth/login
    Route::post('/login', [AuthController::class, 'login']);
    //register to the api (open) - /api/auth/register
    //this is a backdoor route used only for testing and must not reach production
    Route::post('/register', [AuthController::class, 'register']);
    //logout from the api (guarded) - /api/auth/logout
    Route::post('/logout', [AuthController::class, 'logout']);
    //show the profile of the user currently logged in (guarded) - /api/auth/user
    Route::get('/user', [AuthController::class, 'userProfile']);
    //login using an HttpOnly cookie (open) - /api/auth/autologin
    Route::post('/autologin', [AuthController::class, 'autologin']);
    //login insecurely using a stored token (open) - /api/auth/autologin
    Route::post('/autologin-insecure', [AuthController::class, 'autologinNotSecure']);
    //default redirect route for endpoints that requires a login auth to access (guarded) - /api/auth/noaccess
    Route::get('/noaccess', [AuthController::class, 'noAccess'])->name('noaccess');
});

/**
 * Guarded route group for the user model.
 */
Route::group([
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
    'middleware' => 'auth:api',
    'prefix' => 'roles'
], function () {
    //show all roles - /api/roles/index
    Route::get('/index', [RoleController::class, 'index']);
    //get role for specific user - /api/roles/role/name
    Route::get('/role/{name}', [RoleController::class, 'showRole'])->whereAlpha('name');
    //get users under a specific role - /api/roles/role/name/users
    Route::get('/role/{name}/users', [RoleController::class, 'showUsersWithRole'])->whereAlpha('name');
});

/**
 * Open route group for the machine model.
 */
Route::group([
    'prefix' => 'machines'
], function () {
    //show all machines - /api/machines/index
    Route::get('/index', [MachineController::class, 'index']);
    //show specific machine - /api/machines/machine/name
    Route::get('/machine/{name}', [MachineController::class, 'showMachine'])->whereAlphaNumeric('name');
    //show systems under a specific machine - /api/machine/name/systems
    Route::get('/machine/{name}/systems', [MachineController::class, 'showSystemsOnMachine'])->whereAlphaNumeric('name');
});

/**
 * Guarded route group for the machine model.
 */
Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'machines'
], function () {
    //create a new record - /api/machines/machine
    Route::post('/machine', [MachineController::class, 'createMachine']);
    //edit a record - /api/machines/machine/name
    Route::put('/machine/{name}', [MachineController::class, 'editMachine'])->whereAlphaNumeric('name');
    //delete a record - /api/machines/machine/name
    Route::delete('/machine/{name}', [MachineController::class, 'deleteMachine'])->whereAlphaNumeric('name');
});

/**
 * Open route group for the system model.
 */
Route::group([
    'prefix' => 'systems'
], function () {
    //show all systems - /api/systems/index
    Route::get('/index', [SystemController::class, 'index']);
    //show a specific system - /api/systems/system/id
    Route::get('/system/{id}', [SystemController::class, 'showSystem'])->whereNumber('id');
    //show zones under a specific system - /api/system/id/zones
    Route::get('/system/{id}/zones', [SystemController::class, 'showZonesOnSystem'])->whereNumber('id');
});

/**
 * Guarded route group for the system model.
 */
Route::group([
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
    //changes the password of a system - /api/systems/system/id/password
    Route::post('/system/{id}/password', [SystemController::class, 'changeSystemPassword'])->whereNumber('id');
});

Route::group([
    'prefix' => 'rules'
], function () {
    //show all records - /api/event-types/index
    Route::get('/index', [RuleController::class, 'index']);
});

/**
 * Open route group for the event type model.
 */
Route::group([
    'prefix' => 'event-types'
], function () {
    //show all records - /api/event-types/index
    Route::get('/index', [EventtypeController::class, 'index']);
    //show specific event type - /api/event-types/type/code
    Route::get('/type/{code}', [EventtypeController::class, 'showEventType'])->whereAlphaNumeric('code');
});

/**
 * Guarded route group for the event type model.
 */
Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'event-types'
], function () {
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
    'prefix' => 'events'
], function () {
    //show all records - /api/events/index
    Route::get('/index', [EventController::class, 'index']);
    //show specific event type - /api/events/id
    Route::get('/event/{id}', [EventController::class, 'showEvent'])->whereNumber('id');
});

/**
 * Guarded route group for the event model.
 */
Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'events'
], function () {
    //create event - /api/events/event
    Route::post('/event', [EventController::class, 'createEvent']);
    //edit event - /api/events/event/id
    Route::put('/event/{id}', [EventController::class, 'editEvent'])->whereNumber('id');
    //move event to trash - /api/events/event/id
    Route::delete('/event/{id}', [EventController::class, 'deleteEvent'])->whereNumber('id');
});

/**
 * Guarded route group for the trashed event model.
 */
Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'events'
], function () {
    //show all trashed events - /api/events/trash/index
    Route::get('/trash/index', [TrashEventController::class, 'indexTrash']);
    //show specific trashed event - /api/events/trash/event/id
    Route::get('/trash/event/{id}', [EventController::class, 'showTrash'])->whereNumber('id');
    //show restore trashed events - /api/events/trash/event/id
    Route::post('/trash/event/{id}', [TrashEventController::class, 'restoreTrash'])->whereNumber('id');
    //permanently delete trashed events - /api/events/trash/event/id
    Route::delete('/trash/event/{id}', [TrashEventController::class, 'cleanTrash'])->whereNumber('id');
});

/**
 * Open route group for the configuration model.
 */
Route::group([
    'prefix' => 'configs'
], function () {
    //show all trashed events - /api/events/trash/index
    Route::get('/index', [ConfigurationController::class, 'index']);
    //show specific trashed event - /api/events/trash/event/id
    Route::get('/config/{id}', [ConfigurationController::class, 'showConfiguration'])->whereNumber('id');
});
