<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    //$this->post = Post::factory()->create();
    $this->post = $this->user->posts()->create([
        'title' => 'title test',
        'body' => 'body test',
        'status' => 'pending',
    ]);
});

it('has post update route exists', function () {

    $post = $this->user->posts()->create([
        'title' => 'title update',
        'body' => 'body update',
        'status' => 'pending',
    ]);
    $response = $this->actingAs($this->user)->put('post/' . $post->id, [
        'title' => 'new title',
        'body' => 'new body',
        'status' => 'published',
    ]);

    $response->assertRedirect('/post');
});

it('redirects unauthenticated user', function () {
    $response = $this->put('post/' . $this->post->id);
    $response->assertStatus(302);
});

it('validate post details', function () {
    $post = $this->user->posts()->create([
        'title' => 'title update',
        'body' => 'body update',
        'status' => 'pending',
    ]);
    $this->actingAs($this->user)->put('post/' . $post->id)->assertSessionHasErrors(['title', 'body', 'status']);
});


it('abort if the user does not own the post', function () {
    $guestUser = User::factory()->create();
    $post = $this->user->posts()->create([
        'title' => 'title test',
        'body' => 'body test',
        'status' => 'pending',
    ]);

    $this->actingAs($guestUser)->put('post/' . $post->id)->assertStatus(403);
});


it('can update the post', function () {

    $post = $this->user->posts()->create([
        'title' => 'title',
        'body' => 'body',
        'status' => 'pending',
    ]);
    $this->actingAs($this->user)->put('post/' . $post->id, [
        'title' => 'new title',
        'body' => 'new body',
        'status' => 'published',
    ])->assertRedirect('/post');

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'new title',
        'body' => 'new body',
        'status' => 'published',
    ]);
});
