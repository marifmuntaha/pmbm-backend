<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTestimonyRequest;
use App\Http\Resources\TestimonyResource;
use App\Models\Testimony;
use Exception;
use Illuminate\Http\Request;

class TestimonyController extends Controller
{
    public function index(Request $request)
    {
        try {
            $testimonies = new Testimony();
            $testimonies = $request->has('limit')
                ? $testimonies->limit($request->limit)->orderBy('created_at', 'desc')
                : $testimonies;
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => TestimonyResource::collection($testimonies->get())
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreTestimonyRequest $request)
    {
        try {
            return ($testimony = Testimony::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Testimoni Berhasil Disimpan',
                    'result' => new TestimonyResource($testimony)
                ]) : throw new Exception('Data Testimoni Gagal Disimpan');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Testimony $testimony)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new TestimonyResource($testimony)
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(StoreTestimonyRequest $request, Testimony $testimony)
    {
        try {
            return $testimony->update(array_filter($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Testimoni Berhasil Disimpan',
                    'result' => new TestimonyResource($testimony)
                ]) : throw new Exception('Data Testimoni Gagal Disimpan');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Testimony $testimony)
    {
        try {
            return $testimony->delete()
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Testimoni Berhasil Dihapus',
                    'result' => new TestimonyResource($testimony)
                ]) : throw new Exception('Data Testimoni Gagal Dihapus');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
}
