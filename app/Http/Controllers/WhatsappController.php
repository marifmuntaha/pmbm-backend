<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWhatsappRequest;
use App\Http\Requests\UpdateWhatsappRequest;
use App\Http\Resources\WhatsappResource;
use App\Models\Whatsapp;
use App\Services\WhatsAppService;

use Exception;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    public function index(Request $request)
    {
        try {
            $apiWhatsapp = new WhatsAppService();
            $whatsapps = new Whatsapp();
            $whatsapps = $request->has('institutionId') ? $whatsapps->whereInstitutionid($request->institutionId) : $whatsapps;
            $result = $whatsapps->get()->each(function ($item) use ($apiWhatsapp) {
                $status = $apiWhatsapp->deviceInfo($item->device);
                return $item->status = $status;
            });
            return response()->json([
                'status' => 'success',
                'statusMessage' => '',
                'result' => WhatsappResource::collection($result)
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(StoreWhatsappRequest $request)
    {
        try {
            $apiWhatsapp = new WhatsappService();
            if ($apiWhatsapp->deviceAdd($request->device)) {
                return ($whatsapp = Whatsapp::create($request->all()))
                    ? response()->json([
                        'status' => 'success',
                        'statusMessage' => 'Data berhasil ditambahkan',
                        'result' => new WhatsappResource($whatsapp)
                    ], 201) : throw new Exception('Something went wrong', 500);
            }
            else {
                throw new Exception('Whatsapp API tidak terhubung', 500);
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => $th->getMessage(),
            ], 422);
        }
    }

    public function show(Whatsapp $whatsapp)
    {
        try {
            return response()->json([
                'status' => 'success',
                'statusMessage' => '',
                'result' => new WhatsappResource($whatsapp)
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateWhatsappRequest $request, Whatsapp $whatsapp)
    {
        try {
            $apiWhatsapp = new WhatsappService();
            if ($apiWhatsapp->deviceAdd($request->device)) {
                return $whatsapp->update($request->all())
                    ? response()->json([
                        'status' => 'success',
                        'statusMessage' => 'Data berhasil di update',
                        'result' => new WhatsappResource($whatsapp)
                    ]) : throw new \Exception('Something went wrong', 500);
            } else {
                throw new Exception('Whatsapp API tidak terhubung', 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => $th->getMessage(),
            ], 422);
        }
    }

    public function destroy(Whatsapp $whatsapp)
    {
        try {
            $apiWhatsapp = new WhatsappService();
            if ($apiWhatsapp->deviceRemove($whatsapp->device)) {
                return $whatsapp->destroy($whatsapp->id)
                    ? response()->json([
                        'status' => 'success',
                        'statusMessage' => 'Data berhasil dihapus',
                        'result' => new WhatsappResource($whatsapp)
                    ]) : throw new \Exception('Something went wrong', 500);
            } else {
                throw new Exception('Whatsapp API tidak terhubung', 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => $th->getMessage(),
            ], 422);
        }
    }

    public function login(Request $request)
    {
        try {
            $apiWhatsapp = new WhatsappService();
            $response = $apiWhatsapp->deviceLogin($request->device);
            return response()->json([
                'status' => 'success',
                'statusMessage' => '',
                'result' => $response['results']
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'statusMessage' => $th->getMessage(),
            ], 500);
        }
    }
}
