<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::query()->with('roles')->orderBy('name')->paginate(20),
            'roles' => Role::query()->with('permissions')->where('guard_name', 'web')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => [
                'required',
                Rule::exists('roles', 'name')->where(fn ($query) => $query->where('guard_name', 'web')),
            ],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);
        $user->assignRole($data['role']);

        activity('security')
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties(['role' => $data['role']])
            ->log('Accès utilisateur créé par un administrateur');

        return redirect()->route('users.index')->with('status', "L'accès de {$user->name} a été créé.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'Vous ne pouvez pas désactiver votre propre accès administrateur.']);
        }

        $user->update(['is_active' => false]);

        activity('security')
            ->causedBy($request->user())
            ->performedOn($user)
            ->log('Accès utilisateur désactivé par un administrateur');

        return redirect()->route('users.index')->with('status', "L'accès de {$user->name} a été désactivé. Son historique est conservé.");
    }
}
