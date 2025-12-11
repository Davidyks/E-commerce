<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User as User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // Login
    public function loginPage()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Username / Email / No HP wajib diisi',
            'password.required' => 'Password wajib diisi',
        ]);

        if($validator->fails()){
            return redirect()->back()
                        ->withInput()
                        ->withErrors($validator);
        }

        $user = User::where('email', $request->username)
                    ->orWhere('username', $request->username)
                    ->orWhere('phone_number', $request->username)
                    ->first();

        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->with('danger', 'User tidak ditemukan');
        }

        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()
                ->withInput()
                ->with('danger', 'Password salah');
        }

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Login berhasil!');
    }

    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(Str::random(16)), //pasword random
                'google_id' => $googleUser->getId(),
            ]);
        }

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Login dengan Google berhasil!');
    }


    // Register Page 1 Input No HP
    public function registerPhonePage()
    {
        return view('auth.register_phone');
    }

    public function registerPhone(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|numeric|unique:users,phone_number',
        ], [
            'phone_number.required' => 'Nomor HP wajib diisi',
            'phone_number.unique' => 'Nomor HP sudah terdaftar',
        ]);

        // Simpan nomor HP sementara di session
        session(['register_phone' => $request->phone_number]);

        return redirect()->route('register.passwordPage');
    }

    // Register Page 2 Input Password
    public function registerPasswordPage()
    {
        // Pastikan nomor HP sudah ada di session
        if (!session()->has('register_phone')) {
            return redirect()->route('register.phonePage');
        }

        return view('auth.register_password');
    }

    public function registerPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password'
        ], [
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'password_confirmation.same' => 'Konfirmasi password tidak cocok dengan password.',
        ]);

        // Ambil nomor HP dari session
        $phone = session('register_phone');

        // Simpan user baru
        $user = User::create([
            'phone_number' => $phone,
            'password' => $request->password,
        ]);

        // Hapus session
        session()->forget('register_phone');

        // Auto login setelah register
        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Registrasi berhasil!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
