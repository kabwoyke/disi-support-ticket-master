<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    ]);

    // Attempt to authenticate using the custom guard and username field
    if (Auth::guard('solves')->attempt([
        'username' => $credentials['username'],
        'password' => $credentials['password'],
    ], $request->boolean('remember'))) {

        $request->session()->regenerate();

        return redirect()->intended(route('solves-dashboard'));
    }

    // Return back with error if authentication fails
    return back()->withErrors([
        'username' => 'The provided credentials do not match our records.',
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
    return redirect()->route('solves-login');
}


}
