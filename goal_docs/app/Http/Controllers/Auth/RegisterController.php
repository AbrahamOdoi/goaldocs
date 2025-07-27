<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

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
            // Create main user only
            $user = User::create([
                'name' => $request->contact_name,
                'type' => $request->type,
                'email' => $request->contact_email,
                'phone' => $request->phone,
                'type_name' => $request->type !== 'individual' ? $request->type_name : null,
                'password' => Hash::make($request->password),
                'is_admin' => true, // All new signups are admin by default
            ]);

            DB::commit();

            Auth::login($user);

            // Store plain password in session for post-OTP SMS
            session(['plain_password' => $request->password]);

            // Redirect to OTP verification method selection
            return redirect()->route('verification.method');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }
}
