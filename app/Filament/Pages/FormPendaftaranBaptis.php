<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use App\Models\PendaftaranBaptis;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\File;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class FormPendaftaranBaptis extends Page implements HasForms
{
    use InteractsWithForms;
    
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
                Fieldset::make('Data Pendaftar')
                    ->schema([
                        TextInput::make('nama_lengkap')
                            ->required()
                            ->label('Nama Lengkap')
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
                        DatePicker::make('tanggal_lahir')
                            ->required()
                            ->label('Tanggal Lahir'),
                        Textarea::make('alamat_lengkap')
                            ->required()
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),
                        TextInput::make('nomor_telepon')
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
                                'Protestan' => 'Protestan'
                            ]),
                        Select::make('pendidikan_terakhir')
                            ->required()
                            ->label('Pendidikan Terakhir')
                            ->options([
                                'TK' => 'TK',
                                'SD' => 'SD',
                                'SMP' => 'SMP',
                                'SMA' => 'SMA',
                                'Diploma/Sarjana' => 'Diploma/Sarjana',
                            ]),
                            DatePicker::make('tanggal_mulai_belajar')
                                ->required()
                                ->label('Tanggal Mulai Pembelajaran'),
                            TextInput::make('nama_wali_baptis')
                                ->required()
                                ->label('Nama Wali Baptis')
                                ->maxLength(255),
                            Textarea::make('alasan_masuk_katolik')
                                ->required()
                                ->label('Alasan Masuk Katolik')
                                ->columnSpanFull(),
                            DatePicker::make('tanggal_baptis')
                                ->required(),       
                    ]),
                    Fieldset::make('Data Keluarga')
                        ->schema([
                            TextInput::make('nama_ayah')
                                ->required()
                                ->label('Nama Ayah')
                                ->maxLength(255),
                            TextInput::make('agama_ayah')
                                ->required()
                                ->label('Agama Ayah')
                                ->maxLength(255),
                            TextInput::make('nama_ibu')
                                ->required()
                                ->label('Nama Ibu')
                                ->maxLength(255),
                            TextInput::make('agama_ibu')
                                ->required()
                                ->label('Agama Ibu')
                                ->maxLength(255),
                            TextInput::make('nama_keluarga_katolik_1')
                                ->maxLength(255)
                                ->label('Nama Keluarga 1'),
                            TextInput::make('hubungan_keluarga_katolik_1')
                                ->maxLength(255)
                                ->label('Hubungan Keluarga 1'),
                            TextInput::make('nama_keluarga_katolik_2')
                                ->maxLength(255)
                                ->label('Nama Keluarga 2'),
                            TextInput::make('hubungan_keluarga_katolik_2')
                                ->maxLength(255)
                                ->label('Hubungan Keluarga 2'),
                            Textarea::make('alamat_keluarga')
                                ->required()
                                ->label('Alamat Lengkap')
                                ->columnSpanFull(),
                            SignaturePad::make('tanda_tangan_ortu')
                                ->label('Tanda Tangan Orang Tua'),
                        ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $image = $this->data['tanda_tangan_ortu'];  // your base64 encoded
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::random(10).'.'.'png';
        File::put(storage_path(). '/' . $imageName, base64_decode($image));

        $this->data['tanda_tangan_ortu'] = $imageName;
        // dd($this->form->getState());
        PendaftaranBaptis::create($this->form->getState());
    }
}
