<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User as UserModel;

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
        $request->validate([
            'login' => 'required',
            'password' => 'required|min:8',
        ], [
            'login.required' => 'Username / Email / No HP wajib diisi',
            'password.required' => 'Password wajib diisi',
        ]);

        // Ambil input login
        $login = $request->login;
        $password = $request->password;

        // Tentukan field berdasarkan input (email / phone / username)
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' :
                 (is_numeric($login) ? 'phone_number' : 'username');

        // Coba login
        if (Auth::attempt([$field => $login, 'password' => $password])) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard')->with('success', 'Login berhasil!');
        }

        return back()->withErrors(['login' => 'Kredensial tidak valid'])->withInput();
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
        $user = UserModel::create([
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
