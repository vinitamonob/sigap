<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Surat;
use Filament\Forms\Form;
use App\Models\DetailUser;
use Filament\Pages\Page;
use App\Models\Lingkungan;
use App\Models\KeteranganLain;
use App\Models\KetuaLingkungan;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class FormKeteranganLain extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;
    
    protected static ?string $navigationGroup = 'Form Pengajuan';
    protected static ?string $navigationLabel = 'Keterangan Lain';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.form-keterangan-lain';

    public ?array $data = [];
    
    public function mount(): void
    {
        $user = Auth::user();
        $detailUser = DetailUser::where('user_id', $user->id)->first();
        
        // Jika detail user belum ada, buat baru
        if (!$detailUser) {
            $detailUser = DetailUser::create(['user_id' => $user->id]);
        }

        $this->form->fill([
            'user_id' => $user->id,
            'nama_lengkap' => $user->name,
            'tempat_lahir' => $user->tempat_lahir,
            'tgl_lahir' => $user->tgl_lahir,
            'telepon' => $user->telepon,
            'alamat' => $user->detailUser->alamat ?? null,
            'lingkungan_id' => $user->detailUser->lingkungan_id ?? null,
            'paroki' => $user->detailUser->lingkungan->paroki ?? 'St. Stephanus Cilacap',
            'nama_lingkungan' => $user->detailUser->lingkungan->nama_lingkungan ?? null,
            'tgl_surat' => now(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Data Administrasi')
                    ->schema([
                        Hidden::make('user_id'),
                        Hidden::make('nama_lingkungan'),
                        Hidden::make('ketua_lingkungan_id'),
                        Hidden::make('nomor_surat'),
                        Select::make('lingkungan_id')
                            ->required()
                            ->label('Nama Lingkungan / Stasi')
                            ->options(Lingkungan::pluck('nama_lingkungan', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $lingkungan = Lingkungan::find($state);
                                    $ketuaLingkungan = KetuaLingkungan::where('lingkungan_id', $state)
                                        ->where('aktif', true)
                                        ->first();
                                    
                                    if ($lingkungan) {
                                        $set('nama_lingkungan', $lingkungan->nama_lingkungan);
                                        $set('paroki', $lingkungan->paroki ?? 'St. Stephanus Cilacap');
                                    }
                                    
                                    if ($ketuaLingkungan) {
                                        $set('ketua_lingkungan_id', $ketuaLingkungan->id);
                                    }
                                }
                            }),
                        TextInput::make('paroki')
                            ->required()
                            ->label('Paroki')
                            ->readOnly(), 
                        DatePicker::make('tgl_surat')
                            ->required()
                            ->label('Tanggal Surat')
                            ->default(now())
                            ->readOnly(),
                    ]),
                Fieldset::make('Data Pemohon')
                    ->schema([
                        TextInput::make('nama_lengkap')
                            ->required()
                            ->label('Nama Lengkap')
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('data.nama_lengkap', $state);
                            }),
                        TextInput::make('tempat_lahir')
                            ->required()
                            ->label('Tempat Lahir')
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('data.tempat_lahir', $state);
                            }),
                        DatePicker::make('tgl_lahir')
                            ->required()
                            ->label('Tanggal Lahir')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('data.tgl_lahir', $state);
                            }),
                        TextInput::make('pekerjaan')
                            ->required()
                            ->label('Pekerjaan')
                            ->maxLength(255),
                        Textarea::make('alamat')
                            ->required()
                            ->label('Alamat')
                            ->columnSpanFull()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('data.alamat', $state);
                            }),
                        TextInput::make('telepon')
                            ->tel()
                            ->required()
                            ->label('No. Telepon/HP')
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('data.telepon', $state);
                            }),
                        Select::make('status_tinggal')
                            ->required()
                            ->label('Status Tempat Tinggal')
                            ->options([
                                'Sendiri' => 'Sendiri',
                                'Bersama Keluarga' => 'Bersama Keluarga',
                                'Bersama Saudara' => 'Bersama Saudara',
                                'Kos/Kontrak' => 'Kos/Kontrak',
                            ]),
                        Textarea::make('keperluan')
                            ->required()
                            ->label('Keperluan')
                            ->columnSpanFull(),
                        Hidden::make('nama_pastor'),
                        Hidden::make('ttd_pastor'),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        /** @var User $user */
        $user = Auth::user();
        
        // Update data user
        $user->update([
            'name' => $data['nama_lengkap'],
            'tempat_lahir' => $data['tempat_lahir'],
            'tgl_lahir' => $data['tgl_lahir'],
            'telepon' => $data['telepon'],
        ]);
        
        // Update atau create detail user
        $user->detailUser()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'alamat' => $data['alamat'],
                'lingkungan_id' => $data['lingkungan_id'],
            ]
        );

        // Buat keterangan lain
        $keteranganLain = KeteranganLain::create($data);
        
        // Buat surat
        $surat = Surat::create([
            'user_id' => $user->id,
            'lingkungan_id' => $data['lingkungan_id'],
            'jenis_surat' => 'keterangan_lain',
            'perihal' => 'Keterangan Lain',
            'tgl_surat' => $data['tgl_surat'] ?? now(),
            'status' => 'menunggu',
        ]);
        
        if ($surat) {
            $keteranganLain->update(['surat_id' => $surat->id]);
        }

        Notification::make()
            ->title('Pengajuan Keterangan Lain berhasil dibuat')
            ->success()
            ->send();
            
        $this->form->fill();
    }
}