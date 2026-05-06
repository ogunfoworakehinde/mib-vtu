<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller {
    public function login(Request $request) {
        if ($request->isMethod('post')) {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                return response()->json(['redirect' => '/']);
            }
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        return view('auth.login');
    }

    public function register(Request $request) {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'full_name' => 'required',
                'email' => 'required|email|unique:users',
                'phone' => 'required|digits_between:10,20|unique:users',
                'password' => 'required|min:6'
            ]);
            $data['password'] = Hash::make($data['password']);
            User::create($data);
            return response()->json(['message' => 'Registration successful. Please login.']);
        }
        return view('auth.register');
    }

    public function logout() {
        Auth::logout();
        return redirect('/login');
    }
}
