<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UnifiedAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //


    public function render_login(){

        return view();

    }

    public function logout(){
        app(UnifiedAuth::class)->signOut();
        return redirect()->route('login');

    }

    public function support_logout(){

        app(UnifiedAuth::class)->signOut();
        return redirect()->route('ict-login');
    }

    
}
