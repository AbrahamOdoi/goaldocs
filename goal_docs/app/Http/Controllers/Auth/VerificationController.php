<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\AuditLogger;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;


class VerificationController extends Controller
{
    public function chooseMethod()
    {
        return view('auth.verify-method');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'channel' => 'required|in:email,phone',
        ]);

        $user = auth()->user();

        // ✅ RATE LIMIT: max 5 per hour
        $key = 'send-otp:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['Too many OTP requests. Try again in ' . ceil($seconds/60) . ' minutes.']);
        }

        RateLimiter::hit($key, 3600); // 1 hour decay

        // ✅ rest of your OTP logic
        $code = rand(100000, 999999);

        // Store OTP with 15min expiry
        DB::table('otp_codes')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'code' => $code,
                'channel' => $request->channel,
                'expires_at' => now()->addMinutes(15),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Also store the channel in session (optional)
        session(['otp_channel' => $request->channel]);

        if ($request->channel === 'email') {
            $user->notify(new \App\Notifications\SendOtpNotification($code));
        } else {
            // ✅ Send SMS with proper URL encoding
            $query = http_build_query([
                'username'   => config('services.deywuro_sms.username'),
                'password'   => config('services.deywuro_sms.password'),
                'source'     => config('services.deywuro_sms.source'),
                'destination'=> $user->phone,
                'message'    => "Your Goal Doc OTP is: $code",
            ]);

            $url = config('services.deywuro_sms.url') . '?' . $query;

            $response = Http::get($url);

            AuditLogger::log( "OTP requested via $request->channel on $user->phone with response $response");

            logger('Deywuro SMS Response: ' . $response->body());
        }

        return redirect()->route('verification.enter')->with('success', 'Verification code sent!');
    }

    public function resendOtp(Request $request)
    {
        $channel = session('otp_channel', 'email'); // default to email if missing

        $user = auth()->user();

        // ✅ RATE LIMIT: max 5 per hour
        $key = 'send-otp:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['Too many OTP requests. Try again in ' . ceil($seconds/60) . ' minutes.']);
        }

        RateLimiter::hit($key, 3600); // 1 hour decay

        // ✅ rest of your OTP logic
        $code = rand(100000, 999999);

        // Store OTP with 15min expiry
        DB::table('otp_codes')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'code' => $code,
                'channel' => $channel,
                'expires_at' => now()->addMinutes(15),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Also store the channel in session (optional)
        session(['otp_channel' => $channel]);

        if ($channel === 'email') {
            // Send email
//            Mail::raw("Your verification code is: $code", function ($message) use ($user) {
//                $message->to($user->email)
//                    ->subject('Your OTP Verification Code');
//            });

            $user->notify(new \App\Notifications\SendOtpNotification($code));
        } else {
            // ✅ Send SMS with proper URL encoding
            $query = http_build_query([
                'username'   => config('services.deywuro_sms.username'),
                'password'   => config('services.deywuro_sms.password'),
                'source'     => config('services.deywuro_sms.source'),
                'destination'=> $user->phone,
                'message'    => "Your Goal Doc OTP is: $code",
            ]);

            $url = config('services.deywuro_sms.url') . '?' . $query;

            $response = Http::get($url);

            AuditLogger::log( "OTP resend requested via $channel on $user->phone");
        }

        return response()->json(['status' => 'OK']);
    }

    public function showOtpForm(Request $request)
    {
        $channel = session('otp_channel', 'email'); // default to email if missing

        // optional: last 4 digits or masked email
        $user = auth()->user();
        $masked = $channel === 'phone'
            ? substr($user->phone, -4)
            : substr($user->email, 0, 4) . '****';

        AuditLogger::log( "OTP verification form accessed");

        return view('auth.verify-enter', [
            'channel' => $channel,
            'masked' => $masked,
        ]);
    }

    public function checkOtp(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = auth()->user();

        $otp = DB::table('otp_codes')
            ->where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            AuditLogger::log( "OTP verification failed");
            return back()->withErrors(['code' => 'Invalid or expired code']);
        }

        // ✅ Optionally mark email_verified_at if email was used
        if ($otp->channel === 'email') {
            $user->update(['email_verified_at' => now()]);
        }

        // ✅ Delete used OTP
        DB::table('otp_codes')->where('id', $otp->id)->delete();

        // Send registration SMS if plain password is in session (registration flow)
        if (session()->has('plain_password')) {
            $plainPassword = session('plain_password');
            $email = $user->email;
            $phone = $user->phone;
            $message = "Welcome to GoalDocs! Your registration was successful.\nEmail: $email\nPassword: $plainPassword\nPlease keep this information safe.";

            // Send SMS
            $query = http_build_query([
                'username'   => config('services.deywuro_sms.username'),
                'password'   => config('services.deywuro_sms.password'),
                'source'     => config('services.deywuro_sms.source'),
                'destination'=> $phone,
                'message'    => $message,
            ]);
            $url = config('services.deywuro_sms.url') . '?' . $query;
            $response = \Illuminate\Support\Facades\Http::get($url);
            // Remove plain password from session
            session()->forget('plain_password');
        }

        AuditLogger::log( "OTP verification successful");
        return redirect()->route('dashboard')->with('success', 'Your account has been verified!');
    }



}
