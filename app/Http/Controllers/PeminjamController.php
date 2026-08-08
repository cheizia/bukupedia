<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peminjam;
use App\Models\Buku;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PeminjamController extends Controller
{
    public function pinjam(Request $request)
    {
        $request->validate([
            'buku_id' => 'required|exists:buku,id',
        ]);

        $user = $request->user();

        if ($user->role !== 'anggota') {
            return response()->json([
                'message' => 'Hanya anggota yang dapat meminjam buku'
            ], 403);
        }

        return DB::transaction(function () use ($request, $user) {
            $buku = Buku::where('id', $request->buku_id)->lockForUpdate()->first();

            if (!$buku) {
                return response()->json(['message' => 'Buku tidak ditemukan'], 404);
            }

            if ($buku->stok <= 0) {
                return response()->json(['message' => 'Stok buku habis'], 409);
            }

            $jumlahPinjaman = Peminjam::where('user_id', $user->id)
                ->where('status', 'dipinjam')
                ->count();

            if ($jumlahPinjaman >= 3) {
                return response()->json([
                    'message' => 'Maksimal peminjaman aktif adalah 3 buku'
                ], 409);
            }

            $sudahDipinjam = Peminjam::where('user_id', $user->id)
                ->where('buku_id', $buku->id)
                ->where('status', 'dipinjam')
                ->exists();

            if ($sudahDipinjam) {
                return response()->json([
                    'message' => 'Buku ini masih Anda pinjam'
                ], 409);
            }

            $peminjaman = Peminjam::create([
                'user_id' => $user->id,
                'buku_id' => $buku->id,
                'tanggal_pinjam' => Carbon::today(),
                'tanggal_jatuh_tempo' => Carbon::today()->addDays(7),
                'tanggal_kembali' => null,
                'status' => 'dipinjam',
                'denda' => 0,
            ]);

            $buku->decrement('stok');

            return response()->json([
                'message' => 'Buku berhasil dipinjam',
                'data' => $peminjaman->load('buku')
            ], 201);
        });
    }

    public function daftarAnggota(Request $request)
    {
        if ($request->user()->role !== 'petugas') {
            return response()->json([
                'message' => 'Akses ditolak'
            ], 403);
        }

        $anggota = User::where('role', 'anggota')
            ->select('id', 'user_id', 'nama', 'email')
            ->get();

        return response()->json([
            'message' => 'Data anggota berhasil diambil',
            'data' => $anggota
        ], 200);
    }

    public function pinjamPetugas(Request $request)
    {
        if ($request->user()->role !== 'petugas') {
            return response()->json([
                'message' => 'Hanya petugas yang dapat melakukan transaksi ini'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'buku_id' => 'required|exists:buku,id',
        ]);

        $anggota = User::where('id', $request->user_id)
            ->where('role', 'anggota')
            ->first();

        if (!$anggota) {
            return response()->json([
                'message' => 'Anggota tidak ditemukan'
            ], 404);
        }

        return DB::transaction(function () use ($request, $anggota) {
            $buku = Buku::where('id', $request->buku_id)
                ->lockForUpdate()
                ->first();

            if (!$buku) {
                return response()->json([
                    'message' => 'Buku tidak ditemukan'
                ], 404);
            }

            if ($buku->stok <= 0) {
                return response()->json([
                    'message' => 'Stok buku habis'
                ], 409);
            }

            $jumlahPinjaman = Peminjam::where('user_id', $anggota->id)
                ->where('status', 'dipinjam')
                ->count();

            if ($jumlahPinjaman >= 3) {
                return response()->json([
                    'message' => 'Anggota sudah mencapai maksimal 3 buku'
                ], 409);
            }

            $sudahDipinjam = Peminjam::where('user_id', $anggota->id)
                ->where('buku_id', $buku->id)
                ->where('status', 'dipinjam')
                ->exists();

            if ($sudahDipinjam) {
                return response()->json([
                    'message' => 'Buku ini masih dipinjam oleh anggota tersebut'
                ], 409);
            }

            $peminjaman = Peminjam::create([
                'user_id' => $anggota->id,
                'buku_id' => $buku->id,
                'tanggal_pinjam' => Carbon::today(),
                'tanggal_jatuh_tempo' => Carbon::today()->addDays(7),
                'tanggal_kembali' => null,
                'status' => 'dipinjam',
                'denda' => 0,
            ]);

            $buku->decrement('stok');

            return response()->json([
                'message' => 'Peminjaman berhasil dibuat',
                'data' => $peminjaman->load('user', 'buku')
            ], 201);
        });
    }

    public function riwayat(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'petugas', 'anggota'])) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses'
            ], 403);
        }

        $query = Peminjam::with([
            'user',
            'buku.kategori'
        ]);

        if ($user->role === 'anggota') {
            $query->where('user_id', $user->id);
        }

        $data = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'message' => 'Data riwayat peminjaman berhasil diambil',
            'data' => $data
        ], 200);
    }

    public function kembali(Request $request, $id)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'petugas'])) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk mengembalikan buku'
            ], 403);
        }

        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjam::where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$peminjaman) {
                return response()->json([
                    'message' => 'Data peminjaman tidak ditemukan'
                ], 404);
            }

            if ($peminjaman->status === 'dikembalikan') {
                return response()->json([
                    'message' => 'Buku sudah dikembalikan sebelumnya'
                ], 409);
            }

            if (!$peminjaman->buku) {
                return response()->json([
                    'message' => 'Buku tidak ditemukan'
                ], 404);
            }

            $tanggalKembali = Carbon::today();
            $tanggalJatuhTempo = Carbon::parse($peminjaman->tanggal_jatuh_tempo);
            $terlambat = 0;
            $denda = 0;

            if ($tanggalKembali->greaterThan($tanggalJatuhTempo)) {
                $terlambat = (int) $tanggalJatuhTempo->diffInDays($tanggalKembali);
                $denda = $terlambat * 2000;
            }

            $peminjaman->update([
                'tanggal_kembali' => $tanggalKembali,
                'status' => 'dikembalikan',
                'denda' => $denda,
            ]);

            $peminjaman->buku()->increment('stok');

            return response()->json([
                'message' => 'Buku berhasil dikembalikan',
                'terlambat_hari' => $terlambat,
                'denda' => $denda,
                'data' => $peminjaman->fresh()->load('buku', 'user')
            ], 200);
        });
    }
}