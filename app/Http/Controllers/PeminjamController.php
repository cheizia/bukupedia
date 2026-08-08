<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use app\Models\Peminjam;

class PeminjamController extends Controller
{
    public function pinjam(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $peminjam = Peminjam::find($id);
            if (!$peminjam) {
                return response()->json(['message' => 'Peminjam tidak ditemukan'], 404);
            }

            $validatedData = $request->validate([
                'tanggal_pinjam' => 'required|date',
                'tanggal_jatuh_tempo' => 'required|date|after:tanggal_pinjam',
                'status' => 'required|in:dipinjam,dikembalikan',
            ]);

            $buku = Buku::find($request->buku_id);
            if ($buku->stok <= 0) { return response()->json([ 'message' => 'Stok buku habis' ], 409);}

            $peminjam->update($validatedData);
            return response()->json([ 'message' => 'Peminjam berhasil diperbarui', 'data' => $peminjam ], 200);
        });

        $jumlahPeminjam = Peminjam::where('status', 'dipinjam')->count();
        if ($jumlahPeminjam >= 3) {
            return response()->json(['message' => 'Jumlah peminjam melebihi batas'], 400);
        }
        $peminjam = Peminjam::create($validatedData);
        return response()->json([ 'message' => 'Peminjam berhasil ditambahkan', 'data' => $peminjam ], 201);

        $sudahDipinjam = Peminjam::where('user_id', $request->user_id)->where('status', 'dipinjam')->exists();
        if ($sudahDipinjam) {
            return response()->json(['message' => 'User ini sudah meminjam buku'], 400);
        }
        $peminjam = Peminjam::create($validatedData);
        return response()->json([ 'message' => 'Peminjam berhasil ditambahkan', 'data' => $peminjam ], 201);

        $tanggalPinjam = Carbon::parse($request->tanggal_pinjam);
        $tanggalJatuhTempo = Carbon::parse($request->tanggal_jatuh_tempo);
        if ($tanggalJatuhTempo->diffInDays($tanggalPinjam) > 7) {
            return response()->json(['message' => 'Masa peminjaman tidak boleh lebih dari 7 hari'], 400);
        }
        $peminjam = Peminjam::create($validatedData);
        return response()->json([ 'message' => 'Peminjam berhasil ditambahkan', 'data' => $peminjam ], 201);

        $peminjaman = Peminjam::create([
            'user_id' => $request->user_id,
            'buku_id' => $request->buku_id,
            'tanggal_pinjam' => $request->tanggal_pinjam,
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
            'status' => 'dipinjam',
            'denda' => 0,
        ]);

        $tanggal_kembali = Carbon::parse($request->tanggal_kembali);
        if ($tanggal_kembali->gt($tanggal_jatuh_tempo)) {
            $denda = $tanggal_kembali->diffInDays($tanggal_jatuh_tempo) * 2000; 
            $peminjaman->update(['denda' => $denda]);
        }

        $peminjaman->buku()->increment('stok');
        return response()->json([ 'message' => 'Buku berhasil dikembalikan', 'data' => $peminjaman ], 200);

        $terlambat = $tanggal_kembali->gt($tanggal_jatuh_tempo);
        return response()->json([ 'message' => 'Buku berhasil dikembalikan', 'terlambat' => $terlambat, 'denda' => $denda, 'data' => $peminjaman->fresh()->load('buku') ], 200);
    }
    public function riwayat(Request $request)
    {
        $riwayat = Peminjam::with('buku')->get();
        return response()->json([ 'message' => 'Data riwayat peminjaman berhasil diambil', 'data' => $riwayat ], 200);
        if ($user -> role == 'anggota') {
            ($query->where('user_id', $user->id));
            $data = $query->orderBy('id', 'desc')->get();
            return response()->json([ 'message' => 'Data riwayat peminjaman berhasil diambil', 'data' => $data ], 200);
        }
    }
    public function kembali(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $peminjam = Peminjam::find($id);
            if (!$peminjam) {
                return response()->json(['message' => 'Peminjam tidak ditemukan'], 404);
            }

            $validatedData = $request->validate([
                'tanggal_kembali' => 'required|date|after_or_equal:tanggal_pinjam',
                'status' => 'required|in:dipinjam,dikembalikan',
                'denda' => 'nullable|numeric|min:0',
            ]);

            $peminjam->update($validatedData);
            return response()->json([ 'message' => 'Peminjam berhasil diperbarui', 'data' => $peminjam ], 200);
        });
    }
}