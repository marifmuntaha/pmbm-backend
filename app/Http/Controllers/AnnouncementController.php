<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\User;
use App\Jobs\SendWhatsAppMessage;
use Exception;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = Announcement::query();
            if ($request->has('yearId')) {
                $query->where('yearId', $request->yearId);
            }

            if ($user->role == 4) {
                $query->where(function ($q) use ($user) {
                    $q->where('type', 'global')
                        ->orWhere(function ($sub) use ($user) {
                            $sub->where('type', 'institution')
                                ->where('institutionId', $user->institutionId);
                        })
                        ->orWhere(function ($sub) use ($user) {
                            $sub->where('type', 'specific')
                                ->where('user_id', $user->id);
                        });
                });
            } else { // Admin or Operator
                if ($user->role == 2) { // Operator
                    $query->where(function ($q) use ($user) {
                        $q->where('institutionId', $user->institutionId)
                            ->orWhere('type', 'global');
                    });
                }

                // Admin sees all, but can filter by institutionId if provided
                if ($request->has('institutionId')) {
                    $query->where('institutionId', $request->institutionId);
                }
            }

            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => AnnouncementResource::collection($query->orderBy('created_at', 'desc')->get())
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreAnnouncementRequest $request)
    {
        try {
            $announcement = Announcement::create($request->all());

            if ($request->is_wa_sent == 1) {
                $this->broadcastWhatsApp($announcement);
            }

            return response([
                    'status' => 'success',
                    'statusMessage' => 'Data Pengumuman berhasil ditambahkan',
                    'result' => new AnnouncementResource($announcement)
                ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 422);
        }
    }

    public function show(Announcement $announcement)
    {
        try {
            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new AnnouncementResource($announcement)
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 422);
        }
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement)
    {
        try {
            // Use callback to strictly filter nulls, preserving 0 or false
            $data = array_filter($request->all(), function ($value) {
                return $value !== null;
            });

            $updated = $announcement->update($data);
            if ($updated && $announcement->wasChanged('is_wa_sent') && $announcement->is_wa_sent) {
                $this->broadcastWhatsApp($announcement);
            }
            return $updated
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Pengumuman berhasil diperbarui',
                    'result' => new AnnouncementResource($announcement)
                ]) : throw new Exception('Data Pengumuman gagal diperbarui');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(Announcement $announcement)
    {
        try {
            return $announcement->delete()
                ? response([
                    'status' => 'success',
                    'statusMessage' => 'Data Pengumuman berhasil dihapus',
                    'result' => new AnnouncementResource($announcement)
                ]) : throw new Exception('Data Pengumuman gagal dihapus');
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 422);
        }
    }

    private function broadcastWhatsApp(Announcement $announcement)
    {
        $query = User::query()->where('role', 4); // Students only

        if ($announcement->type == 'specific') {
            $query->where('id', $announcement->user_id);
        } elseif ($announcement->type == 'institution') {
            $query->where('institutionId', $announcement->institutionId);
        }
        // If global, no extra filter (sends to all students)
        $message = "*INFO PMB YAYASAN DARUL HIKMAH*\n\n";
        $message .= "*PENGUMUMAN: $announcement->title*\n\n$announcement->description";

        $query->chunk(100, function ($users) use ($message) {
            foreach ($users as $user) {
                if ($user->phone) {
                    SendWhatsAppMessage::dispatch($user->phone, $message);
                }
            }
        });
    }
}
