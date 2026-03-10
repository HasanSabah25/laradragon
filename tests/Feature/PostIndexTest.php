<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('has post index page', function () {
    $response = $this->actingAs($this->user)->get('/post');

    $response->assertStatus(200);
});

it('can we see new post', function () {
    $post = Post::factory()->create();
    $response = $this->actingAs($this->user)->get('/post');

    $response->assertSeeText($post->title)->assertSeeText($post->body);
});
