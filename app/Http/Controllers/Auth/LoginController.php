<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserLogin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    // Show login form
    public function show()
    {
        // Redirect logged-in users to home
        if (Auth::check()) {
            return redirect()->intended('/');
        }

        return view('auth.login');
    }

    // Handle login
    public function authenticate(Request $request)
    {
        // ✅ Validate inputs including Google reCAPTCHA
        $request->validate([
            'email' => 'required|email:rfc,dns|max:100',
            'password' => 'required|string|min:8|max:50',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        $key = Str::lower($request->input('email')) . '|' . $request->ip();
        $maxAttempts = 5;

        // ❌ Check if user is locked out
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'login' => "Too many login attempts. Try again in " . ceil($seconds / 60) . " minutes."
            ])->onlyInput('email');
        }

        // Sanitize email and password
        $email = filter_var($request->input('email'), FILTER_SANITIZE_EMAIL);
        $password = $request->input('password');

        // ✅ Find user by email
        $user = User::where('email', $email)->first();

        // Check credentials and email verification
        if (
            !$user ||
            !$user->email_verified_at ||
            !Hash::check($password, $user->password)
        ) {
            RateLimiter::hit($key, 7200); // Lockout 2 hours after 5 failed attempts
            return back()->withErrors([
                'login' => 'Your email and password did not match, or your account is not verified.'
            ])->onlyInput('email');
        }

        // ✅ Login the user
        Auth::login($user, $request->has('remember'));
        RateLimiter::clear($key);
        $request->session()->regenerate();

        // Log user login info
        DB::transaction(function () use ($request, $user) {
            UserLogin::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()->intended('/');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
