<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserProfile extends Component
{
    public $nombre;
    public $apellido;
    public $sexo;
    public $fecha_nacimiento;
    public $email;
    public $password;
    public $password_confirmation;

    public function mount()
    {
        $user = auth()->user();
        $this->nombre = $user->nombre;
        $this->apellido = $user->apellido;
        $this->sexo = $user->sexo;
        $this->fecha_nacimiento = $user->fecha_nacimiento;
        $this->email = $user->email;
    }

    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'sexo' => 'required|in:M,F',
            'fecha_nacimiento' => 'required|date',
            'email' => ['required', 'email', Rule::unique('persona')->ignore(auth()->id())],
            'password' => 'nullable|min:8|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'sexo.required' => 'El sexo es obligatorio.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El formato del correo es inválido.',
            'email.unique' => 'Este correo ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    public function updateProfile()
    {
        $this->validate();

        $user = User::find(auth()->id());
        $user->update([
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'sexo' => $this->sexo,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'email' => $this->email,
        ]);

        if ($this->password) {
            $user->update([
                'password' => Hash::make($this->password),
            ]);
            $this->password = '';
            $this->password_confirmation = '';
        }

        session()->flash('message', 'Perfil actualizado con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function render()
    {
        return view('livewire.user-profile');
    }
}
