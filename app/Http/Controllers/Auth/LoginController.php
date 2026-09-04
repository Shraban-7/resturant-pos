<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|exists:users,email',
            'password' => 'required|min:8',
        ]);

        if (!Auth::validate($credentials)) {
            return redirect()->back()->with('error', 'Invalid credentials!');
        }

        $user = User::where('email', $credentials['email'])->first();

        Auth::login($user);

        return redirect()->route('admin.dashboard');
    }

    public function logout()
    {
        Session::flush();
        Auth::logout();

        return redirect('/');
    }
}
