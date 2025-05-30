<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Surat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lingkungan_id',
        'jenis_surat',
        'nomor_surat',
        'perihal',
        'tgl_surat',
        'status',
        'file_surat',
    ];

    protected $casts = [
        'tgl_surat' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lingkungan()
    {
        return $this->belongsTo(Lingkungan::class);
    }
    
    public function keteranganKematian()
    {
        return $this->hasOne(KeteranganKematian::class);
    }
    
    public function keteranganLain()
    {
        return $this->hasOne(KeteranganLain::class);
    }
    
    public function pendaftaranBaptis()
    {
        return $this->hasOne(PendaftaranBaptis::class);
    }
    
    public function pendaftaranKanonik()
    {
        return $this->hasOne(PendaftaranPerkawinan::class);
    }
}
