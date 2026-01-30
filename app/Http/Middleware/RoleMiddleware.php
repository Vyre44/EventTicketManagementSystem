<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// RoleMiddleware: Route veya controller erişimini belirli kullanıcı rolleriyle sınırlar.
// - Kullanıcı login değilse login sayfasına yönlendirir.
// - Kullanıcı rolü verilen rollerden biri değilse 403 döner.
// - Enum ile veya string ile rol kontrolü yapılabilir.
class RoleMiddleware
{
    // İzin verilen rolleri kontrol eder. Uygun değilse erişimi engeller.
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Kullanıcı login değilse login sayfasına yönlendir.
        if (! $user) {
            return redirect()->route('login');
        }

        // Enum cast varsa role enum olabilir -> stringe indir
        $currentRole = $user->role instanceof \BackedEnum
            ? $user->role->value
            : $user->role;

        // Kullanıcı rolü izin verilen roller arasında değilse 403 döner.
        if (! in_array($currentRole, $roles, true)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
