<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreProductRequest;
use App\Http\Requests\Master\UpdateProductRequest;
use App\Http\Resources\Master\ProductResource;
use App\Models\Master\Product;
use Exception;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $products = Product::query()
                ->when($request->yearId, fn($query) => $query->where('yearId', $request->yearId))
                ->when($request->institutionId, fn($query) => $query->where('institutionId', $request->institutionId))
                ->when($request->gender, fn($query) => $query->where('gender', $request->gender))
                ->when($request->list === 'table', fn($query) => $query->with(['program', 'boarding']));

            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => ProductResource::collection($products->get())
            ]);
        } catch (Exception $exception) {
            return response([
                'status' => 'error',
                'statusMessage' => $exception->getMessage(),
            ], 500);
        }
    }

    public function store(StoreProductRequest $request)
    {
        try {
            return ($product = Product::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Item berhasil ditambahkan.',
                    'result' => new ProductResource($product)
                ]) : throw new Exception('Data Item gagal ditambahkan.');
        } catch (Exception $exception) {
            return response([
                'status' => 'error',
                'statusMessage' => $exception->getMessage(),
            ], 422);
        }
    }

    public function show(Product $product)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new ProductResource($product)
            ]);
        } catch (Exception $exception) {
            return response([
                'status' => 'error',
                'statusMessage' => $exception->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            return $product->update(array_filter($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Item berhasil diperbarui.',
                    'result' => new ProductResource($product)
                ]) : throw new Exception('Data Item gagal diperbarui.');
        } catch (Exception $exception) {
            return response([
                'status' => 'error',
                'statusMessage' => $exception->getMessage(),
            ], 422);
        }
    }

    public function destroy(Product $product)
    {
        try {
            return $product->delete()
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Item berhasil dihapus.',
                    'result' => new ProductResource($product)
                ]) : throw new Exception('Data Item gagal dihapus.');
        } catch (Exception $exception) {
            return response([
                'status' => 'error',
                'statusMessage' => $exception->getMessage(),
            ], 422);
        }
    }
}
