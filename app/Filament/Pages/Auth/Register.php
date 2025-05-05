<?php

namespace App\Filament\Pages\Auth;

use App\Models\Role;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as AuthRegister;
use Illuminate\Database\Eloquent\Model;

class Register extends AuthRegister
{
    
    public function form(Form $form): Form
    {
        return $form
        ->schema([
            $this->getNameFormComponent()
        ]);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent()
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function handleRegistration(array $data): Model
    {
        $user = $this->getUserModel()::create($data);
        $role = Role::where('name', 'umat')->first();
        if(!$role) {
            $role = Role::create([
                'name' => 'umat'
            ]);
        }
        $user->assignRole('umat');

        // kirim email verifikasi

        return $user;
    } 
}
