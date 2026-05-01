<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Log;
use Exception;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Log::with('user')->latest();

            if ($request->has('level')) {
                $query->where('level', $request->level);
            }

            return response()->json([
                'status' => 'success',
                'result' => $query->paginate($request->paginate ?? 10)
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        Log::findOrFail($id)->delete();
        return response()->json([
            'status' => 'success',
            'statusMessage' => 'Log berhasil dihapus.'
        ]);
    }

    public function clear()
    {
        Log::truncate();
        return response()->json([
            'status' => 'success',
            'statusMessage' => 'Semua log berhasil dibersihkan.'
        ]);
    }
}
