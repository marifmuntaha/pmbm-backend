<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $users = new User();
            $users = $request->has('institutionId') ? $users->whereInstitutionid($request->institutionId) : $users;
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => UserResource::collection($users->get())
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreUserRequest $request)
    {
        try {
            return ($user = User::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Pengguna berhasil ditambahkan.',
                    'result' => new UserResource($user)
                ]) : throw new Exception('Data Pengguna gagal ditambahkan.');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(User $user)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new UserResource($user)
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            return $user->update(array_filter($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Pengguna berhasil diperbarui.',
                    'result' => new UserResource($user)
                ]) : throw new Exception('Data Pengguna gagal diperbarui.');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(User $user)
    {
        try {
            return $user->delete()
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Pengguna berhasil dihapus.',
                    'result' => new UserResource($user)
                ]) : throw new Exception('Data Pengguna gagal dihapus.');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
}
