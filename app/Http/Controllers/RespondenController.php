<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Responden;

class RespondenController extends Controller
{
    /**
     * Get all respondens with id, name, and address.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllRespondens()
    {
        $respondens = Responden::all(['id', 'name', 'address']);  // Tambah address di sini

        return response()->json([
            'message' => 'Respondens fetched successfully',
            'data' => $respondens
        ]);
    }

    /**
     * Get a specific responden by ID with id, name, and address.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRespondensById($id)
    {
        $respondens = Responden::find($id, ['id', 'name', 'address']);  // Tambah address di sini juga

        if (!$respondens) {
            return response()->json([
                'message' => 'Respondens not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Respondens fetched successfully',
            'data' => $respondens
        ]);
    }
}
