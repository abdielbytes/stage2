<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show($id)
    {
//        dd('Hello');
        $user = Auth::user();

        if ($user->userId != $id && !$user->organisations()->whereHas('users', function ($query) use ($id) {
                $query->where('users.userId', $id);
            })->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'statusCode' => 403
            ], 403);
        }

        $requestedUser = User::where('userId', $id)->firstOrFail();

        return response()->json([
            'status' => 'success',
            'message' => 'User retrieved successfully',
            'data' => [
                'userId' => $requestedUser->userId,
                'firstName' => $requestedUser->firstName,
                'lastName' => $requestedUser->lastName,
                'email' => $requestedUser->email,
                'phone' => $requestedUser->phone,
            ]
        ], 200);
    }
}
