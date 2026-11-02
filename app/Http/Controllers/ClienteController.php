<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Entrenador;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $clientes = Cliente::with(['membresiaActiva.plan', 'entrenador'])
            ->withCount('clases')
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('nombre', 'like', $texto)->orWhere('dni', 'like', $texto)->orWhere('correo', 'like', $texto));
            })
            ->orderBy('nombre')
            ->paginate(25)
            ->withQueryString();

        return view('admin.clientes', [
            'clientes' => $clientes,
            'entrenadores' => Entrenador::where('activo', true)->orderBy('nombre')->get(),
            'resumen' => [
                'total' => Cliente::count(),
                'activos' => Cliente::whereHas('membresiaActiva')->count(),
                'nuevos' => Cliente::where('created_at', '>=', today()->startOfMonth())->count(),
                'con_coach' => Cliente::whereNotNull('id_entrenador')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Cliente::create($this->validar($request));

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado correctamente.');
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $cliente->update($this->validar($request, $cliente));

        return redirect()->route('clientes.index', $request->only('q', 'page'))->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        if ($cliente->ventas()->exists()) {
            return back()->with('error', 'El cliente tiene ventas registradas y no se puede eliminar.');
        }

        $cliente->delete();

        return back()->with('success', 'Cliente eliminado.');
    }

    private function validar(Request $request, ?Cliente $cliente = null): array
    {
        return $request->validateWithBag('cliente', [
            'nombre' => ['required', 'string', 'max:150'],
            'dni' => [
                'required', 'string', 'max:20',
                Rule::unique('clientes', 'dni')
                    ->where('id_empresa', Tenant::empresaId())
                    ->ignore($cliente?->id_cliente, 'id_cliente'),
            ],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email', 'max:150'],
            'id_entrenador' => ['nullable', Rule::exists('entrenadores', 'id_entrenador')->where('id_empresa', Tenant::empresaId())],
        ]);
    }
}
