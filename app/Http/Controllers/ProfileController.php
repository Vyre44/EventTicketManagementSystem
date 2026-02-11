<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * ProfileController: Kullanıcı profilini düzenleme, güncelleme ve silme işlemlerini yöneten controller.
 * 
 * İş Mantığı:
 * - Kullanıcı sadece kendi profilini güncelleyebilir (auth()->user())
 * - E-posta değişirse e-mail doğrulaması sıfırlanır
 * - Hesap silme işlemi güvenlik için şifre ister
 * - Enum doğrulaması: Beklenebilir rol değerleri sadece UserRole enum
 * - Invalid/null rol → 403 Forbidden (Enum doğrulaması)
 * 
 * Güvenlik:
 * - Middleware: Authenticated users only
 * - Enum validation: Role must be valid UserRole
 * - Self-only: User sadece kendi profilini işleyebilir
 */
class ProfileController extends Controller
{
    /**
     * Constructor: Enum doğrulaması middleware'ı ekle
     * 
     * Amaç: Kullanıcının role alanı geçerli UserRole enum değeri olmalı
     * Invalid/null role → 403 Forbidden
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            
            // Kullanıcı rolü geçersiz veya null ise 403 döndür
            if (!$user || !($user->role instanceof UserRole)) {
                abort(403, 'Geçersiz kullanıcı rolü: ' . ($user->role ?? 'null'));
            }
            
            return $next($request);
        });
    }

    /**
     * Profil düzenleme formunu göster
     * 
     * @param Request $request
     * @return View
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Profil bilgilerini güncelle (ad, email)
     * 
     * @param ProfileUpdateRequest $request
     * @return RedirectResponse
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        // E-posta değişirse doğrulama sıfırla
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        // Turkish success message
        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated')
            ->with('message', 'Profil bilgileri başarıyla güncellendi.');
    }

    /**
     * Kullanıcı hesabını sil (irreversible)
     * 
     * Güvenlik:
     * - Şifre doğrulaması (current_password validator)
     * - Oturum kapatma + session invalidate
     * - Ana sayfaya yönlendir
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')
            ->with('message', 'Hesabınız ve tüm verileri kalıcı olarak silinmiştir.');
    }
}
