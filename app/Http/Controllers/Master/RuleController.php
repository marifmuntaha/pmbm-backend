<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Resources\Master\RuleResource;
use App\Models\Master\Rule;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Rule::query();

            // If user is Operator (3), show their institution's rules
            if ((int)$user->role === 3) {
                 if ($user->institutionId) {
                    $query->where('institutionId', $user->institutionId);
                 } else {
                     // If operator has no institution, maybe show empty?
                     // Or perhaps they shouldn't see anything.
                     // Let's assume valid operator has institutionId.
                     $query->where('institutionId', -1); // Force empty
                 }
            } else {
                // Admin (1, 2)
                // Filter by institutionId if provided, otherwise default to General Rules (null)
                // If request parameter 'type' is 'general', explicitly look for null
                if ($request->has('institutionId')) {
                    $query->where('institutionId', $request->institutionId);
                } else {
                    // Default behavior for Admin Management Page: List General Rules
                    $query->whereNull('institutionId');
                }
            }

            return response([
                'status' => 'success',
                'statusMessage' => '',
                'result' => RuleResource::collection($query->get())
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'content' => 'required|string',
            ]);
                         
            $data = $request->only(['content', 'institutionId']);
            $rule = Rule::create($data);

            return response([
                'status' => 'success',
                'statusMessage' => 'Data Aturan berhasil ditambahkan',
                'result' => new RuleResource($rule)
            ]);

        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rule $rule)
    {
        try {
            $request->validate([
                'content' => 'required|string',
            ]);

            $user = Auth::user();

            // Authorization check
            if ((int)$user->role === 3) {
                if ($rule->institutionId !== $user->institutionId) {
                    return response([
                        'status' => 'error',
                        'statusMessage' => 'Anda tidak memiliki akses untuk mengubah aturan ini.'
                    ], 403);
                }
            }

            $data = $request->only('content');
            $data['updatedBy'] = $user->id;

            $rule->update($data);

            return response([
                'status' => 'success',
                'statusMessage' => 'Data Aturan berhasil diperbarui',
                'result' => new RuleResource($rule)
            ]);

        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rule $rule)
    {
        try {
            $user = Auth::user();

             // Authorization check
            if ((int)$user->role === 3) {
                if ($rule->institutionId !== $user->institutionId) {
                    return response([
                        'status' => 'error',
                        'statusMessage' => 'Anda tidak memiliki akses untuk menghapus aturan ini.'
                    ], 403);
                }
            }

            $rule->delete();

            return response([
                'status' => 'success',
                'statusMessage' => 'Data Aturan berhasil dihapus',
                'result' => new RuleResource($rule)
            ]);

        } catch (Exception $e) {
            return response([
                'status' => 'error',
                'statusMessage' => $e->getMessage()
            ], 500);
        }
    }
}
