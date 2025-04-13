<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use App\Models\PendaftaranKanonikPerkawinan;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class FormPendaftaranKanonikPerkawinan extends Page implements HasForms
{    
    use InteractsWithForms;
    
    protected static ?string $navigationGroup = 'Form Pengajuan';

    protected static ?string $navigationLabel = 'Pendaftaran Kanonik Perkawinan';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.form-pendaftaran-kanonik-perkawinan';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Data Calon Istri')
                    ->schema([
                        TextInput::make('nama_istri')
                            ->required()
                            ->label('Nama Lengkap Calon Istri')
                            ->maxLength(255),
                        TextInput::make('tempat_lahir_istri')
                            ->required()
                            ->label('Tempat Lahir Calon Istri')
                            ->maxLength(255),
                        DatePicker::make('tanggal_lahir_istri')
                            ->required()
                            ->label('Tanggal Lahir Calon Istri'),
                        Textarea::make('alamat_sekarang_istri')
                            ->required()
                            ->label('Alamat Sekarang Calon Istri')
                            ->columnSpanFull(),
                        Textarea::make('alamat_setelah_menikah_istri')
                            ->required()
                            ->label('Alamat Setelah Menikah Calon Istri')
                            ->columnSpanFull(),
                        TextInput::make('telepon_istri')
                            ->tel()
                            ->required()
                            ->label('Telepon Calon Istri')
                            ->maxLength(255),
                        TextInput::make('pekerjaan_istri')
                            ->required()
                            ->label('Pekerjaan Calon Istri')
                            ->maxLength(255),
                        Select::make('pendidikan_terakhir_istri')
                            ->required()
                            ->label('Pendidikan Terakhir Calon Istri')
                            ->options([
                                'TK' => 'TK',
                                'SD' => 'SD',
                                'SMP' => 'SMP',
                                'SMA' => 'SMA',
                                'Diploma/Sarjana' => 'Diploma/Sarjana',
                            ]),
                        Select::make('agama_istri')
                            ->required()
                            ->label('Agama Calon Istri')
                            ->options([
                                'Katolik' => 'Katolik',
                                'Protestan' => 'Protestan',
                                'Islam' => 'Islam',
                                'Hindu' => 'Hindu',
                                'Budha' => 'Budha',
                            ]),
                        TextInput::make('tempat_baptis_istri')
                            ->maxLength(255)
                            ->label('Tempat Baptis Calon Istri'),
                        DatePicker::make('tanggal_baptis_istri')
                            ->label('Tanggal Baptis Calon Istri'),
                        SignaturePad::make('tanda_tangan_calon_istri')
                            ->label('Tanda Tangan Calon Istri'),
                        
                        Fieldset::make('Data Orang Tua')
                            ->schema([
                                TextInput::make('nama_ayah_istri')
                                    ->required()
                                    ->label('Nama Ayah Calon Istri')
                                    ->maxLength(255),
                                Select::make('agama_ayah_istri')
                                    ->required()
                                    ->label('Agama Ayah Calon Istri')
                                    ->options([
                                        'Katolik' => 'Katolik',
                                        'Protestan' => 'Protestan',
                                        'Islam' => 'Islam',
                                        'Hindu' => 'Hindu',
                                        'Budha' => 'Budha',
                                    ]),
                                TextInput::make('pekerjaan_ayah_istri')
                                    ->required()
                                    ->label('Pekerjaan Ayah Calon Istri')
                                    ->maxLength(255),
                                Textarea::make('alamat_ayah_istri')
                                    ->required()
                                    ->label('Alamat Ayah Calon Istri')
                                    ->columnSpanFull(),
                                TextInput::make('nama_ibu_istri')
                                    ->required()
                                    ->label('Nama Ibu Calon Istri')
                                    ->maxLength(255),
                                Select::make('agama_ibu_istri')
                                    ->required()
                                    ->label('Agama Ibu Calon Istri')
                                    ->options([
                                        'Katolik' => 'Katolik',
                                        'Protestan' => 'Protestan',
                                        'Islam' => 'Islam',
                                        'Hindu' => 'Hindu',
                                        'Budha' => 'Budha',
                                    ]),
                                TextInput::make('pekerjaan_ibu_istri')
                                    ->required()
                                    ->label('Pekerjaan Ibu Calon Istri')
                                    ->maxLength(255),
                                Textarea::make('alamat_ibu_istri')
                                    ->required()
                                    ->label('Alamat Ibu Calon Istri')
                                    ->columnSpanFull(),
                            ]),   
                            Fieldset::make('Data Lingkungan')
                                ->schema([
                                    TextInput::make('nama_ketua_istri')
                                        ->required()
                                        ->label('Nama Ketua Lingkungan Calon Istri')
                                        ->maxLength(255),
                                    TextInput::make('nama_lingkungan_istri')
                                        ->required()
                                        ->label('Nama Lingkungan / Stasi Calon Istri')
                                        ->maxLength(255),
                                    TextInput::make('wilayah_istri')
                                        ->required()
                                        ->label('Wilayah Calon Istri')
                                        ->label('Wilayah')
                                        ->maxLength(255),
                                    TextInput::make('paroki_istri')
                                        ->required()
                                        ->label('Paroki Calon Istri')
                                        ->label('Paroki')
                                        ->maxLength(255),
                                    SignaturePad::make('tanda_tangan_ketua_istri')
                                        ->label('Tanda Tangan Ketua Lingkungan Calon Istri'),
                                ])                          
                    ]),

                    Fieldset::make('Data Calon Suami')
                        ->schema([
                            TextInput::make('nama_suami')
                                ->required()
                                ->label('Nama Lengkap Calon Suami')
                                ->maxLength(255),
                            TextInput::make('tempat_lahir_suami')
                                ->required()
                                ->label('Tempat Lahir Calon Suami')
                                ->maxLength(255),
                            DatePicker::make('tanggal_lahir_suami')
                                ->required()
                                ->label('Tanggal Lahir Calon Suami'),
                            Textarea::make('alamat_sekarang_suami')
                                ->required()
                                ->label('Alamat Sekarang Calon Suami')
                                ->columnSpanFull(),
                            Textarea::make('alamat_setelah_menikah_suami')
                                ->required()
                                ->label('Alamat Setelah Menikah Calon Suami')
                                ->columnSpanFull(),
                            TextInput::make('telepon_suami')
                                ->tel()
                                ->required()
                                ->label('Telepon Calon Suami')
                                ->maxLength(255),
                            TextInput::make('pekerjaan_suami')
                                ->required()
                                ->label('Pekerjaan Calon Suami')
                                ->maxLength(255),
                            Select::make('pendidikan_terakhir_suami')
                                ->required()
                                ->label('Pendidikan Terakhir Calon Suami')
                                ->options([
                                    'TK' => 'TK',
                                    'SD' => 'SD',
                                    'SMP' => 'SMP',
                                    'SMA' => 'SMA',
                                    'Diploma/Sarjana' => 'Diploma/Sarjana',
                                ]),
                            Select::make('agama_suami')
                                ->required()
                                ->label('Agama Calon Suami')
                                ->options([
                                    'Katolik' => 'Katolik',
                                    'Protestan' => 'Protestan',
                                    'Islam' => 'Islam',
                                    'Hindu' => 'Hindu',
                                    'Budha' => 'Budha',
                                ]),
                            TextInput::make('tempat_baptis_suami')
                                ->maxLength(255)
                                ->label('Tempat Baptis Calon Suami'),
                            DatePicker::make('tanggal_baptis_suami')
                                ->label('Tanggal Baptis Calon Suami'),
                            SignaturePad::make('tanda_tangan_calon_suami')
                                ->label('Tanda Tangan Calon Suami'),

                            Fieldset::make('Data Orang Tua')
                                ->schema([
                                    TextInput::make('nama_ayah_suami')
                                        ->required()
                                        ->label('Nama Lengkap Ayah Calon Suami')
                                        ->maxLength(255),
                                    Select::make('agama_ayah_suami')
                                        ->required()
                                        ->label('Agama Ayah Calon Suami')
                                        ->options([
                                            'Katolik' => 'Katolik',
                                            'Protestan' => 'Protestan',
                                            'Islam' => 'Islam',
                                            'Hindu' => 'Hindu',
                                            'Budha' => 'Budha',
                                        ]),
                                    TextInput::make('pekerjaan_ayah_suami')
                                        ->required()
                                        ->label('Pekerjaan Ayah Calon Suami')
                                        ->maxLength(255),
                                    Textarea::make('alamat_ayah_suami')
                                        ->required()
                                        ->label('Alamat Ayah Calon Suami')
                                        ->columnSpanFull(),
                                    TextInput::make('nama_ibu_suami')
                                        ->required()
                                        ->label('Nama Lengkap Ibu Calon Suami')
                                        ->maxLength(255),
                                    Select::make('agama_ibu_suami')
                                        ->required()
                                        ->label('Agama Ibu Calon Suami')
                                        ->options([
                                            'Katolik' => 'Katolik',
                                            'Protestan' => 'Protestan',
                                            'Islam' => 'Islam',
                                            'Hindu' => 'Hindu',
                                            'Budha' => 'Budha',
                                        ]),
                                    TextInput::make('pekerjaan_ibu_suami')
                                        ->required()
                                        ->label('Pekerjaan Ibu Calon Suami')
                                        ->maxLength(255),
                                    Textarea::make('alamat_ibu_suami')
                                        ->required()
                                        ->label('Alamat Ibu Calon Suami')
                                        ->columnSpanFull(),
                                ]),
                                Fieldset::make('Data Lingkungan')
                                    ->schema([
                                        TextInput::make('nama_ketua_suami')
                                            ->required()
                                            ->label('Nama Ketua Lingkungan Calon Suami')
                                            ->maxLength(255),
                                        TextInput::make('nama_lingkungan_suami')
                                            ->required()
                                            ->label('Nama Lingkungan / Stasi Calon Suami')
                                            ->maxLength(255),
                                        TextInput::make('wilayah_suami')
                                            ->required()
                                            ->label('Wilayah Calon Suami')
                                            ->maxLength(255),
                                        TextInput::make('paroki_suami')
                                            ->required()
                                            ->label('Paroki Calon Suami')
                                            ->maxLength(255),
                                        SignaturePad::make('tanda_tangan_ketua_suami')
                                            ->label('Tanda Tangan Ketua Lingkungan Calon Suami'),
                                    ])
                        ]),

                        Fieldset::make('Data Perkawinan')
                            ->schema([
                                Fieldset::make('Label')
                                    ->schema([
                                        DatePicker::make('tanggal_daftar')
                                            ->required()
                                            ->label('Tanggal Daftar')
                                            ->default(now())
                                            ->readOnly(),
                                    ]),
                                TextInput::make('lokasi_gereja')
                                    ->required()
                                    ->label('Lokasi Gereja')
                                    ->maxLength(255),
                                DatePicker::make('tanggal_pernikahan')
                                    ->required()
                                    ->label('Tanggal Pernikahan'),
                                TimePicker::make('waktu_pernikahan')
                                    ->required()
                                    ->label('Waktu Pernikahan'),
                                ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
       // Array tanda tangan yang perlu diproses
       $tandaTanganFields = [
        'tanda_tangan_ketua_istri',
        'tanda_tangan_ketua_suami',
        'tanda_tangan_calon_istri',
        'tanda_tangan_calon_suami'
        ];

        foreach ($tandaTanganFields as $field) {
            if (isset($this->data[$field]) && !empty($this->data[$field])) {
                $image = $this->data[$field];
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10) . '.png';
                File::put(storage_path() . '/' . $imageName, base64_decode($image));
                
                $this->data[$field] = $imageName;
            }
        }
        // dd($this->form->getState());
        PendaftaranKanonikPerkawinan::create($this->form->getState());
    }
}