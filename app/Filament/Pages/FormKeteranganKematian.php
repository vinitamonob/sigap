<?php

namespace App\Filament\Pages;

use App\Models\KeteranganKematian;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;

class FormKeteranganKematian extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Form Pengajuan';

    protected static ?string $navigationLabel = 'Keterangan Kematian';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.form-keterangan-kematian';

    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Label')
                    ->schema([
                        TextInput::make('nama_lingkungan')
                            ->required()
                            ->label('Nama Lingkungan / Stasi')
                            ->default(fn () => Auth::user()->nama_lingkungan)
                            ->readOnly()
                            ->maxLength(255),
                        TextInput::make('paroki')
                            ->required()
                            ->label('Paroki')
                            ->default('St. Stephanus Cilacap')
                            ->readOnly()
                            ->maxLength(255),
                        DatePicker::make('tanggal_surat')
                            ->required()
                            ->label('Tanggal Surat')
                            ->default(Carbon::now())
                            ->readOnly(),
                    ]),
                    Fieldset::make('Data Kematian')
                        ->schema([
                            TextInput::make('nama_lengkap')
                                ->required()
                                ->label('Nama Lengkap')
                                ->maxLength(255),
                            TextInput::make('usia')
                                ->required()
                                ->label('Usia')
                                ->numeric()
                                ->minValue(0),
                            TextInput::make('nama_orang_tua')
                                ->required()
                                ->label('Nama Orang Tua')
                                ->maxLength(255),
                            TextInput::make('nama_pasangan')
                                ->required()
                                ->label('Nama Pasangan')
                                ->maxLength(255),
                            DatePicker::make('tanggal_kematian')
                                ->required()
                                ->label('Tanggal Kematian'),
                            DatePicker::make('tanggal_pemakaman')
                                ->required()
                                ->label('Tanggal Pemakaman'),
                            TextInput::make('tempat_pemakaman')
                                ->required()
                                ->label('Tempat Pemakaman')
                                ->maxLength(255),
                            TextInput::make('pelayan_sakramen')
                                ->required()
                                ->label('Pelayanan Sakramen')
                                ->maxLength(255),
                            TextInput::make('sakramen_yang_diberikan')
                                ->required()
                                ->label('Sakramen yang Diberikan')
                                ->maxLength(255),
                            TextInput::make('tempat_no_buku_baptis')
                                ->required()
                                ->label('Tempat & No. Buku Baptis')
                                ->maxLength(255),
                        ])
            ])
            ->statePath('data');
    }
    
    public function create(): void
    {
        KeteranganKematian::create($this->form->getState());
    }
}
