<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreBoardingRequest;
use App\Http\Requests\Master\UpdateBoardingRequest;
use App\Http\Resources\Master\BoardingResource;
use App\Models\Master\Boarding;
use Exception;
//use Illuminate\Http\Request;
class BoardingController extends Controller
{
    public function index()
    {
        try {
            $boardings = new Boarding();
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => BoardingResource::collection($boardings->get())
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }
    public function store(StoreBoardingRequest $request)
    {
        try {
            return ($boarding = Boarding::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Boarding berhasil disimpan',
                    'result' => new BoardingResource($boarding)
                ]) : throw new Exception('Data Boarding gagal disimpan');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
    public function show(Boarding $boarding)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new BoardingResource($boarding)
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(UpdateBoardingRequest $request, Boarding $boarding)
    {
        try {
            return $boarding->update(array_filter($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Boarding berhasil diperbarui',
                    'result' => new BoardingResource($boarding)
                ]) : throw new Exception('Data Boarding gagal diperbarui');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
    public function destroy(Boarding $boarding)
    {
        try {
            return $boarding->delete()
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Boarding berhasil dihapus',
                    'result' => new BoardingResource($boarding)
                ]) : throw new Exception('Data Boarding gagal dihapus');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
}
