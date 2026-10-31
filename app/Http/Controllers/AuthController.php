<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $credentials = $request->validateWithBag('login', [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password yang dimasukkan salah.'], 'login')
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route(Auth::user()->role === 'admin' ? 'admin' : 'home'));
    }

    public function showLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'film_id' => ['sometimes', 'integer', 'exists:films,id'],
        ]);

        if (isset($validated['film_id'])) {
            $request->session()->put(
                'url.intended',
                route('movies.show', $validated['film_id']),
            );
        }

        return redirect()->route('home')->with('open_login', true);
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validateWithBag('register', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $userRole = Role::firstOrCreate(['name' => 'user']);
            $profile = Profile::create([
                'umur' => 0,
                'bio' => '',
                'alamat' => '',
            ]);

            return User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'profile_id' => $profile->id,
                'role_id' => $userRole->id,
                'role' => 'user',
            ]);
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'Akun berhasil dibuat. Selamat datang!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Anda berhasil logout.');
    }

    public function adminDashboard(): View
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        return view('components.admin');
    }
}
