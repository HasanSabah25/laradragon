<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Jobs\UpdateProductQtyJob;
use App\Models\Product;
use Goutte\Client; // deprecated for php 8.2
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
//composer require symfony/browser-kit symfony/http-client symfony/css-selector
Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-job', function () {
    UpdateProductQtyJob::dispatch();
    return response()->json(['success']);
});

Route::get('/test-cache', function () {
    // 1. Clear everything first
    Cache::forget('all_products');

    // 2. Measure Database Speed (First time)
    $start = microtime(true);
    $products = Cache::remember('all_products', 600, function () {
        return DB::table('products')->select('id', 'name')->get();
    });
    $timeDb = (microtime(true) - $start) * 1000;

    // 3. Measure Cache Speed (Second time - Hits Redis/Memurai)
    $start = microtime(true);
    $productsFromCache = Cache::get('all_products');
    $timeCache = (microtime(true) - $start) * 1000;

    return response()->json([
        'db_hit_time' => $timeDb . ' ms',
        'cache_hit_time' => $timeCache . ' ms',
        'products_count' => $products->count(),
        'improvement' => round(($timeDb / $timeCache), 2) . 'x faster'
    ]);
});

Route::get('/stress-test', function () {
    $start = microtime(true);

    for ($i = 0; $i < 1000; $i++) {
        // We simulate a user hitting a specific product cache
        Cache::get('all_products'); 
    }

    return (microtime(true) - $start) * 1000 . " ms for 1,000 reads";
});

Route::get('/stress-test-final', function () {
    // 1. Create a "Real World" result: An array of 1,000 simple points (ID + Price)
    // This is much smaller than 11,000 full Eloquent objects.
    $data = [];
    for ($i = 0; $i < 500; $i++) {
        $data[] = ['id' => $i, 'value' => rand(100, 1000)];
    }

    // 2. Pre-fill the cache so we only measure the READ speed
    Cache::put('dashboard_chart', $data, 600);

    $start = microtime(true);

    // 3. Simulate 1,000 users hitting this dashboard chart
    for ($i = 0; $i < 1000; $i++) {
        Cache::get('dashboard_chart'); 
    }

    $totalTime = (microtime(true) - $start) * 1000;

    return response()->json([
        'driver' => config('cache.default'),
        'total_time' => round($totalTime, 2) . ' ms',
        'avg_per_read' => round($totalTime / 1000, 4) . ' ms',
        'items_in_array' => count($data)
    ]);
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
