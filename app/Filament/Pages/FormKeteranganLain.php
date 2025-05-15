<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Surat;
use Filament\Forms\Form;
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
        // Isi form dengan data user yang login
        $user = Auth::user();
        $detailUser = $user->detailUser;
        
        $this->form->fill([
            'user_id' => $user->id,
            'nama_lengkap' => $user->name,
            // 'akun_email' => $user->email,
            'tempat_lahir' => $user->tempat_lahir,
            'tgl_lahir' => $user->tgl_lahir,
            'telepon' => $user->telepon,
            'alamat' => $detailUser->alamat ?? null,
            'lingkungan_id' => $detailUser->lingkungan_id ?? null,
            'paroki' => $detailUser->lingkungan->paroki ?? 'St. Stephanus Cilacap',
            'nama_lingkungan' => $detailUser->lingkungan->nama_lingkungan ?? null,
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
                            ->maxLength(255),
                        // TextInput::make('akun_email')
                        //     ->required()
                        //     ->label('Akun Email')
                        //     ->maxLength(255),
                        TextInput::make('tempat_lahir')
                            ->required()
                            ->label('Tempat Lahir')
                            ->maxLength(255),
                        DatePicker::make('tgl_lahir')
                            ->required()
                            ->label('Tanggal Lahir'),
                        TextInput::make('pekerjaan')
                            ->required()
                            ->label('Pekerjaan')
                            ->maxLength(255),
                        Textarea::make('alamat')
                            ->required()
                            ->label('Alamat')
                            ->columnSpanFull(),
                        TextInput::make('telepon')
                            ->tel()
                            ->required()
                            ->label('No. Telepon/HP')
                            ->maxLength(255),
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
        
        $keteranganLain = KeteranganLain::create($data);
        
        $surat = Surat::create([
            'user_id' => Auth::id(),
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