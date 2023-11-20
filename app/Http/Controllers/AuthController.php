<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function register(Request $request) {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => bcrypt($fields['password'])
            ]);
    
            $token = $user->createToken('myapptoken')->plainTextToken;
            
            // Send Email by Calling Email Service
            $sendEmail = Http::post(env("EMAIL_SERVICE")."/api/send-email", [
                "name" => $fields['name'],
                "body" => "Welcome " . $fields['name'],
                "recipient_email" => $fields['email'],
                "recipient_name" => $fields['name'],
                "subject" => "Welcome To " . env("APP_NAME") . "!"
            ]);

            // Check Response
            if ($sendEmail->successful()) {
                // Commit changes if success
                DB::commit();
                $response = [
                    'user' => $user,
                    'token' => $token
                ];
        
                return response($response, 201);
            } else {
                // Rollback changes if fail
                DB::rollBack();
                $response = [
                    "message" => "Failed to register user!" . $sendEmail->body()
                ];
                
                return response($response, 500);
            }
        } catch (\Throwable $th) {
            // Rollback changes if fail
            DB::rollBack();
        }
    }

    public function login(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->first();

        // Check password
        if(!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Bad creds'
            ], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function logout(Request $request) {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'Logged out'
        ];
    }

    public function me() {
        print_r(auth()->user());
        if (Auth::user()) {
            return response(["user" => Auth::user(), 200]);
        }

        return response(["message" => "User not found or not logged in!"], 404);
    }
}
