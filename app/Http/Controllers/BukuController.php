<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Buku;

class BukuController extends Controller
{
    public function index()
    {
        $buku = Buku::all();
        return response()->json([ 'message' => 'Data buku berhasil diambil', 'data' => $buku ], 200);
    }
    public function show($id)
    {
        $buku = Buku::find($id);
        if (!$buku) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }
        return response()->json([ 'message' => 'Data buku berhasil diambil', 'data' => $buku ], 200);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'judul' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'tahun_terbit' => 'required|integer|digits:4',
        ]);
        do {
            $bukuId = 'BKU' . strtoupper(Str::random(8));
        } while (Buku::where('buku_id', $bukuId)->exists());

        // Masukkan buku_id ke data yang akan disimpan
        $validatedData['buku_id'] = $bukuId;
        $buku = Buku::create($validatedData);
        return response()->json([ 'message' => 'Buku berhasil ditambahkan', 'data' => $buku ], 201);
    }
    public function update(Request $request, $id)
    {
        $buku = Buku::find($id);
        if (!$buku) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }

        $validatedData = $request->validate([
            'judul' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'tahun_terbit' => 'required|integer|digits:4',
        ]);

        $buku->update($validatedData);
        return response()->json([ 'message' => 'Buku berhasil diperbarui', 'data' => $buku ], 200);
    }
    public function destroy($id)
    {
        $buku = Buku::find($id); 
        if (!$buku) { 
            return response()->json([ 'message' => 'Buku tidak ditemukan' ], 404); 
        } 
        if ($buku->peminjaman()->exists()) { 
            return response()->json([ 'message' => 'Buku tidak dapat dihapus karena memiliki riwayat peminjaman' ], 409); } 
        $buku->delete(); return response()->json([ 'message' => 'Buku berhasil dihapus' ], 200); 
    }
}