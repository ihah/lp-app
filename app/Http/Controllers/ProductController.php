<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return ProductResource::collection(
            Product::with(['warehouseStocks'])->simplePaginate(20)
        );
    }

    public function show(int $id)
    {
        return new ProductResource(Product::with(['warehouseStocks'])->findOrFail($id));
    }
}
