<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Product management
 *
 * APIs for managing products
 */
class ProductController extends Controller
{
    /**
     * Display all products.
     *
     * @queryParam page int The page number. Example: 1
     * @response {
     *  "status": true,
     *  "data": [
     *    {
     *      "id": 1,
     *      "name": "Product 1",
     *      "price": "1000.00",
     *      "created_at": "2024-06-21 19:42:39",
     *      "updated_at": "2024-06-21 19:42:39"
     *    }
     *  ],
     *  "meta": {
     *    "current_page": 1,
     *    "first_page_url": "http://localhost:8000/api/products?page=1",
     *    "next_page_url": null,
     *    "prev_page_url": null,
     *    "path": "http://localhost:8000/api/products",
     *    "per_page": 15,
     *    "from": 1,
     *    "to": 1
     *  }
     * }
     */
    public function index()
    {
        $products = Product::simplePaginate();
        return response()->json([
            'status' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'first_page_url' => $products->url(1),
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
                'path' => $products->path(),
                'per_page' => $products->perPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ]
        ]);
    }

    /**
     * Store a new product.
     *
     * @bodyParam name string required The name of the product. Example: new product
     * @bodyParam price float required The price of the product. Example: 13000
     * @response 200 {
     *  "status": true,
     *  "data": {
     *    "id": 1,
     *    "name": "new product",
     *    "price": "13000.00",
     *    "created_at": "2024-06-21 19:42:39",
     *    "updated_at": "2024-06-21 19:42:39"
     *  },
     *  "message": "product created successfully."
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required', 'price' => 'required|numeric']);

        $product = Product::create($validated);

        return response()->json([
            'status' => true,
            'data' => $product,
            'message' => 'product created successfully.',
        ]);
    }

    /**
     * Display a specified product.
     *
     * @urlParam id int required The ID of the product. Example: 1
     * @response {
     *  "status": true,
     *  "data": {
     *    "id": 1,
     *    "name": "Product 1",
     *    "price": "1000.00",
     *    "created_at": "2024-06-21 19:42:39",
     *    "updated_at": "2024-06-21 19:42:39"
     *  }
     * }
     */
    public function show(Product $product)
    {
        return response()->json([
            'status' => true,
            'data' => $product,
        ]);
    }

    /**
     * Update a product.
     *
     * @urlParam id int required The ID of the product. Example: 1
     * @bodyParam name string required The name of the product. Example: updated product
     * @bodyParam price float required The price of the product. Example: 15000
     * @response 200 {
     *  "status": true,
     *  "data": {
     *    "id": 1,
     *    "name": "updated product",
     *    "price": "15000.00",
     *    "created_at": "2024-06-21 19:42:39",
     *    "updated_at": "2024-06-21T11:30:00.000000Z"
     *  },
     *  "message": "product updated successfully."
     * }
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate(['name' => 'required', 'price' => 'required|numeric']);
        $validated['price'] = str_replace(',', '', $request->price);

        $product->update($validated);

        return response()->json([
            'status' => true,
            'data' => $product,
            'message' => 'product updated successfully.',
        ]);
    }

    /**
     * delete a product.
     *
     * @urlParam id int required The ID of the product. Example: 1
     * @response 200 {
     *  "status": true,
     *  "message": "product deleted successfully."
     * }
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'product deleted successfully.',
        ]);
    }
}