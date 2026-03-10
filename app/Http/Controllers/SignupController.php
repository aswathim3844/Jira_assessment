<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class SignupController extends Controller
{
    public function signup(Request $request)
    {
        // 1. Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Retrieve the mock DB (array of users). 
        // Using Laravel cache to preserve the mock data between API requests.
        $mockDb = Cache::get('mock_users_db', []);

        // 3. Ensure the email doesn't already exist
        foreach ($mockDb as $user) {
            if ($user['email'] === $request->email) {
                return response()->json([
                    'message' => 'Email already exists.',
                ], 409); // 409 Conflict
            }
        }

        // 4. Hash the password for security
        $hashedPassword = Hash::make($request->password);

        // 5. Save the user data to the mock db array
        $newUser = [
            'id' => uniqid(),
            'username' => $request->username,
            'email' => $request->email,
            'password' => $hashedPassword,
        ];

        $mockDb[] = $newUser;
        Cache::put('mock_users_db', $mockDb);

        // 6. Return an account created status
        return response()->json([
            'status' => 'success',
            'message' => 'Account created successfully',
            'user' => [
                'id' => $newUser['id'],
                'username' => $newUser['username'],
                'email' => $newUser['email'],
            ]
        ], 201); // 201 Created
    }
}
