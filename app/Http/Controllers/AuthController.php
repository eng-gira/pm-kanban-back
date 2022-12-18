<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['register', 'login']]);
    }
    public function register(Request $request) {
        try {
            $validated = $request->validate([
                'email' => 'required|email|unique:users,email',
                'name' => 'required',
                'password' => 'required|min:8'
            ]);

            $user = new User;
            $user->email = $validated['email'];
            $user->name = $validated['name'];
            $user->password = bcrypt($validated['password']); // must store encrypted (bycrypted) pw for Auth::attempt to work.

            if($user->save()) {
                $accessToken = Auth::setTTL(10)->claims(['user' => $user])->attempt(['email' => $validated['email'], 'password' => $validated['password']]);
                $refreshToken = Auth::setTTL(30)->attempt(['email' => $validated['email'], 'password' => $validated['password']]);
 
                return $this->respondWithTwoTokens($accessToken, $refreshToken);
            }

        } catch(\Exception $e) {
            return json_encode(['message' => 'failed', 'data' => $e->getMessage()]);
        }
    }

    public function login(Request $request) {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required'
            ]);

            $user = User::where('email', '=', $validated['email'])->first();

            $accessToken = Auth::setTTL(10)->claims(['user' => $user])->attempt($validated);

            // Override the token ttl
            $refreshToken = Auth::setTTL(30)->attempt($validated);

            if(! $accessToken) {
                return response()->json(['message' => 'failed', 'data' => 'Unauthorized'], 401);
            }
            
            return $this->respondWithTwoTokens($accessToken, $refreshToken);

        } catch(\Exception $e) {
            return json_encode(['message'=> 'failed', 'data' => $e->getMessage()]);
        }
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['data' => 'Successfully logged out']);
    }

    public function refresh()
    {
        $authHeader = apache_request_headers()['ExpTok'] ?? false;

        if($authHeader == false) { 
            echo response()->json(['data' => 'no ExpTok!'], 401);
            return false;
        }
        
        $expToken = $authHeader;
        try {
            // $token = JWTAuth::getToken();
            // $payload = JWTAuth::getPayload($token)->toArray();
            // return json_encode(['payload' => $payload, 'user' => $payload['user'], 'id' => $payload['user']['id']]);

            return $this->respondWithTwoTokens(Auth::refresh($expToken));

        } catch(TokenExpiredException $e) {
            return response()->json(['data' => 'Token expired: ' . $e->getMessage()], 401);
        } catch(Exception $e) {
            return response()->json(['data' => $e->getMessage()], 401);
        }

    }

    protected function respondWithTwoTokens($accessToken, $refreshToken = null)
    {
        $data = [
            'access_token' => $accessToken,
            'token_type' => 'bearer',
        ];
        
        if($refreshToken !== null) {
            $data['refresh_token'] = $refreshToken;
        }

        return response()->json($data);
    }
}
