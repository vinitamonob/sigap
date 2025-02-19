<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lingkungan extends Model
{
    protected $fillable = [
        'kode',
        'nama_lingkungan',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
