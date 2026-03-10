<?php

namespace App\Http\Controllers\Institution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Institution\ActivityRequest;
use App\Http\Resources\Institution\ActivityResource;
use App\Models\Institution\Activity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        try {
            $activities = new Activity();
            $activities = $request->has('institutionId') ? $activities->whereInstitutionid($request->institutionId) : $activities;
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => ActivityResource::collection($activities->get())
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }
    public function store(ActivityRequest $request)
    {
        try {
            if ($request->hasFile('file')) {
                $path = Storage::disk('public')->putFileAs('brochure', $request->file('file'), $request->file('file')->hashName());
                $request->merge(['brochure' => $path]);
            }
            return ($activity = Activity::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Aktifitas Berhasil Disimpan',
                    'result' => new ActivityResource($activity)
                ]) : throw new Exception("Data Aktifitas Gagal Disimpan");
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Activity $activity)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new ActivityResource($activity)
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(ActivityRequest $request, Activity $activity)
    {
        try {
            if ($request->hasFile('file')) {
                Storage::disk('public')->delete($activity->getRawOriginal('brochure'));
                $path = Storage::disk('public')->putFileAs('brochure', $request->file('file'), $request->file('file')->hashName());
                $request->merge(['brochure' => $path]);
            }
            return $activity->update(array_filter($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Aktifitas Berhasil Diperbarui.',
                    'result' => new ActivityResource($activity)
                ]) : throw new Exception("Data Aktifitas Gagal Diperbarui");
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Activity $activity)
    {
        try {
            Storage::disk('public')->delete($activity->getRawOriginal('brochure'));
            return $activity->delete()
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Aktifitas Berhasil Dihapus.',
                    'result' => new ActivityResource($activity)
                ]) : throw new Exception("Data Aktifitas Gagal Dihapus");
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
}
