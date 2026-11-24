<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Empleado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $key = 'employee-login:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['usuario' => 'Demasiados intentos. Espera '.RateLimiter::availableIn($key).' segundos.']);
        }
        RateLimiter::hit($key, 60);
        $employee = Empleado::whereRaw('LOWER(TRIM(usuario)) = LOWER(?)', [trim($request->string('usuario')->toString())])->first();
        $hash = $employee?->password_hash ?? '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        $valid = password_verify($request->string('password')->toString(), $hash);
        if (! $employee || ! $valid) {
            throw ValidationException::withMessages(['usuario' => 'Usuario o contraseña incorrectos.']);
        }
        Auth::login($employee);
        $request->session()->regenerate();
        RateLimiter::clear($key);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }
}
