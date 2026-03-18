<?php

use App\Models\User;

it('redirects guests to the login page from root', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login', absolute: false));
});

it('redirects authenticated users to the dashboard from root', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertRedirect(route('dashboard', absolute: false));
});

it('redirects guests to the login page from dashboard', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect(route('login', absolute: false));
});

it('redirects guests to the login page from profile', function () {
    $response = $this->get('/profile');

    $response->assertRedirect(route('login', absolute: false));
});
