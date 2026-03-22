<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Jobs\UpdateProductQtyJob;
use Illuminate\Support\Facades\Route;
use Goutte\Client; // deprecated for php 8.2
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
//composer require symfony/browser-kit symfony/http-client symfony/css-selector
Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-job', function () {
    UpdateProductQtyJob::dispatch();
    return response()->json(['success']);
});

Route::get('/users', function () {
    return view('welcome');
});

Route::get('/test', function () {
    $browser = new HttpBrowser(HttpClient::create());

    // Step 1: Access the target page
    $crawler = $browser->request('GET', 'https://obdadvisor.com/codes/');

    // Step 2: Select the form and fill in the code
    $form = $crawler->filter('form.search-form')->form([
        'keyword' => 'P0115', // Match the input field's "name" attribute
    ]);

    // Step 3: Submit the form
    $crawler = $browser->submit($form);

    // Step 4: Extract relevant data from the response
    $data = $crawler->filter('.code-title')->each(function (Crawler $node) {
        return $node->text();
    });

    // Return the extracted data
    return response()->json(['data' => $data]);
});



Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('post', PostController::class)->except('show', 'edit', 'update');
    Route::get('/post/{post}/edit', [PostController::class, 'edit'])->name('post.edit');
    Route::put('/post/{post}', [PostController::class, 'update'])->name('post.update');

    Route::get('product', [ProductController::class, 'index'])->name('product.index');
    Route::middleware('is_admin')->group(function () {
        Route::get('product/create', [ProductController::class, 'create'])->name('product.create');
        Route::post('product', [ProductController::class, 'store'])->name('product.store');
        Route::get('product/{product}/edit', [ProductController::class, 'edit'])->name('product.edit');
        Route::put('product/{product}', [ProductController::class, 'update'])->name('product.update');
        Route::delete('product/{product}', [ProductController::class, 'destroy'])->name('product.destroy');
    });
});

require __DIR__ . '/auth.php';
