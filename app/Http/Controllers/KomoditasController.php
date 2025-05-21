<?php

// app/Http/Controllers/KomoditasController.php
namespace App\Http\Controllers;

use App\Models\Komoditas;
use Illuminate\Http\Request;

class KomoditasController extends Controller
{
    /**
     * Get all komoditas names.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllKomoditas()
    {
        $komoditas = Komoditas::all(['id', 'name']);  // Only get id and name columns

        return response()->json([
            'message' => 'Komoditas fetched successfully',
            'data' => $komoditas
        ]);
    }

    /**
     * Get a specific komoditas by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getKomoditasById($id)
    {
        $komoditas = Komoditas::find($id, ['id', 'name']);  // Find by ID and get only id and name columns

        if (!$komoditas) {
            return response()->json([
                'message' => 'Komoditas not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Komoditas fetched successfully',
            'data' => $komoditas
        ]);
    }
}
