<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AgencyInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        $isNewUser = ! $user;

        if ($isNewUser) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => null,
                'tipe_pendaftaran' => null,
            ]);
        } else {
            $user->update([
                'google_id' => $user->google_id ?? $googleUser->getId(),
                'avatar' => $googleUser->getAvatar() ?? $user->avatar,
            ]);
        }

        // Role assignment HANYA saat user baru dibuat DAN belum punya role
        if ($isNewUser || $user->roles->isEmpty()) {
            $invitation = AgencyInvitation::where('email', $user->email)
                ->where('is_redeemed', false)
                ->first();

            if ($invitation) {
                $user->assignRole('agency_admin');
                $user->update(['agency_id' => $invitation->agency_id]);
                $invitation->update(['is_redeemed' => true]);
            } else {
                $user->assignRole('student');
            }
        }

        Auth::login($user);

        // Redirect sesuai role
        if ($user->hasRole('super_admin')) {
            return redirect()->route('superadmin.dashboard');
        } elseif ($user->hasRole('agency_admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home');
    }
}
