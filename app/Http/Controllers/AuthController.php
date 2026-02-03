<?php

namespace App\Http\Controllers;
// AuthController: Kullanıcı kimlik doğrulama işlemlerini (login, register, logout, şifre sıfırlama) yöneten controller.
// - Kullanıcı kayıt, giriş, çıkış ve şifre sıfırlama işlemlerini içerir.
// - Laravel'in Auth ve Password servislerini kullanır.
// - Varsayılan olarak yeni kullanıcıya attendee rolü atanır.

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Login formunu gösterir.
    public function showLoginForm()
    {
        return view('auth.login');//login formunu göster
    }

    // Kullanıcı giriş işlemi. Bilgiler doğruysa oturum açılır, yanlışsa hata döner.
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();
            $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
            if ($role === UserRole::ADMIN->value) {
                return redirect()->intended(route('admin.events.index'));
            } elseif ($role === UserRole::ORGANIZER->value) {
                return redirect()->intended(route('organizer.events.index'));
            } else {
                return redirect()->intended(route('home'));
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    // Kayıt formunu gösterir.
    public function showRegisterForm()//kayıt formunu göster
    {
        return view('auth.register');
    }

    // Kullanıcı kayıt işlemi. Yeni kullanıcı oluşturur ve attendee rolü atar.
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => UserRole::ATTENDEE->value,
        ]);

        Auth::login($user);
        return redirect()->route('home');
    }

    // Kullanıcı çıkış işlemi. Oturumu kapatır ve tokeni sıfırlar.
    public function logout(Request $request)// çıkış işlemi
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // Şifre sıfırlama formunu gösterir.
    public function showForgotPasswordForm()//şifre unutma formunu göster
    {
        return view('auth.forgot-password');
    }

    // Şifre sıfırlama linki gönderir.
    public function sendResetLinkEmail(Request $request)//şifre sıfırlama linki gönder
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink(
            $request->only('email')
        );
        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    // Şifre sıfırlama formunu gösterir.
    public function showResetForm($token)//şifre sıfırlama formunu göster
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    // Şifre sıfırlama işlemi. Token ve email doğrulanır, yeni şifre kaydedilir.
    public function resetPassword(Request $request)// şifre sıfırlama işlemi
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect('/login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
