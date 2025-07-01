<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Surat;
use Filament\Forms\Form;
use App\Models\DetailUser;
use Filament\Pages\Page;
use App\Models\Lingkungan;
use App\Models\KetuaLingkungan;
use App\Models\KeteranganKematian;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class FormKeteranganKematian extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationGroup = 'Form Pengajuan';
    protected static ?string $navigationLabel = 'Keterangan Kematian';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.form-keterangan-kematian';

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
            'lingkungan_id' => $detailUser->lingkungan_id ?? null,
            'paroki' => $detailUser->lingkungan->paroki ?? 'St. Stephanus Cilacap',
            'nama_lingkungan' => $detailUser->lingkungan->nama_lingkungan ?? null,
            'tgl_surat' => now(),
            'pelayanan_sakramen' => 'Perminyakan',
            'sakramen' => 'Minyak Suci',
        ]);
    }
    
    public function form(Form $form): Form
    {
        $user = Auth::user();
        $detailUser = DetailUser::where('user_id', $user->id)->first();
        // $isLingkunganDisabled = $detailUser && $detailUser->lingkungan_id;

        return $form
            ->schema([
                Fieldset::make('Data Administrasi')
                    ->schema([
                        Hidden::make('nomor_surat'),
                        Hidden::make('user_id'),
                        Hidden::make('nama_lingkungan'),
                        Hidden::make('ketua_lingkungan_id'),
                        Select::make('lingkungan_id')
                            ->required()
                            ->label('Nama Lingkungan / Stasi')
                            ->options(Lingkungan::pluck('nama_lingkungan', 'id'))
                            ->searchable()
                            ->reactive()
                            // ->disabled($isLingkunganDisabled)
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
                    
                Fieldset::make('Data Kematian')
                    ->schema([
                        TextInput::make('nama_lengkap')
                            ->required()
                            ->label('Nama Lengkap')
                            ->regex('/^[\pL\s]+$/u')
                            ->maxLength(255),
                        TextInput::make('usia')
                            ->required()
                            ->label('Usia')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('tempat_baptis')
                            ->required()
                            ->label('Tempat Baptis')
                            ->maxLength(255),
                        TextInput::make('no_baptis')
                            ->required()
                            ->label('No. Buku Baptis')
                            ->maxLength(255),
                        TextInput::make('nama_ortu')
                            ->required()
                            ->label('Nama Orang Tua')
                            ->regex('/^[\pL\s]+$/u')
                            ->maxLength(255),
                        TextInput::make('nama_pasangan')
                            ->label('Nama Pasangan')
                            ->regex('/^[\pL\s]+$/u')
                            ->maxLength(255),
                        DatePicker::make('tgl_kematian')
                            ->required()
                            ->label('Tanggal Kematian'),
                        DatePicker::make('tgl_pemakaman')
                            ->required()
                            ->label('Tanggal Pemakaman'),
                        TextInput::make('tempat_pemakaman')
                            ->required()
                            ->label('Tempat Pemakaman')
                            ->maxLength(255),
                        TextInput::make('pelayanan_sakramen')
                            ->required()
                            ->label('Pelayanan Sakramen')
                            ->readOnly(), 
                        TextInput::make('sakramen')
                            ->required()
                            ->label('Sakramen')
                            ->readOnly(),
                    ])
            ])
            ->statePath('data');
    }
    
    public function create(): void
    {
        $data = $this->form->getState();
        /** @var User $user */
        $user = Auth::user();
        
        // Update data lingkungan di detail user jika berubah
        if (isset($data['lingkungan_id'])) {
            $user->detailUser()->updateOrCreate(
                ['user_id' => $user->id],
                ['lingkungan_id' => $data['lingkungan_id']]
            );
        }

        // Simpan data ke tabel Keterangan Kematian
        $keteranganKematian = KeteranganKematian::create($data);
        
        // Buat data surat terkait
        $surat = Surat::create([
            'user_id' => $user->id,
            'lingkungan_id' => $data['lingkungan_id'],
            'jenis_surat' => 'keterangan_kematian',
            'perihal' => 'Keterangan Kematian',
            'tgl_surat' => $data['tgl_surat'] ?? now(),
            'status' => 'menunggu',
        ]);
        
        // Update nomor surat jika diperlukan
        if ($surat) {
            $keteranganKematian->update([
                'surat_id' => $surat->id
            ]);
        }

        Notification::make()
            ->title('Pengajuan Keterangan Kematian berhasil dibuat')
            ->success()
            ->send();
            
        // Reset form setelah submit
        $this->form->fill();
    }
}