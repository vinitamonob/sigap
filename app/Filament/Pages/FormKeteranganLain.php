<?php

namespace App\Filament\Pages;

use App\Models\KeteranganLain;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;

class FormKeteranganLain extends Page implements HasForms
{
    use InteractsWithForms;
    
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
                            TextInput::make('telepon_rumah')
                                ->tel()
                                ->label('No. Telepon Rumah')
                                ->maxLength(255),
                            TextInput::make('telepon_kantor')
                                ->tel()
                                ->label('No. Telepon Kantor')
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
        KeteranganLain::create($this->form->getState());
    }
}
