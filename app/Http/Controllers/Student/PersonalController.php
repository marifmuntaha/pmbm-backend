<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StorePersonalRequest;
use App\Http\Requests\Student\UpdatePersonalRequest;
use App\Http\Resources\Student\PersonalResource;
use App\Models\Student\StudentPersonal;
use Exception;
use Illuminate\Http\Request;

class PersonalController extends Controller
{
    public function index(Request $request)
    {
        try {
            $personals = new StudentPersonal();
            $personals = $request->has('userId') ? $personals->whereUserid($request->userId) : $personals;
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => PersonalResource::collection($personals->get()),
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StorePersonalRequest $request)
    {
        try {
            return ($personal = StudentPersonal::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Pribadi berhasil disimpan.',
                    'result' => new PersonalResource($personal),
                ]) : throw new Exception('Data Pribadi gagal disimpan.');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(StudentPersonal $personal)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new PersonalResource($personal),
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdatePersonalRequest $request, StudentPersonal $personal)
    {
        try {
            return $personal->update(array_filter($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Pribadi berhasil diperbarui.',
                    'result' => new PersonalResource($personal),
                ]) : throw new Exception('Data Pribadi gagal diperbarui.');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(StudentPersonal $personal)
    {
        try {
            return $personal->delete()
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Pribadi berhasil dihapus.',
                    'result' => new PersonalResource($personal),
                ]) : throw new Exception('Data Pribadi gagal dihapus.');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
}
