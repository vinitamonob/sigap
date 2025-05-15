<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Surat;
use App\Models\Keluarga;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\DetailUser;
use App\Models\Lingkungan;
use Illuminate\Support\Str;
use App\Models\KetuaLingkungan;
use App\Models\PendaftaranBaptis;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class FormPendaftaranBaptis extends Page implements HasForms
{
    use InteractsWithForms;

    use HasPageShield;
    
    protected static ?string $navigationGroup = 'Form Pengajuan';

    protected static ?string $navigationLabel = 'Pendaftaran Baptis';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.form-pendaftaran-baptis';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Data Administrasi')
                    ->schema([
                        Hidden::make('nomor_surat'),
                        Hidden::make('user_id')
                            ->default(fn () => Auth::id()),
                        Select::make('lingkungan_id')
                            ->required()
                            ->label('Nama Lingkungan/Stasi')
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
                        Hidden::make('nama_lingkungan'),
                        Hidden::make('ketua_lingkungan_id'),
                        TextInput::make('paroki')
                            ->required()
                            ->label('Paroki')
                            ->readOnly(), 
                        DatePicker::make('tgl_surat')
                            ->required()
                            ->label('Tanggal Surat')
                            ->default(now())
                            ->readOnly(),
                        Select::make('user_id')
                            ->label('Pilih Umat (Opsional)')
                            ->options(function () {
                                return User::with('detailUser')->get()
                                    ->mapWithKeys(function ($user) {
                                        return [$user->id => $user->name];
                                    });
                            })
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $user = User::with(['detailUser', 'detailUser.keluarga'])->find($state);
                                    if ($user) {
                                        $set('nama_lengkap', $user->name);
                                        $set('akun_email', $user->email);
                                        $set('jenis_kelamin', $user->jenis_kelamin);
                                        $set('tempat_lahir', $user->tempat_lahir);
                                        $set('tgl_lahir', $user->tgl_lahir);
                                        $set('telepon', $user->telepon);
                                        
                                        if ($user->detailUser) {
                                            $set('nama_baptis', $user->detailUser->nama_baptis);
                                            $set('alamat', $user->detailUser->alamat);
                                            
                                            if ($user->detailUser->keluarga) {
                                                $set('nama_ayah', $user->detailUser->keluarga->nama_ayah);
                                                $set('agama_ayah', $user->detailUser->keluarga->agama_ayah);
                                                $set('nama_ibu', $user->detailUser->keluarga->nama_ibu);
                                                $set('agama_ibu', $user->detailUser->keluarga->agama_ibu);
                                                $set('alamat_keluarga', $user->detailUser->keluarga->alamat_ayah);
                                                $set('ttd_ortu', $user->detailUser->keluarga->ttd_ayah ?? $user->detailUser->keluarga->ttd_ibu);
                                            }
                                        }
                                    }
                                }
                            }),
                    ]),
                Fieldset::make('Data Pendaftar')
                    ->schema([
                        TextInput::make('nama_lengkap')
                            ->required()
                            ->label('Nama Lengkap')
                            ->maxLength(255),
                        TextInput::make('akun_email')
                            ->required()
                            ->label('Akun Email')
                            ->maxLength(255),
                        TextInput::make('nama_baptis')
                            ->required()
                            ->label('Nama Baptis')
                            ->maxLength(255),
                        Radio::make('jenis_kelamin')
                            ->required()
                            ->label('Jenis Kelamin')
                            ->inline()
                            ->inlineLabel(false)
                            ->options([
                                'Pria' => 'Pria',
                                'Wanita' => 'Wanita'
                            ]),
                        TextInput::make('tempat_lahir')
                            ->required()
                            ->label('Tempat Lahir')
                            ->maxLength(255),
                        DatePicker::make('tgl_lahir')
                            ->required()
                            ->label('Tanggal Lahir'),
                        Textarea::make('alamat')
                            ->required()
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),
                        TextInput::make('telepon')
                            ->tel()
                            ->required()
                            ->label('Nomor Telepon')
                            ->maxLength(255),
                        Radio::make('agama_asal')
                            ->required()
                            ->label('Agama Asal')
                            ->inline()
                            ->inlineLabel(false)
                            ->options([
                                'Islam' => 'Islam',
                                'Hindu' => 'Hindu',
                                'Budha' => 'Budha',
                                'Protestan' => 'Protestan',
                            ]),
                        Select::make('pendidikan_terakhir')
                            ->required()
                            ->label('Pendidikan Terakhir')
                            ->options([
                                'Diploma/Sarjana' => 'Diploma/Sarjana',
                                'SMA' => 'SMA',
                                'SMP' => 'SMP',
                                'SD' => 'SD',
                                'TK' => 'TK',
                                'Belum Sekolah' => 'Belum Sekolah',
                            ]),
                        DatePicker::make('tgl_belajar')
                            ->required()
                            ->label('Tanggal Mulai Pembelajaran'),
                        TextInput::make('wali_baptis')
                            ->required()
                            ->label('Nama Wali Baptis')
                            ->maxLength(255),
                        Textarea::make('alasan_masuk')
                            ->required()
                            ->label('Alasan Masuk Katolik')
                            ->columnSpanFull(),
                        DatePicker::make('tgl_baptis')
                            ->required(),       
                    ]),
                Fieldset::make('Data Keluarga')
                    ->schema([
                        TextInput::make('nama_ayah')
                            ->required()
                            ->label('Nama Ayah')
                            ->maxLength(255),
                        Select::make('agama_ayah')
                            ->required()
                            ->label('Agama Ayah')
                            ->options([
                                'Islam' => 'Islam',
                                'Hindu' => 'Hindu',
                                'Budha' => 'Budha',
                                'Katolik' => 'Katolik',
                                'Protestan' => 'Protestan',
                            ]),
                        TextInput::make('nama_ibu')
                            ->required()
                            ->label('Nama Ibu')
                            ->maxLength(255),
                        Select::make('agama_ibu')
                            ->required()
                            ->label('Agama Ibu')
                            ->options([
                                'Islam' => 'Islam',
                                'Hindu' => 'Hindu',
                                'Budha' => 'Budha',
                                'Katolik' => 'Katolik',
                                'Protestan' => 'Protestan',
                            ]),
                        Fieldset::make('Anggota Keluarga yang sudah Katolik')
                            ->schema([
                                TextInput::make('nama_keluarga1')
                                    ->maxLength(255)
                                    ->label('Nama Keluarga 1'),
                                Select::make('hub_keluarga1')
                                    ->label('Hubungan Keluarga 1')
                                    ->options([
                                        'Saudara Kandung' => 'Saudara Kandung',
                                        'Pasangan' => 'Pasangan',
                                        'Sepupu' => 'Sepupu',
                                        'Wali' => 'Wali',
                                        'Kerabat Lainnya' => 'Kerabat Lainnya',
                                    ]),
                                TextInput::make('nama_keluarga2')
                                    ->maxLength(255)
                                    ->label('Nama Keluarga 2'),
                                Select::make('hub_keluarga2')
                                    ->label('Hubungan Keluarga 2')
                                    ->options([
                                        'Saudara Kandung' => 'Saudara Kandung',
                                        'Pasangan' => 'Pasangan',
                                        'Sepupu' => 'Sepupu',
                                        'Wali' => 'Wali',
                                        'Kerabat Lainnya' => 'Kerabat Lainnya',
                                    ]),
                            ]),
                        Textarea::make('alamat_keluarga')
                            ->required()
                            ->label('Alamat Keluarga')
                            ->columnSpanFull(),
                        SignaturePad::make('ttd_ortu')
                            ->required()
                            ->label('Tanda Tangan Orang Tua (Ayah)'),
                        Hidden::make('nama_pastor'),
                        Hidden::make('ttd_pastor'),
                        Hidden::make('ttd_ketua'),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $image = $this->data['ttd_ortu'];  
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::random(10).'.'.'png';
        File::put(storage_path(). '/' . $imageName, base64_decode($image));

        $this->data['ttd_ortu'] = $imageName;

        $data = $this->form->getState();
        
        $keteranganLain = PendaftaranBaptis::create($data);
        
        $surat = Surat::create([
            'user_id' => Auth::id(),
            'lingkungan_id' => $data['lingkungan_id'],
            'jenis_surat' => 'pendaftaran_baptis',
            'perihal' => 'Pendaftaran Baptis',
            'tgl_surat' => $data['tgl_surat'] ?? now(),
            'status' => 'menunggu',
        ]);
        
        if ($surat) {
            $keteranganLain->update(['surat_id' => $surat->id]);
        }

        Notification::make()
            ->title('Pengajuan Pendaftaran Baptis berhasil dibuat')
            ->success()
            ->send();
            
        $this->form->fill();
    }
}
