<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// ProfileController: Kullanıcı profilini düzenleme, güncelleme ve silme işlemlerini yöneten controller.
// - Kullanıcı kendi profilini güncelleyebilir veya hesabını silebilir.
// - E-posta değişirse doğrulama sıfırlanır.
// - Hesap silme işlemi güvenlik için şifre ister.
class ProfileController extends Controller
{
   // Kullanıcı profilini düzenleme formunu gösterir.
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }
    // Kullanıcı profilini günceller. E-posta değişirse doğrulama sıfırlanır.
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    // Kullanıcı hesabını siler. Şifre doğrulaması ister, oturumu kapatır ve ana sayfaya yönlendirir.
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

        return Redirect::to('/');
    }
}
