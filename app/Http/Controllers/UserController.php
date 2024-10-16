<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function destroy(User $user)
    {
        // Ensure the admin is authorized to delete the user
        if (auth()->user()->isAdmin()) { 
            $user->delete();
            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        }

        return redirect()->route('users.index')->with('error', 'You are not authorized to delete this user.');
    }
}

