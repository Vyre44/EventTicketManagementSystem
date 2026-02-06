<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\UserRole;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * EventOwner Middleware - Kaynak Bazlı Yetkilendirme
 * 
 * Route model binding: $request->route('event') Event modelini alır
 * Admin bypass: Tüm etkinliklere erişim
 * Organizer kontrolü: organizer_id == auth()->id()
 * 
 * Durum Kodu:
 * - 403 Forbidden: Yetkisiz erişim (sahip değil)
 * - 404 Not Found: Kaynak bulunamadı
 */
class EventOwnerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        // Admin: Tüm etkinliklere erişim
        if ($user->role === UserRole::ADMIN) {
            return $next($request);
        }

        // Organizer: Sadece kendi etkinliklerine erişim
        if ($user->role === UserRole::ORGANIZER) {
            $event = $request->route('event');

            // Event instance kontrolü - Route model binding başarısız ise 404
            if (!($event instanceof Event)) {
                abort(404);
            }

            // Sahiplik kontrolü - Event başka organizer'a ait ise 403
            if ($event->organizer_id !== $user->id) {
                abort(403);
            }

            return $next($request);
        }

        abort(403);
    }
}
