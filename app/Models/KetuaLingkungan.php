<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KetuaLingkungan extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'lingkungan_id',
        'periode',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function lingkungan()
    {
        return $this->has(Lingkungan::class);
    }
}
