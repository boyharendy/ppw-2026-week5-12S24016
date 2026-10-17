<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    /**
     * Menampilkan formulir login Pustakawan
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Memproses autentikasi pustakawan, regenerasi ID sesi, dan antrean cookie
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // WAJIB sesuai modul: Regenerasi ID Sesi untuk mencegah Session Fixation!
            $request->session()->regenerate();

            // Simpan timestamp login di sesi
            session(['login_timestamp' => now()->format('d M Y, H:i:s') . ' WIB']);

            // Simpan email terakhir di HTTP Cookie (berlaku 7 hari = 10080 menit)
            Cookie::queue('del_last_user_email', $request->email, 10080);

            return redirect()->intended(route('buku.index'))->with('success', 'Sesi Anda telah aktif. Selamat datang, ' . Auth::user()->name);
        }

        return back()->withInput($request->only('email', 'remember'))->with('error', 'Kredensial login tidak valid.');
    }

    /**
     * Menghapus sesi server secara aman (Logout)
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // WAJIB sesuai modul: Invalidate sesi server & regenerate CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sesi server telah dihapus dengan aman.');
    }
}
