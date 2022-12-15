<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                $accessToken = Auth::setTTL(1)->claims(['user' => $user])->attempt(['email' => $validated['email'], 'password' => $validated['password']]);
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

            $accessToken = Auth::setTTL(1)->claims(['user' => $user])->attempt($validated);

            // Override the token ttl
            // $token = auth()->setTTL(7200)->attempt($credentials);
            $refreshToken = Auth::setTTL(30)->attempt($validated);

            if(! $accessToken || ! $refreshToken) {
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
        return $this->respondWithTwoTokens(Auth::refresh());
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
