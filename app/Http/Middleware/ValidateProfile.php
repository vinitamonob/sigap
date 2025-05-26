<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Filament\Notifications\Notification;

class ValidateProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('filament.admin.auth.profile')) {
            return $next($request);
        }

        $user = Auth::user(); 
        if (
            $user->tempat_lahir == null ||
            $user->tgl_lahir == null ||
            $user->jenis_kelamin == null ||
            $user->telepon == null ||
            $user->tanda_tangan == null ||
            $user->detailUser->alamat == null ||
            $user->detailUser->lingkungan->nama_lingkungan == null ||
            $user->detailUser->nama_baptis == null ||
            $user->detailUser->tempat_baptis == null ||
            $user->detailUser->tgl_baptis == null ||
            $user->detailUser->no_baptis == null ||
            $user->detailUser->keluarga->nama_ayah == null ||
            $user->detailUser->keluarga->agama_ayah == null ||
            $user->detailUser->keluarga->pekerjaan_ayah == null ||
            $user->detailUser->keluarga->alamat_ayah == null ||
            $user->detailUser->keluarga->nama_ibu == null ||
            $user->detailUser->keluarga->agama_ibu == null ||
            $user->detailUser->keluarga->pekerjaan_ibu == null ||
            $user->detailUser->keluarga->alamat_ibu == null ||
            $user->detailUser->keluarga->ttd_ayah == null ||
            $user->detailUser->keluarga->ttd_ibu == null 
        ) {
            // Menambahkan notifikasi sebelum redirect
            Notification::make()
                ->title('Lengkapi Profil Anda')
                ->body('Anda harus melengkapi data profil terlebih dahulu untuk mengakses halaman ini.')
                ->warning()
                ->persistent()
                ->send();

            return redirect()->route('filament.admin.auth.profile');
        }
        return $next($request);
    }
}