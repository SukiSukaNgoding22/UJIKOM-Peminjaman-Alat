<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Kategori\StoreKategoriRequest;
use App\Http\Requests\Kategori\UpdateKategoriRequest;
use App\Http\Resources\KategoriResource;
use App\Models\Kategori;
use Illuminate\Http\JsonResponse;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = Kategori::latest()->get();
        return response()->json([
            'message' => 'Daftar kategori berhasil diambil',
            'data' => KategoriResource::collection($kategori),
        ]);
    }



    public function store(StoreKategoriRequest $request): JsonResponse
    {
        $kategori = Kategori::create($request->validated());
        return response()->json([
            'message' => 'Kategori berhasil ditambahkan',
            'data' => new KategoriResource($kategori),
        ], 201);
    }

    public function show(Kategori $kategori): JsonResponse
    {
        return response()->json([
            'data' => new KategoriResource($kategori),
        ]);
    }

    public function update(UpdateKategoriRequest $request, Kategori $kategori): JsonResponse
    {
        $kategori->update($request->validated());
        return response()->json([
            'message' => 'Kategori berhasil diperbarui',
            'data' => new KategoriResource($kategori),
        ]);
    }

    public function destroy(Kategori $kategori): JsonResponse
    {
        $kategori->delete();
        return response()->json([
            'message' => 'Kategori berhasil dihapus',
        ]);
    }
}
