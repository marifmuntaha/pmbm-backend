<?php

namespace App\Http\Controllers\Institution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Institution\PeriodRequest;
use App\Http\Resources\Institution\PeriodResource;
use App\Models\Institution\Period;
use Exception;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    public function index(Request $request)
    {
        try {
            $periods = new Period();
            $periods = $request->has('yearId') ? $periods->whereYearid($request->yearId) : $periods;
            $periods = $request->has('institutionId') ? $periods->whereInstitutionid($request->institutionId) : $periods;
            $periods = $request->has('date')
                ? $periods->whereDate('start', '<=', $request->date)->whereDate('end', '>=', $request->date)
                : $periods;
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => PeriodResource::collection($periods->get())
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }
    public function store(PeriodRequest $request)
    {
        try {
            return ($period = Period::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Period berhasil ditambahkan',
                    'result' => new PeriodResource($period)
                ]) : throw new Exception('Data Period gagal ditambahkan');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Period $period)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new PeriodResource($period)
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
    public function update(PeriodRequest $request, Period $period)
    {
        try {
            return $period->update(array_filter($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Period berhasil diperbarui',
                    'result' => new PeriodResource($period)
                ]) : throw new Exception('Data Period gagal diperbarui');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
    public function destroy(Period $period)
    {
        try {
            return $period->delete()
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Period berhasil dihapus',
                    'result' => new PeriodResource($period)
                ]) : throw new Exception('Data Period gagal dihapus');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
}
