<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lingkungan_id',
        'keluarga_id',
        'nama_baptis',
        'tempat_baptis',
        'tgl_baptis',
        'no_baptis',
        'alamat',
    ];

    protected $casts = [
        'tgl_baptis' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lingkungan()
    {
        return $this->belongsTo(Lingkungan::class);
    }

    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class);
    }
}
