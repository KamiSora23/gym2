<?php
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;



Route::get('/shop', function () {
    return view('shop');
});

Route::get('/carrito', function () {
    return view('carrito');
});

Route::get('/checkout', function () {
    return view('checkout');
});

Route::get('/registro', function () {
    return view('registro');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');


Route::get('/', function () {
   return view('index');
});

//Route::get('/dashboard', function () {
 //   return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});
Route::post('/configuracion', [ConfiguracionController::class, 'store'])
    ->name('configuracion.store');

require __DIR__.'/auth.php';
