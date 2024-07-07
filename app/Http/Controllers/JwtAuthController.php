<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class JwtAuthController extends Controller
{

    public function register(Request $request)
    {
        try {
            $attributes = $request->validate([
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'phone' => 'string|max:12',
                'password' => 'required|min:7|max:255',
            ]);
//            Log::info('Attributes before UUID generation:', $attributes);
            $attributes['userId'] = (string)Str::uuid();
            $attributes['password'] = Hash::make($attributes['password']);
//            Log::info('Attributes after UUID generation:', $attributes);
            $user = User::create($attributes);

            try {
                $organisation = $user->organisations()->create([

                    'orgId' => (string)Str::uuid(),
                    'name' => "{$attributes['firstName']}'s Organisation",
                    'description' => 'Your default organisation',
                ]);

            } catch (\Exception $e) {
                return response()->json([$e->getMessage()]);
            }

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful',
                'data' => [
                    'accessToken' => $token,
                    'user' => $user,
                ],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'Bad request',
                'message' => 'Registration unsuccessful',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 'Bad request',
                    'message' => 'Authentication failed',
                    'statusCode' => 401
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'Internal Server Error',
                'message' => 'Could not create token',
                'statusCode' => 500
            ], 500);
        }

        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'accessToken' => $token,
                'user' => $user,
            ]
        ], 200);
    }

    public function getAuthenticatedUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }

        return response()->json(compact('user'));
    }
}
