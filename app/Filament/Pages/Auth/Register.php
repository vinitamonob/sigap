<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Filament\Pages\Auth\Register as AuthRegister;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class Register extends AuthRegister
{
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

        return $user;
    } 
}
