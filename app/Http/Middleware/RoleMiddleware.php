<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware - Route-level authorization
 * 
 * Variadic parameters: 'role:admin,organizer'
 * UserRole enum casting ve strict comparison
 * parseRoles() helper: string -> enum array transformation
 * Returns 403 Forbidden if role mismatch
 */
class RoleMiddleware
{
    /**
     * Laravel middleware handle - role-based access control
     * Variadic $roles: 'admin', 'admin,organizer'
     * Returns 403 if user role not in allowed roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        /**
         * Kullanıcı giriş yapmamış mı?
         * 
         * JSON istekleri: 401 Unauthorized JSON yanıtı
         * HTML istekleri: /login sayfasına yönlendir
         * 
         * Normalde auth middleware bunu yapar ama double-check yaparız.
         */
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        /**
         * Middleware parametrelerini UserRole enum array'e dönüştür
         * 
         * Örnek transformasyon:
         * Input:  ['admin,organizer']
         * Output: [UserRole::ADMIN, UserRole::ORGANIZER]
         * 
         * parseRoles() method bunu işler:
         * - Virgüllü string'leri parçala
         * - Boşlukları temizle
         * - Her birini UserRole::tryFrom() ile convert et
         * - Geçersiz roller için exception fırlat
         */
        $allowedRoles = $this->parseRoles($roles);

        /**
         * ROLÜ KONTROL ET
         * 
         * in_array($user->role, $allowedRoles, true)
         * - $user->role: UserRole enum (ASLA STRING DEĞİL)
         * - $allowedRoles: UserRole enum array
         * - true: STRICT comparison (=== kullanır, == değil)
         * 
         * NEDEN STRICT?
         * - Tip güvenliği sağlar
         * - String-enum karıştırılmasını engeller
         * - Production-ready security
         * 
         * SONUÇ:
         * - Kullanıcı rolleri listesinde varsa: true -> sonraki middleware'ye
         * - Kullanıcı rolleri listesinde yoksa: false -> 403 abort
         */
        if (!in_array($user->role, $allowedRoles, true)) {
            /**
             * UNAUTHORIZED - 403 HATASI
             * 
             * Kullanıcı giriş yapmış ama bu route'a erişme yetkisi yok
             * 
             * Örnek senaryolar:
             * - ATTENDEE kullanıcısı /admin/* route'a girmeye çalışan
             * - ORGANIZER kullanıcısı /admin/* route'a girmeye çalışan (admin olacak)
             * - Admin olmayan kullanıcı admin-only route'a erişmeye çalışan
             */
            abort(403, 'Unauthorized.');
        }

        /**
         * BAŞARILI - Sonraki middleware'ye geç
         * 
         * Kullanıcı:
         * - Giriş yapmış (auth middleware geçti)
         * - Gerekli role'e sahip (bu middleware geçti)
         * - Route'un controller'ı çalışabilir
         */
        return $next($request);
    }

    /**
     * ============================================================
     * parseRoles() - HELPER METHOD
     * ============================================================
     * 
     * AMAÇ:
     * Middleware parametrelerini UserRole enum array'e dönüştür
     * 
     * NASIL ÇALIŞIR:
     * 1. Gelen roller parametrelerini al (variadic array)
     * 2. Her parametreyi virgülle böl (eğer virgül varsa)
     * 3. Boşlukları temizle (trim)
     * 4. Her string'i UserRole::tryFrom() ile enum'a dönüştür
     * 5. Geçersiz roller için exception fırlat
     * 
     * GİRDI ÖRNEKLERİ:
     * ['admin']                    -> [UserRole::ADMIN]
     * ['admin,organizer']          -> [UserRole::ADMIN, UserRole::ORGANIZER]
     * ['admin', 'organizer']       -> [UserRole::ADMIN, UserRole::ORGANIZER]
     * ['admin , organizer']        -> [UserRole::ADMIN, UserRole::ORGANIZER]
     * 
     * ÇIKTI:
     * UserRole enum array
     * 
     * HATALAR:
     * - Geçersiz role: InvalidArgumentException
     * - Örnek: 'invalid_role' -> Exception
     * 
     * @param array $roles - Middleware parametrelerinden gelen roller
     * @return array<UserRole> - Dönüştürülmüş enum array
     * @throws \InvalidArgumentException Geçersiz role varsa
     */
    private function parseRoles(array $roles): array
    {
        /**
         * ADIM 1: Rolleri virgülle böl ve düzle
         * 
         * Amaç: Hem virgüllü hem arrayli parametreleri destekle
         * 
         * Örnek:
         * Input:  ['admin,organizer']
         * Output: ['admin', 'organizer']
         * 
         * Veya:
         * Input:  ['admin', 'organizer']
         * Output: ['admin', 'organizer']
         */
        $roleStrings = [];
        foreach ($roles as $role) {
            // Eğer role parametresi virgül içeriyorsa parçala
            if (str_contains($role, ',')) {
                // Örnek: 'admin,organizer' -> ['admin', 'organizer']
                $roleStrings = array_merge(
                    $roleStrings,
                    array_map('trim', explode(',', $role))
                );
            } else {
                // Virgül yoksa doğrudan ekle
                $roleStrings[] = trim($role);
            }
        }

        /**
         * ADIM 2: String'leri UserRole enum'a dönüştür
         * 
         * UserRole::tryFrom($value):
         * - Eğer value geçerli enum case ise enum nesnesi döner
         * - Eğer geçersiz ise null döner
         * - Örnek: UserRole::tryFrom('admin') -> UserRole::ADMIN
         * - Örnek: UserRole::tryFrom('invalid') -> null
         */
        $enums = array_map(function ($roleStr) {
            /**
             * TRY FROM: Güvenli enum dönüşümü
             * 
             * UserRole enum'da:
             * case ADMIN = 'admin'
             * case ORGANIZER = 'organizer'
             * case ATTENDEE = 'attendee'
             * 
             * tryFrom('admin') -> UserRole::ADMIN object
             * tryFrom('organizer') -> UserRole::ORGANIZER object
             * tryFrom('invalid') -> null
             */
            $enum = UserRole::tryFrom($roleStr);
            
            /**
             * HATA KONTROLÜ
             * 
             * Eğer string geçersiz role ise:
             * - Exception fırlat
             * - Middleware'in konfigürasyonu hatalı demek
             * - Admin tarafından düzeltilmeli
             * 
             * Örnek hata:
             * InvalidArgumentException: "Invalid role in middleware: 'super_admin'"
             */
            if (!$enum) {
                throw new \InvalidArgumentException("Invalid role in middleware: '{$roleStr}'");
            }
            
            /**
             * BAŞARILI: Enum nesnesi döner
             * 
             * Örnek:
             * 'admin' -> UserRole::ADMIN (enum case)
             * 'organizer' -> UserRole::ORGANIZER (enum case)
             */
            return $enum;
        }, $roleStrings);

        /**
         * SONUÇ
         * 
         * Dönüş: UserRole enum array
         * Örnek: [UserRole::ADMIN, UserRole::ORGANIZER]
         * 
         * Bu array handle() method'da in_array() ile kontrol edilir.
         * 
         * array_values() - Index'leri yeniden düzenle
         * (Tekrar eden değerler varsa bunların index'i uyumlu olsun)
         */
        return array_values($enums);
    }
}
