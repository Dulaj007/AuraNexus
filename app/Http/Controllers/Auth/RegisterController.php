<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Support\SiteSettings;

class RegisterController extends Controller
{
    /**
     * Whether new registrations are currently allowed, per the admin setting.
     */
    protected function registrationsOpen(): bool
    {
        $settings = class_exists(SiteSettings::class) ? SiteSettings::public() : [];
        return (int)($settings['registrations_open'] ?? 1) === 1;
    }

    /**
     * Site name used in user-facing messages.
     */
    protected function siteName(): string
    {
        $settings = class_exists(SiteSettings::class) ? SiteSettings::public() : [];
        return (string)($settings['site_name'] ?? config('app.name', 'Site'));
    }

    public function show()
    {
        // Show the closed page instead of the registration form when registrations are off.
        if (!$this->registrationsOpen()) {
            return response()->view('auth.registration-closed', [
                'siteName' => $this->siteName(),
            ], 403);
        }

        return view('auth.register');
    }

    public function store(Request $request)
    {
        if (!$this->registrationsOpen()) {
            return redirect()
                ->route('login')
                ->with('error', $this->siteName() . ' is currently not allowing new registrations.');
        }

        $settings = class_exists(SiteSettings::class) ? SiteSettings::public() : [];
        $minimumAge = (int) ($settings['minimum_age'] ?? 18);

        // Server-side validation, including the reCAPTCHA response.
        $request->validate(
            [
                'name' => 'required|string|max:30|regex:/^[A-Za-z ]+$/',
                'username' => 'required|min:5|max:30|regex:/^[A-Za-z0-9_]+$/|unique:users|unique:pending_users',
                'email' => 'required|email:rfc,dns|max:100|unique:users|unique:pending_users',
                'dob' => ['required', 'date', 'before:-'.$minimumAge.' years'],

                'password' => [
                    'required',
                    'confirmed',
                    'min:8',
                    'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).+$/'
                ],
                'terms' => 'accepted',
                'g-recaptcha-response' => 'required|captcha',
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

        // Reject disposable/temporary email domains.
        $emailDomain = substr(strrchr($request->email, "@"), 1);
        if (in_array($emailDomain, config('blocked_emails'))) {
            return back()->withErrors([
                'email' => 'Temporary or disposable email addresses are not allowed.'
            ])->withInput();
        }

        $age = Carbon::parse($request->dob)->age;

        $token = Str::uuid();

        $pending = PendingUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'age' => $age,
            'password' => Hash::make($request->password),
            'verification_token' => $token,
            'expires_at' => now()->addHours(24),
        ]);

        Mail::to($pending->email)->send(
            new \App\Mail\VerifyEmail($pending)
        );

        return redirect()->route('login')
            ->with('success', 'Check your email to verify your account.');
    }
}
