<?php
namespace App\Http\Controllers\Auth;

use App\Models\Organisation;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        try {
            $attributes = $request->validate([
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'phone' => 'string|max:12',
                'password' => 'required|min:7|max:255',
            ]);
            Log::info('Attributes before UUID generation:', $attributes);
            $attributes['userId'] = (string) Str::uuid();
            $attributes['password'] = Hash::make($attributes['password']);
            Log::info('Attributes after UUID generation:', $attributes);
            $user = User::create($attributes);

            try {
                $organisation = $user->organisations()->create([

                    'orgId' => (string) Str::uuid(),
                    'name' => "{$attributes['firstName']}'s Organisation",
                    'description' => 'Your default organisation',
                ]);

            } catch (\Exception $e) {
                return response()->json([$e->getMessage()]);
            }

            $token = $user->createToken('authToken')->plainTextToken;

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
}
