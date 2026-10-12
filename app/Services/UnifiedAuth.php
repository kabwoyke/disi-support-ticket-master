<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Single sign-on across the ticketing, support and DISI-Solves modules.
 * Credentials live on `users`; one login opens every module the person has a profile in.
 */
class UnifiedAuth
{
    public const GUARDS = ['web', 'support', 'solves'];

    /** Check credentials (email or username) and sign into every module the account belongs to. */
    public function attempt(string $identifier, string $password, bool $remember = false): ?User
    {
        $user = User::where('email', $identifier)->orWhere('username', $identifier)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        $this->signIn($user, $remember);

        return $user;
    }

    public function signIn(User $user, bool $remember = false): void
    {
        Auth::guard('web')->login($user, $remember);

        if ($support = $user->supportProfile) {
            Auth::guard('support')->login($support, $remember);
        }

        if ($solves = $user->solveProfile) {
            Auth::guard('solves')->login($solves, $remember);
        }
    }

    /** Global logout: clears every guard and the session. */
    public function signOut(): void
    {
        foreach (self::GUARDS as $guard) {
            Auth::guard($guard)->logout();
        }

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
