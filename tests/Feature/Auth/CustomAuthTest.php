<?php

use App\Models\User;


test('unauthenticated user cannot see product page', function () {
    $response = $this->get('/product');

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
});
