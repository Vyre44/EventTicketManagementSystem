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
    public function index()
    {
        $q = request('q');
        $users = User::query()
            ->when($q, fn($query) =>
                $query->where(function($q2) use ($q) {
                    $q2->where('name', 'like', "%$q%")
                       ->orWhere('email', 'like', "%$q%");
                })
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();
        return view('admin.users.index', compact('users', 'q'));
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return redirect()->route('admin.users.index')->with('success', 'Kullanıcı oluşturuldu.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

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

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            abort(403);
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Kullanıcı silindi.');
    }
}
