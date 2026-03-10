<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreAchievementRequest;
use App\Http\Requests\Student\UpdateAchievementRequest;
use App\Http\Resources\Student\AchievementResource;
use App\Models\Student\StudentAchievement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AchievementController extends Controller
{
    public function index(Request $request)
    {
        try {
            $achievements = new StudentAchievement();
            $achievements = $request->has('userId') ? $achievements->whereUserid($request->userId) : $achievements;
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => AchievementResource::collection($achievements->get())
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreAchievementRequest $request)
    {
        try {
            if ($request->hasFile('image')) {
                $path = Storage::disk('public')
                    ->putFileAs('images/achievement', $request->file('image'), $request->file('image')->hashName());
                $request->merge(['file' => $path]);
            }
            return ($achievement = StudentAchievement::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Prestasi berhasil ditambahkan',
                    'result' => new AchievementResource($achievement)
                ]) : throw new Exception('Data Prestasi gagal ditambahkan');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 422);
        }
    }

    public function show(StudentAchievement $achievement)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new AchievementResource($achievement)
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateAchievementRequest $request, StudentAchievement $achievement)
    {
        try {
            if ($request->hasFile('image')) {
                Storage::disk('public')->delete($achievement->getRawOriginal('file'));
                $path = Storage::disk('public')
                    ->putFileAs('images/achievement', $request->file('image'), $request->file('image')->hashName());
                $request->merge(['file' => $path]);
            }
            return $achievement->update($request->all())
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Prestasi berhasil diperbarui',
                    'result' => new AchievementResource($achievement)
                ]) : throw new Exception('Data Prestasi gagal diperbarui');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(StudentAchievement $achievement)
    {
        try {
            Storage::disk('public')->delete($achievement->getRawOriginal('file'));
            return $achievement->delete()
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Prestasi berhasil dihapus',
                    'result' => new AchievementResource($achievement)
                ]) : throw new Exception('Data Prestasi gagal dihapus');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 422);
        }
    }
}
