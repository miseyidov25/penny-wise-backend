<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // Function to update user info
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        // Validate each field only if it is present in the request
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check if the name is present and update it
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        // Check if the email is present and update it
        if ($request->has('email')) {
            $user->email = $request->email;
        }

        // Check if the password is present and update it
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        // Save the updated user information
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
    }


    // Function to delete user account
    public function deleteAccount()
    {
        $user = auth()->user();
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully'
        ], 200);
    }

    public function getUser(Request $request)
    {
        return response()->json($request->user());
    }
}