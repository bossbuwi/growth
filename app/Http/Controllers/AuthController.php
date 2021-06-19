<?php
//done
namespace App\Http\Controllers;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\Models\User;
use App\Models\Role;


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'autologin', 'noAccess']]);
    }

    public function noAccess() {
        return response()->json(['error' => 'Please login to access the resource.'], 401);
    }

    /**
     * Reads the client side JWT cookie and determines if user is authorized.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function autologin(Request $request) {
        $cookie = $request->cookie(config('properties.jwt_name'));
        $payload = JWTAuth::setToken($cookie)->getPayload();
        $parsedPayload = json_decode(json_encode($payload), true);
        $userId = $parsedPayload['id'];
        $username = $parsedPayload['username'];

        if (User::where('id', $userId)->where('username', $username)->exists()) {
            $user = User::where('id', $userId)->where('username', $username)->first();
            Auth::login($user);
        }
        return response()->json(auth()->user());
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        } else if ($validator->passes()) {
            $username = $request->input('username');
            $password = $request->input('password');

            $ldap = ldap_connect("ldap://misys.global.ad:389");
            $ldaprdn = 'MISYSROOT' . "\\" . $username;

            ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

            if (@ldap_bind($ldap, $ldaprdn, $password)) {
                if (Auth::attempt($request->only('username','password'))) {
                    if (!$token = auth()->attempt($validator->validated())) {
                        return response()->json(['error' => 'Unauthorized credentials.'], 401);
                    }

                    return $this->createNewToken($token);
                } else {
                    try {
                        $user = User::where('username', $username)->firstOrFail();
                        $user->password = bcrypt($password);
                        $user->save();

                        if (!$token = auth()->attempt($validator->validated())) {
                            return response()->json(['error' => 'Unauthorized credentials.'], 401);
                        }

                        return $this->createNewToken($token);
                    } catch (ModelNotFoundException $e) {
                        $role = Role::where('user', true)->first();
                        $newUser = new User();
                        $newUser->username = $username;
                        $newUser->password = bcrypt($password);
                        $newUser->role_id = $role->id;
                        $newUser->save();

                        if (!$token = auth()->attempt($validator->validated())) {
                            return response()->json(['error' => 'Unauthorized credentials.'], 401);
                        }

                        return $this->createNewToken($token);
                    }
                }
            } else if (User::where('username', $username)->exists()) {
                //this will execute if the user is not found in LDAP directory but saved in the app's database
                //this opens a vulnerability where an LDAP user can log in with their old password
                //this should include a check to only allow users with a certain role to log in
                if (Auth::attempt($request->only('username','password'))) {
                    if (!$token = auth()->attempt($validator->validated())) {
                        return response()->json(['error' => 'Unauthorized credentials.'], 401);
                    }
                    return $this->createNewToken($token);
                } else {
                    return response()->json(['error' => 'Unauthorized credentials.'], 401);
                }
            } else {
                return response()->json(['error' => 'Unauthorized credentials.'], 401);
            }
        }
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = new User();
        $user->username = $request->input('username');
        $user->password = bcrypt($request->input('password'));
        $user->save();

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }


    /**
     * Log the user out, invalidate the JWT and delete the client side cookie.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();
        return response()->json([
            'message' => 'User successfully signed out.'
        ])->withCookie(Cookie::forget(config('properties.jwt_name')));
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => config('properties.jwt_token_type'),
            'expires_in' => auth()->factory()->getTTL() * 60,
            'username' => auth()->user()->username,
            'role' => auth()->user()->role->role
        ])->withCookie(cookie()->forever(config('properties.jwt_name'), $token, null, null, null, true, false, null));
    }

    /**
     * (Deprecated) Generate a JWT with custom payload.
     * The custom payload is now moved at the correct class,
     * on the User class
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function create(){
        $payloadable = [
            'id' => auth()->user()->id,
            'username' => auth()->user()->username,
        ];
        $payload = JWTFactory::sub('token')->data($payloadable)->make();
        $token = JWTAuth::encode($payload)->get();
        return response()->json([
            'access_token' => $token,
            'token_type' => config('properties.jwt_token_type'),
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ])->withCookie(cookie()->forever(config('properties.jwt_name'), $token, null, null, null, true, false, null));
    }

}
