<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware
 * 
 * Belirli rollere sahip kullanıcıları route/controller'lara erişime izin verir.
 * 
 * Kullanım:
 * ->middleware('role:admin')                    // Sadece admin
 * ->middleware('role:admin,organizer')          // Admin veya organizer
 * ->middleware(['auth', 'role:admin,organizer']) // Auth + role kontrolü
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Middleware parametreleri UserRole enum'ına dönüştürülerek strict karşılaştırma yapılır.
     * 
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles Middleware parametrelerinden gelen roller (örn: "admin,organizer")
     * @return Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Giriş yapmamış kullanıcıyı yönlendir veya 401 döndür
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Middleware parametrelerini UserRole enum'ına dönüştür
        // Parametreler string olarak gelir: "admin,organizer" veya ["admin", "organizer"]
        $allowedRoles = $this->parseRoles($roles);

        // Kullanıcı rolü izin verilen roller arasında değilse 403 döner
        if (!in_array($user->role, $allowedRoles, true)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }

    /**
     * Middleware parametrelerini UserRole enum array'ine dönüştür.
     * 
     * Desteklenen formatlar:
     * - ["admin"]
     * - ["admin,organizer"]
     * - ["admin", "organizer"]
     * 
     * @param array $roles
     * @return array<UserRole>
     * @throws \InvalidArgumentException Geçersiz role var ise
     */
    private function parseRoles(array $roles): array
    {
        // String'leri virgülle böl ve trim et
        $roleStrings = [];
        foreach ($roles as $role) {
            if (str_contains($role, ',')) {
                $roleStrings = array_merge($roleStrings, array_map('trim', explode(',', $role)));
            } else {
                $roleStrings[] = trim($role);
            }
        }

        // Her string'i UserRole enum'ına dönüştür
        $enums = array_map(function ($roleStr) {
            $enum = UserRole::tryFrom($roleStr);
            if (!$enum) {
                throw new \InvalidArgumentException("Invalid role in middleware: '{$roleStr}'");
            }
            return $enum;
        }, $roleStrings);

        return array_values($enums);
    }
}
