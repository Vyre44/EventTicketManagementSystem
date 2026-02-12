<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Admin Kullanıcı Controller - Resource CRUD
 * 
 * Form Request'ler: StoreUserRequest, UpdateUserRequest (validation)
 * Query Builder: when() ile koşullu filtreleme
 * Hash facade: bcrypt parola hashleme
 * Koruma: Admin'in kendini silmesini/rol düşürmesini engeller
 */
class UserController extends Controller
{
    /**
     * Index - Kullanıcı listesi (arama, sıralama, sayfalama)
     * Filtreler: q (ad veya email), sortBy (id, name, email, role), sortDir (asc/desc)
     * Admin yetkisi ile tüm kullanıcılar gösterilir
     */
    public function index()
    {
        $q = request('q');
        $sortBy = request('sortBy', 'id');
        $sortDir = request('sortDir', 'desc');
        
        // Geçerli sütunları kontrol et
        $allowedColumns = ['id', 'name', 'email', 'role'];
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'id';
        }
        
        // Geçerli yönü kontrol et
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }
        
        $users = User::query()
            ->when($q, fn($query) =>
                $query->where(function($q2) use ($q) {
                    $q2->where('name', 'like', "%$q%")
                       ->orWhere('email', 'like', "%$q%");
                })
            )
            ->orderBy($sortBy, $sortDir)
            ->paginate(20)
            ->withQueryString();
        return view('admin.users.index', compact('users', 'q', 'sortBy', 'sortDir'));
    }

    // Route Model Binding: User
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    // Route Model Binding: N/A (new resource)
    /**
     * Request Validation: StoreUserRequest
     * name, email, password, role (required|unique)
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return redirect()->route('admin.users.index')->with('success', 'Kullanıcı oluşturuldu.');
    }

    // Route Model Binding: User
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Request Validation: UpdateUserRequest
     * name, email (unique except current), password (optional), role
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        if ($user->id === auth()->id()) {
            unset($data['role']);
        }
        $user->update($data);
        return redirect()->route('admin.users.index')->with('success', 'Kullanıcı güncellendi.');
    }

    // Route Model Binding: User
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            abort(403);
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Kullanıcı silindi.');
    }
}
