<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreRoomRequest;
use App\Http\Requests\Master\UpdateRoomRequest;
use App\Http\Resources\Master\RoomResource;
use App\Models\Master\Room;
use Exception;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        try {
            $rooms = Room::query();
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => RoomResource::collection($rooms->get())
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreRoomRequest $request)
    {
        try {
            return ($room = Room::create($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data kamar berhasil disimpan',
                    'result' => new RoomResource($room)
                ]) : throw new Exception('Data kamar gagal disimpan');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Room $room)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new RoomResource($room)
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateRoomRequest $request, Room $room)
    {
        try {
            return $room->update(array_filter($request->all()))
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data kamar berhasil diperbarui',
                    'result' => new RoomResource($room)
                ]) : throw new Exception('Data kamar gagal diperbarui');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Room $room)
    {
        try {
            return $room->delete()
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data kamar berhasil dihapus',
                    'result' => new RoomResource($room)
                ]) : throw new Exception('Data kamar gagal dihapus');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage(),
            ], 422);
        }
    }
}
