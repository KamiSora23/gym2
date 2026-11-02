<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register as clients', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/');

    $user = User::where('email', 'test@example.com')->first();

    expect($user->isCliente())->toBeTrue()
        ->and($user->cliente)->not->toBeNull()
        ->and($user->cliente->dni)->toBe('TEMP-'.$user->id);
});
