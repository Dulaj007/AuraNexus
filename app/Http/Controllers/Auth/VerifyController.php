<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingUser;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class VerifyController extends Controller
{
   public function verify($token)
{
    $pending = PendingUser::where('verification_token', $token)
        ->where('expires_at', '>', now())
        ->firstOrFail();

    DB::transaction(function () use ($pending) {

        $user = User::create([
            'name' => $pending->name,
            'email' => $pending->email,
            'username' => $pending->username,
            'password' => $pending->password,

            // ✅ verification metadata
            'email_verified_at' => now(),
            'email_verified_ip' => request()->ip(),
        ]);

        // attach default role
        $memberRole = Role::where('name', 'member')->firstOrFail();
        $user->roles()->attach($memberRole->id);

        $pending->delete();

       

    });

    return redirect()->route('login')
        ->with('success', 'Your email has been verified. You can now log in.');
}

}
