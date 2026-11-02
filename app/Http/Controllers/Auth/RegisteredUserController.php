<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // El registro público siempre crea clientes, junto con su ficha.
        // Los clientes que se registran en el sitio público pertenecen a la empresa principal.
        $empresaId = Empresa::principal()?->id_empresa;

        $user = DB::transaction(function () use ($request, $empresaId) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'role_id' => Role::idDe(Role::CLIENTE),
                'id_empresa' => $empresaId,
            ]);

            Cliente::create([
                'id_empresa' => $empresaId,
                'nombre' => $user->name,
                'correo' => $user->email,
                'dni' => 'TEMP-'.$user->id,
                'user_id' => $user->id,
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect('/')->with('success', '¡Bienvenido! Tu cuenta fue creada correctamente.');
    }
}
