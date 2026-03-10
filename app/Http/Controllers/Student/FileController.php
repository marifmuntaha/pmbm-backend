<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreFileRequest;
use App\Http\Requests\Student\UpdateFileRequest;
use App\Http\Resources\Student\FileResource;
use App\Models\Student\StudentFile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function index(Request $request)
    {
        try {
            $files = new StudentFile();
            $files = $request->has('userId') ? $files->whereUserid($request->userId) : $files;
            return response([
                'status' => "success",
                'statusMessage' => '',
                'result' => FileResource::collection($files->get())
            ]);
        } catch (Exception $e) {
            return response([
                'status' => "error",
                'statusMessage' => $e->getMessage()
            ], 500);
        }
    }
    public function store(StoreFileRequest $request)
    {
        try {
            if ($request->hasFile('imagePhoto')) {
                $path = Storage::disk('public')
                    ->putFileAs('images/files', $request->file('imagePhoto'), $request->file('imagePhoto')->hashName());
                $request->merge(['filePhoto' => $path]);
            }
            if ($request->hasFile('imageKk')) {
                $path = Storage::disk('public')
                    ->putFileAs('images/files', $request->file('imageKk'), $request->file('imageKk')->hashName());
                $request->merge(['fileKk' => $path]);
            }
            if ($request->hasFile('imageKtp')) {
                $path = Storage::disk('public')
                    ->putFileAs('images/files', $request->file('imageKtp'), $request->file('imageKtp')->hashName());
                $request->merge(['fileKtp' => $path]);
            }
            if ($request->hasFile('imageAkta')) {
                $path = Storage::disk('public')
                    ->putFileAs('images/files', $request->file('imageAkta'), $request->file('imageAkta')->hashName());
                $request->merge(['fileAkta' => $path]);
            }
            if ($request->hasFile('imageIjazah')) {
                $path = Storage::disk('public')
                    ->putFileAs('images/files', $request->file('imageIjazah'), $request->file('imageIjazah')->hashName());
                $request->merge(['fileIjazah' => $path]);
            }
            if ($request->hasFile('imageSkl')) {
                $path = Storage::disk('public')
                    ->putFileAs('images/files', $request->file('imageSkl'), $request->file('imageSkl')->hashName());
                $request->merge(['fileSkl' => $path]);
            }
            if ($request->hasFile('imageKip')) {
                $path = Storage::disk('public')
                    ->putFileAs('images/files', $request->file('imageKip'), $request->file('imageKip')->hashName());
                $request->merge(['fileKip' => $path]);
            }
            $data = $request->except(['imagePhoto', 'imageKk', 'imageKtp', 'imageAkta', 'imageIjazah', 'imageSkl', 'imageKip']);
            return ($file = StudentFile::create(array_filter($data, function($value) {
                return !is_null($value) && $value !== '';
            })))
                ? response([
                    'status' => "success",
                    'statusMessage' => "Berkas berhasil disimpan",
                    'result' => new FileResource($file)
                ]) : throw new Exception('Berkas gagal disimpan');
        } catch (Exception $e) {
            return response([
                'status' => "error",
                'statusMessage' => $e->getMessage()
            ], 422);
        }
    }
    public function show(StudentFile $file)
    {
        try {
            return response([
                'status' => "success",
                'statusMessage' => '',
                'result' => new FileResource($file)
            ]);
        } catch (Exception $e) {
            return response([
                'status' => "error",
                'statusMessage' => $e->getMessage()
            ], 500);
        }
    }
    public function update(UpdateFileRequest $request, StudentFile $file)
    {
        try {
            if ($request->hasFile('imagePhoto')) {
                Storage::disk('public')->delete($file->getRawOriginal('filePhoto'));
                $path = Storage::disk('public')
                    ->putFileAs('images/files', $request->file('imagePhoto'), $request->file('imagePhoto')->hashName());
                $request->merge(['filePhoto' => $path]);
            }
            if ($request->hasFile('imageKk')) {
                Storage::disk('public')->delete($file->getRawOriginal('fileKk'));
                $path = Storage::disk('public')
                    ->putFileAs('images/files', $request->file('imageKk'), $request->file('imageKk')->hashName());
                $request->merge(['fileKk' => $path]);
            }
            if ($request->hasFile('imageKtp')) {
                Storage::disk('public')->delete($file->getRawOriginal('fileKtp'));
                $path = Storage::disk('public')
                    ->putFileAs('images/files', $request->file('imageKtp'), $request->file('imageKtp')->hashName());
                $request->merge(['fileKtp' => $path]);
            }
            if ($request->hasFile('imageAkta')) {
                Storage::disk('public')->delete($file->getRawOriginal('fileAkta'));
                $path = Storage::disk('public')
                    ->putFileAs('images/files', $request->file('imageAkta'), $request->file('imageAkta')->hashName());
                $request->merge(['fileAkta' => $path]);
            }
            if ($request->hasFile('imageIjazah')) {
                if ($file->fileIjazah != null) {
                    Storage::disk('public')->delete($file->getRawOriginal('fileIjazah'));
                }
                $path = Storage::disk('public')
                    ->putFileAs('images/files', $request->file('imageIjazah'), $request->file('imageIjazah')->hashName());
                $request->merge(['fileIjazah' => $path]);
            }
            if ($request->hasFile('imageSkl')) {
                if ($file->fileSkl != null) {
                    Storage::disk('public')->delete($file->getRawOriginal('fileSkl'));
                }
                $path = Storage::disk('public')
                    ->putFileAs('images/files', $request->file('imageSkl'), $request->file('imageSkl')->hashName());
                $request->merge(['fileSkl' => $path]);
            }
            if ($request->hasFile('imageKip')) {
                if ($file->fileKip != null) {
                    Storage::disk('public')->delete($file->getRawOriginal('fileKip'));
                }
                $path = Storage::disk('public')
                    ->putFileAs('images/files', $request->file('imageKip'), $request->file('imageKip')->hashName());
                $request->merge(['fileKip' => $path]);
            }
            $data = $request->except(['imagePhoto', 'imageKk', 'imageKtp', 'imageAkta', 'imageIjazah', 'imageSkl', 'imageKip']);
            return $file = $file->update(array_filter($data, function($value) {
                return !is_null($value) && $value !== '';
            }))
                ? response([
                    'status' => "success",
                    'statusMessage' => "Berkas berhasil diperbarui",
                    'result' => new FileResource($file)
                ]) : throw new Exception('Berkas gagal diperbarui');
        } catch (Exception $e) {
            return response([
                'status' => "error",
                'statusMessage' => $e->getMessage()
            ], 422);
        }
    }
    public function destroy(StudentFile $file)
    {
        try {
            Storage::disk('public')->delete($file->getRawOriginal('fileKk'));
            Storage::disk('public')->delete($file->getRawOriginal('fileKtp'));
            Storage::disk('public')->delete($file->getRawOriginal('fileAkta'));
            Storage::disk('public')->delete($file->getRawOriginal('fileIjazah'));
            Storage::disk('public')->delete($file->getRawOriginal('fileSkl'));
            Storage::disk('public')->delete($file->getRawOriginal('fileKip'));
            return $file->delete()
                ? response([
                    'status' => "success",
                    'statusMessage' => "Berkas berhasil dihapus",
                    'result' => new FileResource($file)
                ]) : throw new Exception('Berkas gagal dihapus');
        } catch (Exception $e) {
            return response([
                'status' => "error",
                'statusMessage' => $e->getMessage()
            ], 422);
        }
    }
}
