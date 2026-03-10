<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreDetailRequest;
use App\Http\Requests\Invoice\UpdateDetailRequest;
use App\Http\Resources\Invoice\DetailResource;
use App\Models\Invoice\Detail;
use Exception;
use Illuminate\Http\Request;

class DetailController extends Controller
{
    public function index(Request $request)
    {
        try {
            $details = $request->has('invoiceId') ? Detail::where('invoiceId', $request->invoiceId) : new Detail();
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => DetailResource::collection($details->get())
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreDetailRequest $request)
    {
        try {
            return ($detail = Detail::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Detail has been created.',
                    'result' => new DetailResource($detail)
                ]) : throw new Exception('Unable to create detail.');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Detail $detail)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new DetailResource($detail)
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(UpdateDetailRequest $request, Detail $detail)
    {
        try {
            return $detail->update(array_filter($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Detail has been updated.',
                    'result' => new DetailResource($detail)
                ]) : throw new Exception('Unable to update detail.');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
    public function destroy(Detail $detail)
    {
        try {
            return $detail->delete()
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Detail has been deleted.',
                    'result' => new DetailResource($detail)
                ]) : throw new Exception('Unable to delete detail.');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
}
