<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\ProductUnit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::self()->active()->latest('id')->paginate(20)->withQueryString();

        return view('seller.products.index', compact('products'));
    }

    public function create()
    {
        $units = ProductUnit::get();
        $categories = ProductCategory::get();

        return view('seller.products.create', compact('units', 'categories'));
    }

    public function store(Request $request)
    {
        $input = $request->validate([
            'category_id' => 'required',
            'unit_id' => 'required',
            'name' => 'required|min:2',
            'buying_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock_in' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
        ]);

        $input['seller_id'] = auth()->id();

        if ($request->hasFile('image')) {

            $input['image'] = upload_file($request->file('image'), 'images/products');
        }

        $product = Product::create($input);

        ProductStock::create([
            'product_id' => $product->id,
            'seller_id' => $product->seller_id,
            'type' => 'increment',
            'quantity' => $request->stock_in,
            'old_stock' => 0,
            'new_stock' => $request->stock_in,
            'buying_price' => $request->buying_price,
            'selling_price' => $request->selling_price,
        ]);

        return redirect()->back()->with('success', 'Product Created');
    }

    public function edit(Product $product)
    {
        $units = ProductUnit::get();
        $categories = ProductCategory::get();

        return view('seller.products.edit', compact('product', 'units', 'categories'));
    }

    public function update(Product $product, Request $request)
    {
        $input = $request->validate([
            'category_id' => 'required',
            'unit_id' => 'required',
            'name' => 'required|min:2',
            'buying_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock_in' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image != null) {
                delete_file($product->image);
            }

            $input['image'] = upload_file($request->file('image'), 'images/products');
        }

        $this->updateStock($product, $request);

        $product->update($input);

        return redirect()->back()->with('success', 'Product Updated');
    }

    private function updateStock($product, $request)
    {
        $newStock = $request->stock_in;
        $oldStockQuantity = $product->stock_in;

        if ($newStock == $oldStockQuantity) {
            return;
        }

        $productStock = new ProductStock;
        $productStock->seller_id = $product->seller_id;
        $productStock->product_id = $product->id;
        $productStock->old_stock = $oldStockQuantity;
        $productStock->buying_price = $request->buying_price;
        $productStock->selling_price = $request->selling_price;
        $productStock->new_stock = $newStock;

        if ($newStock > $oldStockQuantity) {
            $productStock->type = 'increment';
            $productStock->quantity = $newStock - $oldStockQuantity;
        }

        if ($newStock < $oldStockQuantity) {
            $productStock->type = 'decrement';
            $productStock->quantity = $oldStockQuantity - $newStock;
        }

        $productStock->save();
    }

    public function delete(Product $product)
    {
        $product->is_active = false;
        $product->save();

        return redirect()->back()->with('success', 'Product Deleted');
    }
}
