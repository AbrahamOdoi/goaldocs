<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Modules\User\Entities\User;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'contact_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'contact_email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'type_name' => $request->type !== 'individual' ? 'required|string|max:255' : 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->contact_name,
                'type' => $request->type,
                'email' => $request->contact_email,
                'phone' => $request->phone,
                'type_name' => $request->type !== 'individual' ? $request->type_name : null,
                'password' => Hash::make($request->password),
            ]);

            DB::commit();

            Auth::login($user);

            return response()->json(['user' => $user, 'message' => 'Registration successful'], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Registration failed: ' . $e->getMessage()], 500);
        }
    }
} 