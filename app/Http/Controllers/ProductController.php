<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    // GET /api/products - List all products
    public function index()
    {
        return response()->json(Product::all(), 200);
    }

    // POST /api/products - Create a new product
    public function store(ProductRequest $request)
    {
        $product = Product::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'user_id' => auth()->id(), // Associate the product with the authenticated user
        ]);

        return response()->json($product, 201);
    }

    // GET /api/products/{id} - Show product details
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product, 200);
    }

    // PUT /api/products/{id} - Update a product
    public function update(ProductRequest $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $this->authorize('update', $product);

        $product->update($request->validated());

        return response()->json($product, 200);
    }

    // DELETE /api/products/{id} - Delete a product
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $this->authorize('delete', $product);

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }

    // GET /api/products/search - custom search
    public function search(Request $request)
    {
        $products = Product::where('name', 'like', '%' . $request->query('name') . '%')->get();

        return response()->json($products, 200);
    }
}
