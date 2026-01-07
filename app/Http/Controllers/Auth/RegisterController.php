<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        // ✅ Backend validation including Google reCAPTCHA
        $request->validate(
            [
                'name' => 'required|string|max:30|regex:/^[A-Za-z ]+$/',
                'username' => 'required|min:5|max:30|regex:/^[A-Za-z0-9_]+$/|unique:users|unique:pending_users',
                'email' => 'required|email:rfc,dns|max:100|unique:users|unique:pending_users',
                'dob' => ['required', 'date', 'before:-'.env('MINIMUM_AGE', 18).' years'],

                'password' => [
                    'required',
                    'confirmed',
                    'min:8',
                    'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).+$/'
                ],
                'terms' => 'accepted',
                'g-recaptcha-response' => 'required|captcha', // ✅ Google reCAPTCHA validation
            ],
            [
                'dob.before' =>
                    'This website contains 18+ content. You are not allowed to create an account.',
                'password.regex' =>
                    'Try a stronger password. It must include uppercase letters and numbers.',
                'terms.accepted' =>
                    'You must agree to the Terms & Privacy Policy to continue.',
                'email.email' =>
                    'Please enter a valid email address.',
                'username.regex' =>
                    'Username can only contain letters, numbers, and underscores.',
                'g-recaptcha-response.required' =>
                    'Please verify that you are not a robot.',
                'g-recaptcha-response.captcha' =>
                    'reCAPTCHA verification failed. Try again.',
            ]
        );

        // ✅ Block temporary email providers
        $emailDomain = substr(strrchr($request->email, "@"), 1);
        if (in_array($emailDomain, config('blocked_emails'))) {
            return back()->withErrors([
                'email' => 'Temporary or disposable email addresses are not allowed.'
            ])->withInput();
        }

        // ✅ Calculate age from DOB
        $age = Carbon::parse($request->dob)->age;

        $token = Str::uuid();

        // ✅ Create pending user
        $pending = PendingUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'age' => $age,
            'password' => Hash::make($request->password),
            'verification_token' => $token,
            'expires_at' => now()->addHours(24),
        ]);

        // ✅ Send verification email
        Mail::to($pending->email)->send(
            new \App\Mail\VerifyEmail($pending)
        );

        return redirect()->route('login')
            ->with('success', 'Check your email to verify your account.');
    }
}
