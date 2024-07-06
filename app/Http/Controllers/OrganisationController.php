<?php
namespace App\Http\Controllers;

use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrganisationController extends Controller {
    public function index() {
        $user = Auth::user();
        $organisations = $user->organisations;

        return response()->json([
            'status' => 'success',
            'message' => 'Organisations retrieved successfully',
            'data' => $organisations,
        ]);
    }

    public function show($orgId) {
        $user = Auth::user();
        $organisation = Organisation::find($orgId);

        if (!$organisation || !$user->organisations->contains($orgId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organisation not found or unauthorized',
                'statusCode' => 403
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Organisation retrieved successfully',
            'data' => $organisation,
        ]);
    }

    public function store(Request $request) {
        $attributes = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $attributes['orgId'] = (string) Str::uuid();
        $organisation = Organisation::create($attributes);

        $user = Auth::user();
        $user->organisations()->attach($organisation->orgId);

        return response()->json([
            'status' => 'success',
            'message' => 'Organisation created successfully',
            'data' => $organisation,
        ], 201);
    }

    public function addUser($orgId, Request $request) {
        $request->validate([
            'userId' => 'required|uuid|exists:users,userId',
        ]);

        $organisation = Organisation::find($orgId);
        if (!$organisation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organisation not found',
                'statusCode' => 404,
            ], 404);
        }

        $user = Auth::user();
        if (!$user->organisations->contains($orgId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'statusCode' => 403,
            ], 403);
        }

        $organisation->users()->attach($request->userId);

        return response()->json([
            'status' => 'success',
            'message' => 'User added to organisation successfully',
        ]);
    }
}
