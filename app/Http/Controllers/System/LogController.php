<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = Log::with('user')->latest();

        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        if ($request->has('search')) {
            $query->where('message', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'status' => 'success',
            'result' => $query->paginate($request->get('limit', 20))
        ]);
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
