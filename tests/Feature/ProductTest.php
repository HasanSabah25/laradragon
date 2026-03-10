<?php

use App\Models\Post;
use App\Models\Product;
use function Pest\Laravel\assertDatabaseHas;

use Illuminate\Testing\Fluent\AssertableJson;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;
use Illuminate\Foundation\Testing\RefreshDatabase;

//uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = createUser();
    $this->admin = createUser(isAdmin: true);
});

it('has empty product in product page', function () {
    $this->actingAs($this->user)
        ->get('/product')
        ->assertStatus(200)
        ->assertSee('No products found');
});

it('has product in product page', function () {

    $product = Product::create([
        'name' => 'product 1',
        'price' => '10000',
    ]);

    $this->actingAs($this->user)
        ->get('/product')
        ->assertStatus(200)
        ->assertDontSee('No products found')
        ->assertViewHas('products', function ($collection) use ($product) {
            return $collection->contains($product);
        });
});

it('has correct pagination in product page', function () {

    $products = Product::factory(11)->create();

    $lastProduct = $products->last();

    $this->actingAs($this->user)
        ->get('/product')
        ->assertStatus(200)
        ->assertViewHas('products', function ($collection) use ($lastProduct, $products) {
            return !$collection->contains($lastProduct) && $products->count() > 10;
        });
});

test('admin can see add new product button', function () {

    $this->actingAs($this->admin)
        ->get('/product')
        ->assertStatus(200)
        ->assertSee('Add New Poduct');
});

test('non admin cannot see add new product button', function () {
    $this->actingAs($this->user)
        ->get('/product')
        ->assertStatus(200)
        ->assertDontSee('Add New Product');
});


test('admin can access create product page', function () {

    $this->actingAs($this->admin)
        ->get('/product/create')
        ->assertStatus(200);
});

test('non admin cannot access create product page', function () {

    $this->actingAs($this->user)
        ->get('/product/create')
        ->assertStatus(403);
});

test('create product is successfull', function () {

    $product = [
        'name' => 'new product',
        'price' => 13000,
    ];

    $this->actingAs($this->admin)
        ->post('product', $product)
        ->assertRedirect(route('product.index'));

    assertDatabaseHas('products', $product);

    $lastProduct = Product::select('name', 'price')->latest()->first();

    expect($lastProduct->name)->tobe($product['name']);
    expect($lastProduct->price)->toEqual($product['price']);
});


test('edit product has correct values', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)
        ->get('product/' . $product->id . '/edit')
        ->assertStatus(200)
        ->assertSee('value="' . $product->name . '"', false)
        ->assertSee('value="' . number_format($product->price) . '"', false)
        ->assertViewHas('product', $product);
});

test('update product validation erros redirects back to form', function () {
    $product = Product::factory()->create();

    $response = $this->actingAs($this->admin)->put('product/' . $product->id, [
        'name' => '',
        'price' => '',
    ]);

    $response->assertStatus(302);
    $response->assertInvalid(['name', 'price']);
});

test('delete product successfull', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)
        ->delete('product/' . $product->id)
        ->assertStatus(302)
        ->assertRedirect('product');

    assertDatabaseMissing('products', $product->toArray());
    assertDatabaseCount('products', 0);
});


test('api returns products list', function () {
    Product::factory(20)->create();

    $products = Product::all();

    $response = $this->getJson('/api/product')
        ->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['id', 'name', 'price', 'created_at', 'updated_at']
            ],
            'meta' => [
                'current_page',
                'first_page_url',
                'next_page_url',
                'prev_page_url',
                'path',
                'per_page',
                'from',
                'to',
            ]
        ]);

    // $response->assertJson(function (AssertableJson $json) use ($products) {
    //     $json->where('status', true)
    //         ->where('data', [$products->toArray()]);
    // });
});


test('api store product successfull', function () {
    // Define the product data to be sent in the request
    $productData = [
        'name' => 'test product',
        'price' => 10000,
    ];

    // Send a POST request to create the product and assert the response
    $response = $this->postJson('/api/product', $productData)
        ->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => [
                'id', 'name', 'price', 'created_at', 'updated_at'
            ],
            'message'
        ]);

    // Get the response data
    $responseData = $response->json('data');

    // Ensure the product data in the response matches the input data
    $this->expect($productData['name'])->toBe($responseData['name']);
    $this->expect($productData['price'])->toBe($responseData['price']);

    // Check that the product exists in the database
    $this->assertDatabaseHas('products', [
        'id' => $responseData['id'],
        'name' => $productData['name'],
        'price' => $productData['price']
    ]);
});



test('api invalid store product return errors', function () {
    $product = [
        'name' => '',
        'price' => 10000,
    ];

    $response = $this->postJson('/api/product', $product);

    $response->assertStatus(422);
});


test('api show product', function () {
    $product = Product::factory()->create();

    $this->getJson('/api/product/' . $product->id)
        ->assertJson([
            'status' => true,
            'data' => $product->toArray()
        ]);
});


test('api update product successfull', function () {

    // Create a product using a factory
    $product = Product::factory()->create();

    // Data to update the product with
    $updatedData = [
        'name' => 'test product',
        'price' => 10000,
    ];

    // Send a PUT request to update the product and assert the response
    $this->putJson('/api/product/' . $product->id, $updatedData)
        ->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'product updated successfully.',
            'data' => [
                'id' => $product->id, // ID should remain the same
                'name' => $updatedData['name'], // Updated name
                'price' => $updatedData['price'], // Updated price
            ]
        ]);

    // Refresh the product instance to ensure it's updated in the database
    $product->refresh();

    // Assert the product data in the database matches the updated data
    $this->assertEquals($updatedData['name'], $product->name);
    $this->assertEquals($updatedData['price'], $product->price);
});


test('api invalid update product return errors', function () {

    $product = Product::factory()->create();

    // Data to update the product with
    $updatedData = [
        'name' => '',
        'price' => 10000,
    ];

    $this->putJson('/api/product/' . $product->id, $updatedData)
        ->assertStatus(422);
});

test('api delete product successfull', function () {
    $product = Product::factory()->create();

    $this->deleteJson('/api/product/' . $product->id)
        ->assertJson([
            'status' => true,
            'message' => 'product deleted successfully.'
        ]);
});