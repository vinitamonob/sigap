<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Surat;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Lingkungan;
use App\Models\KeteranganLain;
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
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(fn () => Auth::id()),
                Fieldset::make('Label')
                    ->schema([
                        TextInput::make('nama_lingkungan')
                            ->required()
                            ->label('Nama Lingkungan / Stasi')
                            ->default(fn () => Auth::user()->nama_lingkungan)
                            ->readOnly()
                            ->maxLength(255),
                        TextInput::make('nama_ketua')
                            ->required()
                            ->label('Nama Ketua Lingkungan')
                            ->default(function () {
                                $user = Auth::user();
                                // Asumsi bahwa user memiliki relasi ke lingkungan
                                $lingkungan = Lingkungan::where('nama_lingkungan', $user->nama_lingkungan)->first();
                                // Jika lingkungan ditemukan, ambil nama user yang terkait
                                if ($lingkungan) {
                                    $ketuaUser = User::find($lingkungan->user_id);
                                    return $ketuaUser ? $ketuaUser->name : '';
                                }
                                return '';
                            })
                            ->readOnly(),
                        TextInput::make('paroki')
                            ->required()
                            ->label('Paroki')
                            ->default('St. Stephanus Cilacap')
                            ->readOnly()
                            ->maxLength(255),
                        DatePicker::make('tanggal_surat')
                            ->required()
                            ->label('Tanggal Surat')
                            ->default(now())
                            ->readOnly(),
                    ]),
                    Fieldset::make('Data Keperluan')
                        ->schema([
                            TextInput::make('nama_lengkap')
                                ->required()
                                ->label('Nama Lengkap')
                                ->maxLength(255),
                            TextInput::make('tempat_lahir')
                                ->required()
                                ->label('Tempat Lahir')
                                ->maxLength(255),
                            DatePicker::make('tanggal_lahir')
                                ->required()
                                ->label('Tanggal Lahir'),
                            TextInput::make('jabatan_pekerjaan')
                                ->required()
                                ->label('Jabatan Pekerjaan')
                                ->maxLength(255),
                            Textarea::make('alamat')
                                ->required()
                                ->label('Alamat')
                                ->columnSpanFull(),
                            TextInput::make('telepon')
                                ->tel()
                                ->label('No. Telepon / HP')
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
                                ->label('Perihal / Keperluan')
                                ->columnSpanFull(),
                        ])
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        // Mendapatkan data dari form
        $formData = $this->form->getState();
        // Simpan data ke tabel Keterangan Lain
        $keteranganLain = KeteranganLain::create($formData);

        Surat::create([
            'user_id' => Auth::id(),
            'kode_nomor_surat' => null,
            'perihal_surat' => 'Keterangan Lain',
            'atas_nama' => $formData['nama_lengkap'], 
            'nama_lingkungan' => $formData['nama_lingkungan'],
            'status' => 'Menunggu'
        ]);

        Notification::make()
            ->title('Pengajuan berhasil dibuat')
            ->icon('heroicon-o-document-text')
            ->iconColor('success')
            ->send();
    }
}
