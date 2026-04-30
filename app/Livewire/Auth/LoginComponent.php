<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Login')]
class LoginComponent extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    public function login(): void
    {
        $credentials = $this->validate();

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        request()->session()->regenerate();

        $this->redirectIntended(default: route('dashboard'), navigate: false);
    }

    public function render()
    {
        return view('livewire.auth.login-component');
    }
}
