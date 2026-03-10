<?php

namespace App\Http\Controllers\Institution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Institution\ProgramRequest;
use App\Http\Resources\Institution\ProgramResource;
use App\Models\Institution\Program;
use Exception;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        try {
            $programs = new Program();
            $programs = $request->has('yearId') ? $programs->whereYearid($request->yearId) : $programs;
            $programs = $request->has('institutionId') ? $programs->whereInstitutionid($request->institutionId) : $programs;
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => ProgramResource::collection($programs->get())
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }
    public function store(ProgramRequest $request)
    {
        try {
            return ($program = Program::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Program Berhasil Disimpan',
                    'result' => new ProgramResource($program)
                ]) : throw new Exception("Data Program Gagal Disimpan");
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Program $program)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new ProgramResource($program)
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(ProgramRequest $request, Program $program)
    {
        try {
            return $program->update(array_filter($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Program Berhasil Diperbarui.',
                    'result' => new ProgramResource($program)
                ]) : throw new Exception("Data Program Gagal Diperbarui");
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Program $program)
    {
        try {
            return $program->delete()
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Program Berhasil Dihapus.',
                    'result' => new ProgramResource($program)
                ]) : throw new Exception("Data Program Gagal Dihapus");
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
}
