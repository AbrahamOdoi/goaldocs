<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('accountSettings.account', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phoneNumber' => 'nullable|string|max:20',

        ]);

        $user->name = $request->firstName . ' ' . $request->lastName;
        $user->email = $request->email;
        $user->phone = $request->phoneNumber;
        // SECURITY: Users cannot change their organization - this must be managed by admins
        // $user->type_name = $request->organization;
        $user->save();

        return redirect()->route('account.settings')->with('success', 'Account updated successfully.');
    }

    public function updateAvatar(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:800',
        ]);

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
            $user->save();
        }

        return redirect()->route('account.settings')->with('success', 'Profile picture updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|string|min:8|confirmed',
        ], [
            'newPassword.confirmed' => 'The new password confirmation does not match.'
        ]);

        if (!\Hash::check($request->currentPassword, $user->password)) {
            return back()->withErrors(['currentPassword' => 'Current password is incorrect.']);
        }

        $user->password = \Hash::make($request->newPassword);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }

    public function deactivate(Request $request)
    {
        $user = Auth::user();
        $user->is_active = false;
        $user->save();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Your account has been deactivated.');
    }
} 