<?php

use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Abre una sesión de caja para el usuario en la caja de su sucursal (o la primera de su empresa).
 */
function abrirCaja(User $user, float $monto = 100): CajaSesion
{
    $caja = Caja::withoutGlobalScopes()
        ->where('id_empresa', $user->id_empresa)
        ->when($user->id_sucursal, fn ($q) => $q->where('id_sucursal', $user->id_sucursal))
        ->firstOrFail();

    return CajaSesion::withoutGlobalScopes()->create([
        'id_empresa' => $caja->id_empresa,
        'id_sucursal' => $caja->id_sucursal,
        'id_caja' => $caja->id_caja,
        'user_id' => $user->id,
        'monto_inicial' => $monto,
        'abierta_en' => now(),
        'estado' => 'abierta',
    ]);
}
