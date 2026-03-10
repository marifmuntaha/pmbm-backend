<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreDiscountRequest;
use App\Http\Requests\Master\UpdateDiscountRequest;
use App\Http\Resources\Master\DiscountResource;
use App\Models\Master\Discount;
use Exception;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index(Request $request)
    {
        try {
            $discounts = new Discount();
            $discounts = $request->has('institutionId') ? $discounts->whereInstitutionid($request->institutionId) : $discounts;
            return response()->json([
                'success' => 'success',
                'statusMessage' => '',
                'result' => DiscountResource::collection($discounts->get())
            ]);
        } catch (Exception $e) {
            return response([
                'success' => false,
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreDiscountRequest $request)
    {
        try {
            return ($discount = Discount::create($request->all()))
                ? response([
                    'success' => 'success',
                    'statusMessage' => 'Data Potongan berhasil ditambahkan',
                    'result' => new DiscountResource($discount)
                ]) : throw new Exception('Data Potongan gagal ditambahkan');
        } catch (Exception $e) {
            return response([
                'success' => false,
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Discount $discount)
    {
        try {
            return response([
                'success' => 'success',
                'statusMessage' => '',
                'result' => new DiscountResource($discount)
            ]);
        } catch (Exception $e) {
            return response([
                'success' => false,
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(UpdateDiscountRequest $request, Discount $discount)
    {
        try {
            return $discount->update(array_filter($request->all()))
                ? response([
                    'success' => 'success',
                    'statusMessage' => 'Data Potongan berhasil diperbarui',
                    'result' => new DiscountResource($discount)
                ]) : throw new Exception('Data Potongan gagal diperbarui');
        } catch (Exception $e) {
            return response([
                'success' => false,
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
    public function destroy(Discount $discount)
    {
        try {
            return $discount->delete()
                ? response([
                    'success' => 'success',
                    'statusMessage' => 'Data Potongan berhasil dihapus',
                    'result' => new DiscountResource($discount)
                ]) : throw new Exception('Data Potongan gagal dihapus');
        } catch (Exception $e) {
            return response([
                'success' => false,
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
}
