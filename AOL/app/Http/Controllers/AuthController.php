<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User as User;
use App\Models\SellerDetail as SellerDetail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

        return redirect('/home')->with('success', 'Login berhasil!');
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

        return redirect('/home')->with('success', 'Login dengan Google berhasil!');
    }


    public function registerPage()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|min:5|max:40', // Bisa email / phone / username
            'password' => 'required|max:18|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
            'password_confirmation' => 'required|same:password',
        ], [
            'identifier.required' => 'Email / No HP / Username wajib diisi',
            'identifier.min' => 'Username minimal 5 karakter',
            'identifier.max' => 'Username maximal 40 karakter',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.max' => 'Password maximal 18 karakter',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan simbol',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi',
            'password_confirmation.same' => 'Konfirmasi password tidak sama',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $identifier = $request->identifier;

        // Tentukan tipe input
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } elseif (preg_match('/^[0-9]{8,15}$/', $identifier)) {
            $field = 'phone_number';
        } elseif (preg_match('/^\d+$/', $identifier)) {
            return back()->withErrors(['username' => 'Username tidak boleh hanya angka dan format nomor telpon tidak sesuai']);
        } else {
            $field = 'username';
        }

        // Cek apakah user sudah terdaftar
        $existing = User::where($field, $identifier)->first();
        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('danger', ucfirst($field) . ' sudah digunakan');
        }

        // Buat user baru
        $user = User::create([
            'username' => $field == 'username' ? $identifier : null,
            'email' => $field == 'email' ? $identifier : null,
            'phone_number' => $field == 'phone_number' ? $identifier : null,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect('/home')->with('success', 'Registrasi berhasil!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function showProfile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user(); 

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|min:5|max:40',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|numeric|unique:users,phone_number,'.$user->id, 
            'email' => 'required|email|unique:users,email,'.$user->id,
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
            'password' => 'nullable|max:18|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
        ], [
            'name.min' => 'Name minimal 5 karakter',
            'name.max' => 'Name maximal 40 karakter',
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email telah digunakan',
            'email.email' => 'Email tidak sesuai format',
            'phone_number.unique' => 'Nomor tersebut telah digunakan',
            'phone_number.numeric' => 'Phone number harus numeric',
            'password.min' => 'Password minimal 6 karakter',
            'password.max' => 'Password maximal 18 karakter',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan simbol',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && Storage::exists('public/' . $user->profile_picture)) {
                Storage::delete('public/' . $user->profile_picture);
            }
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $path;
        }

        $user->name = $request->name;
        $user->address = $request->address;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save(); 

        // Sync User with Seller Detail
        if ($user->role === 'seller' && $user->sellerDetail) {

            $sellerDetail = $user->sellerDetail;

            // Update store name jika name berubah
            if ($request->filled('name')) {
                $sellerDetail->store_name = $user->name;
            }

            // Update store logo jika upload foto baru
            if ($request->hasFile('profile_picture')) {
                $sellerDetail->store_logo = $user->profile_picture;
            }

            $sellerDetail->save();
        }

        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }
}
