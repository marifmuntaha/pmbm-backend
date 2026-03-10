<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreYearRequest;
use App\Http\Requests\Master\UpdateYearRequest;
use App\Http\Resources\Master\YearResource;
use App\Models\Master\Year;
use Exception;
use Illuminate\Http\Request;

class YearController extends Controller
{
    public function index(Request $request)
    {
        try {
            $years = new Year();
            $years = $request->has('active') ? $years->whereActive(true) : $years;
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => YearResource::collection($years->get())
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreYearRequest $request)
    {
        try {
            return ($year = Year::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data tahun pelajaran berhasil disimpan',
                    'result' => new YearResource($year)
                ]) : throw new Exception('Data tahun pelajaran gagal disimpan');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Year $year)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new YearResource($year)
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateYearRequest $request, Year $year)
    {
        try {
            return $year->update(array_filter($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data tahun pelajaran berhasil diperbarui',
                    'result' => new YearResource($year)
                ]) : throw new Exception('Data tahun pelajaran gagal diperbarui');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Year $year)
    {
        try {
            return $year->delete()
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data tahun pelajaran berhasil dihapus',
                    'result' => new YearResource($year)
                ]) : throw new Exception('Data Tahun pelajaran gagal dihapus');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
}
