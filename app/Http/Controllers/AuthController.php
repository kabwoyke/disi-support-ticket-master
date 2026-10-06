<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //


    public function render_login(){

        return view();

    }

    public function logout(){
        Auth::logout();
        session()->invalidate();
        session()->regenerate();
        return redirect()->route('login');

    }

    public function support_logout(){

        auth()->guard('support')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('ict-login');
    }

    
}
