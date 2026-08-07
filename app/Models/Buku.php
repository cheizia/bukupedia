<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    use HasFactory;
    protected $table = 'buku';
    protected $fillable = [
        'buku_id',
        'judul',
        'penulis',
        'penerbit',
        'kategori_id',
        'stok',
        'tahun_terbit',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }
    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjam::class, 'buku_id');
    }
}
