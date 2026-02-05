<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * EventOwner Middleware - Kaynak Bazlı Yetkilendirme
 * 
 * Route model binding: $request->route('event') Event modelini alır
 * Admin bypass: Tüm etkinliklere erişim
 * Organizer kontrolü: organizer_id == auth()->id()
 * 403 Forbidden: Yetkisiz erişim engellenir
 */
class EventOwnerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }
        $role = $user->role;
        if ($role === UserRole::ADMIN) {
            return $next($request);
        }
        if ($role === UserRole::ORGANIZER) {
            $event = $request->route('event');
            if (is_object($event) && method_exists($event, 'getAttribute')) {
                $organizerId = $event->getAttribute('organizer_id');
            } elseif (is_array($event) && isset($event['organizer_id'])) {
                $organizerId = $event['organizer_id'];
            } else {
                $organizerId = $event->organizer_id ?? null;
            }
            if ($organizerId == $user->id) {
                return $next($request);
            }
        }
        abort(403);
    }
}
