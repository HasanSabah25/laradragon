<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('unauthenticated user cannot store a post', function () {
    $response = $this->post('/post');

    $response->assertStatus(302);
});


it('authenticated user can store a post', function () {

    $response = $this->actingAs($this->user)->post(route('post.store'), [
        'user_id' => $this->user->id,
        'title' => 'Sample Post',
        'body' => 'This is a sample post content.',
        'status' => 'published',
    ]);

    // Assert that the response status is 201 (created)
    $response->assertRedirect('/post');

    // Assert that the post is in the database
    $this->assertDatabaseHas('posts', [
        'user_id' => $this->user->id,
        'title' => 'Sample Post',
        'body' => 'This is a sample post content.',
        'status' => 'published',
    ]);
});

it('requires title body and status', function () {

    $this->actingAs($this->user)->post(route('post.store'), [])
        ->assertSessionHasErrors([
            'title',
            'body',
            'status'
        ]);
});

it('authenticated user can visit create post page', function () {
    $response = $this->actingAs($this->user)->get('/post/create');

    $response->assertStatus(200);
});

it('unauthenticated user cannot visit create post page', function () {
    $response = $this->get('/post/create');

    $response->assertStatus(302);
});


it('create post has title body status', function () {
    $response = $this->actingAs($this->user)->get('/post/create');


    $response->assertSee('Title');
    $response->assertSee('Body');
    $response->assertSee('Status');
});
