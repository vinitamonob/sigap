<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Tables\Columns\Summarizers\Sum;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'alamat',
        'telepon',
        'nama_lingkungan',
        'tanda_tangan'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function lingkungan()
    {
        return $this->hasOne(Lingkungan::class);
    }

    public function keteranganKematian()
    {
        return $this->hasMany(KeteranganKematian::class);
    }

    public function keteranganLain()
    {
        return $this->hasMany(KeteranganLain::class);
    }

    public function pendaftaranBaptis()
    {
        return $this->hasMany(PendaftaranBaptis::class);
    }

    public function pendaftaranKanonikPerkawinan()
    {
        return $this->hasMany(PendaftaranKanonikPerkawinan::class);
    }

    public function surat()
    {
        return $this->hasMany(Surat::class);
    }
}
