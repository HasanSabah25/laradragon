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

it('has post edit page', function () {
    $response = $this->actingAs($this->user)->get('post/' . $this->post->id . '/edit');

    $response->assertStatus(200);
});

it('has post details in form', function () {
    $response = $this->actingAs($this->user)->get('post/' . $this->post->id . '/edit');

    $response->assertSee($this->post->title);
    $response->assertSee($this->post->body);
    $response->assertSee($this->post->status->value);
});

it('redirects unauthenticated user', function () {
    $response = $this->get('post/' . $this->post->id . '/edit');
    $response->assertStatus(302);
});

it('abort if the user does not own the post', function () {
    $guestUser = User::factory()->create();
    $post = $this->user->posts()->create([
        'title' => 'title test',
        'body' => 'body test',
        'status' => 'pending',
    ]);

    $this->actingAs($guestUser)->get('post/' . $post->id . '/edit')->assertStatus(403);
});
