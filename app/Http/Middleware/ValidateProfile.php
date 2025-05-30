<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Symfony\Component\HttpFoundation\Response;

class ValidateProfile
{
    protected array $missingFields = [];

    public function handle(Request $request, Closure $next): Response
    {
        // Bypass validasi untuk halaman tertentu
        if ($this->shouldBypassValidation($request)) {
            return $next($request);
        }

        $user = Auth::user();

        // Skip validasi jika email belum diverifikasi
        if (is_null($user->email_verified_at)) {
            return $next($request);
        }

        // Jika profile belum lengkap
        if ($this->profileIncomplete($user)) {
            return $this->redirectToProfile($user);
        }

        return $next($request);
    }

    protected function shouldBypassValidation(Request $request): bool
    {
        return $request->routeIs([
            'filament.admin.auth.profile',
            'filament.admin.auth.login',
            'filament.admin.auth.register',
            'filament.admin.auth.password-reset.reset',
            'filament.admin.auth.password-reset.request',
            'filament.admin.auth.email-verification.prompt',
            'filament.admin.auth.email-verification.verify',
            'verification.notice',
            'verification.verify',
            'verification.send',
            'filament.admin.auth.logout'
        ]) || $request->is('livewire/*');
    }

    protected function profileIncomplete($user): bool
    {
        $this->missingFields = [];
        $user = User::where('id', Auth::user()->id)->first();

        // Daftar field yang wajib diisi berdasarkan role
        $requiredFields = [];

        if ($user->hasRole('super_admin')) {
            $requiredFields = [
                'telepon' => $user->telepon,
            ];
        } elseif ($user->hasRole('paroki')) {
            $requiredFields = [
                'telepon' => $user->telepon,
                'tanda_tangan' => $user->tanda_tangan,
            ];
        } elseif ($user->hasRole('ketua_lingkungan')) {
            $requiredFields = [
                'tempat_lahir' => $user->tempat_lahir,
                'tgl_lahir' => $user->tgl_lahir,
                'jenis_kelamin' => $user->jenis_kelamin,
                'telepon' => $user->telepon,
                'tanda_tangan' => $user->tanda_tangan,
            ];
        } else {
            // Default untuk role lain, menggunakan validasi lengkap
            $requiredFields = [
                'tempat_lahir' => $user->tempat_lahir,
                'tgl_lahir' => $user->tgl_lahir,
                'jenis_kelamin' => $user->jenis_kelamin,
                'telepon' => $user->telepon,
                'tanda_tangan' => $user->tanda_tangan,
            ];
        }

        // Cek field pada user
        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                $this->missingFields[] = str_replace('_', ' ', $field);
                return true;
            }
        }

        // Khusus untuk ketua_lingkungan, tambahkan validasi data detail
        if ($user->hasRole('ketua_lingkungan')) {
            if (!$user->detailUser) {
                $this->missingFields[] = 'data detail user';
                return true;
            }

            $detailRequiredFields = [
                'alamat' => $user->detailUser->alamat,
                'lingkungan.nama_lingkungan' => optional($user->detailUser->lingkungan)->nama_lingkungan,
            ];

            foreach ($detailRequiredFields as $field => $value) {
                if (empty($value)) {
                    $this->missingFields[] = str_replace('.', ' ', $field);
                    return true;
                }
            }
        }

        // Validasi data keluarga hanya untuk role selain superadmin, paroki, dan ketua_lingkungan
        if (!$user->hasRole('super_admin') && !$user->hasRole('paroki') && !$user->hasRole('ketua_lingkungan')) {
            if (!$user->detailUser) {
                $this->missingFields[] = 'data detail user';
                return true;
            }

            if (!$user->detailUser->keluarga) {
                $this->missingFields[] = 'data keluarga';
                return true;
            }

            $keluargaFields = [
                'nama_ayah' => $user->detailUser->keluarga->nama_ayah,
                'agama_ayah' => $user->detailUser->keluarga->agama_ayah,
                'pekerjaan_ayah' => $user->detailUser->keluarga->pekerjaan_ayah,
                'alamat_ayah' => $user->detailUser->keluarga->alamat_ayah,
                'nama_ibu' => $user->detailUser->keluarga->nama_ibu,
                'agama_ibu' => $user->detailUser->keluarga->agama_ibu,
                'pekerjaan_ibu' => $user->detailUser->keluarga->pekerjaan_ibu,
                'alamat_ibu' => $user->detailUser->keluarga->alamat_ibu,
            ];

            foreach ($keluargaFields as $field => $value) {
                if (empty($value)) {
                    $this->missingFields[] = str_replace('_', ' ', $field);
                    return true;
                }
            }
        }

        return false;
    }

    protected function redirectToProfile($user): Response
    {
        $message = 'Anda harus melengkapi data profil terlebih dahulu untuk mengakses halaman ini.';
        
        if (!empty($this->missingFields)) {
            $fields = implode(', ', array_unique($this->missingFields));
            $message = "Silakan lengkapi data berikut: $fields.";
        }

        Notification::make()
            ->title('Lengkapi Profil Anda')
            ->body($message)
            ->warning()
            ->persistent()
            ->send();

        return redirect()->route('filament.admin.auth.profile');
    }
}