<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = Kategori::all();

        return response()->json([
            'message' => 'Data kategori berhasil diambil',
            'data' => $kategori
        ], 200);
    }

    public function show($id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Data kategori berhasil diambil',
            'data' => $kategori
        ], 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        $kategori = Kategori::create($validatedData);

        return response()->json([
            'message' => 'Kategori berhasil dibuat',
            'data' => $kategori
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        $validatedData = $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        $kategori->update($validatedData);

        return response()->json([
            'message' => 'Kategori berhasil diperbarui',
            'data' => $kategori
        ], 200);
    }

    public function destroy($id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        if ($kategori->buku()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh buku'
            ], 409);
        }

        $kategori->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus'
        ], 200);
    }
}