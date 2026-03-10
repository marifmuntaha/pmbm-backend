<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInstitutionRequest;
use App\Http\Resources\InstitutionResource;
use App\Models\Institution;
use Exception;
use Illuminate\Http\Request;
use Storage;

class InstitutionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $institutions = new Institution();
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => InstitutionResource::collection($institutions->get()),
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function store (StoreInstitutionRequest $request)
    {
        try {
            if ($request->hasFile('image')) {
                $path = Storage::disk('public')->putFileAs('images', $request->file('image'), $request->file('image')->hashName());
                $request->merge(['logo' => $path]);
            }
            return ($institution = Institution::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Lembaga berhasil ditambahkan',
                    'result' => new InstitutionResource($institution),
                ], 201) : throw new Exception("Data Lembaga gagal ditambahkan", 422);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Institution $institution)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new InstitutionResource($institution),
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, Institution $institution)
    {
        try {
            if ($request->hasFile('image')) {
                Storage::disk('public')->delete($institution->getRawOriginal('logo'));
                $path = Storage::disk('public')->putFileAs('images', $request->file('image'), $request->file('image')->hashName());
                $request->merge(['logo' => $path]);
            }
            return $institution->update(array_filter($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Lembaga berhasil diperbarui',
                    'result' => new InstitutionResource($institution),
                ]) : throw new Exception("Data Lembaga gagal diperbarui");
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Institution $institution)
    {
        try {
            Storage::disk('public')->delete($institution->getRawOriginal('logo'));
            return $institution->delete()
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Lembaga berhasil dihapus',
                    'result' => new InstitutionResource($institution),
                ]) : throw new Exception("Data Lembaga gagal dihapus");
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
}
