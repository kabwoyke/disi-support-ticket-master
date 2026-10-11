<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SolveAuthController extends Controller
{
    //

    public function render_login_page(){
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Please enter your username.',
            'password.required' => 'Please enter your password.',
        ]);

        $throttleKey = Str::lower($credentials['username']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'username' => "Too many failed attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('username');
        }

        // Attempt to authenticate using the custom guard and username field
        if (Auth::guard('solves')->attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {

            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $name = Auth::guard('solves')->user()->first_name ?: $credentials['username'];

            return redirect()->intended(route('solves-dashboard'))
                ->with('success', "Welcome back, {$name}! You have signed in successfully.");
        }

        RateLimiter::hit($throttleKey, 60);

        $left = max(0, 5 - RateLimiter::attempts($throttleKey));
        $hint = $left > 0 && $left <= 2 ? " You have {$left} attempt(s) left." : '';

        // Generic on purpose: don't reveal whether the username exists.
        return back()->withErrors([
            'username' => 'Incorrect username or password. Please check your details and try again.' . $hint,
        ])->onlyInput('username');
    }

public function logout(Request $request)
{
    // Log out using the support guard
    Auth::guard('solves')->logout();

    // Invalidate the current session
    $request->session()->invalidate();

    // Regenerate the CSRF token for security
    $request->session()->regenerateToken();

    // Redirect to the login page or home
    return redirect()->route('solves-login')->with('success', 'You have been signed out.');
}


}
