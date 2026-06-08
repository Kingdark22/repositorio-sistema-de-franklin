<?php

namespace App\Livewire;

use App\Models\RolExterno;
use App\Models\UsuarioExterno;
use App\Services\UserRoleService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SistemaRolManager extends Component
{
    public string $busquedaCedula = '';

    public string $cedulaTarget   = '';
    public string $nombreTarget   = ''; // Mantener por compatibilidad con validación de UI
    public ?int   $rolSeleccionado = null;

    public string $mensaje     = '';
    public string $tipoMensaje = 'success';

    protected function esGestionador(): bool
    {
        $user = Auth::user();

        return $user && app(UserRoleService::class)->esGestionador($user);
    }

    public function asignarRol(): void
    {
        if (! $this->esGestionador()) {
            return;
        }

        $repositorioConn = (string) config('dual_database.repositorio_connection', 'mysql');

        $this->validate([
            'cedulaTarget'    => 'required|min:6|max:20',
            'nombreTarget'    => 'required|min:3|max:120',
            'rolSeleccionado' => "required|exists:{$repositorioConn}.rol_externo,rex_codigo",
        ], [
            'cedulaTarget.required'    => 'La cédula es obligatoria.',
            'nombreTarget.required'    => 'El nombre es obligatorio.',
            'rolSeleccionado.required' => 'Seleccione un rol.',
            'rolSeleccionado.exists'   => 'El rol seleccionado no existe.',
        ]);

        $cedula = trim($this->cedulaTarget);

        // Verificar si ya tiene ese rol
        $existe = UsuarioExterno::where('uex_nombre', $cedula)
            ->where('uex_rex_codigo', $this->rolSeleccionado)
            ->first();

        if ($existe) {
            if ($existe->uex_estado == 1) {
                $this->notificar('Este usuario ya tiene ese rol asignado.', 'error');
                return;
            }
            // Reactivar
            $existe->update(['uex_estado' => 1]);
            $this->notificar('Rol reactivado correctamente.');
        } else {
            UsuarioExterno::create([
                'uex_nombre'     => $cedula,
                'uex_contrasena' => password_hash($cedula, PASSWORD_BCRYPT), // Contraseña por defecto (su propia cédula)
                'uex_rex_codigo' => $this->rolSeleccionado,
                'uex_estado'     => 1,
            ]);
            $this->notificar('Rol asignado correctamente.');
        }

        $this->resetFormAsignacion();
    }

    public function revocarRol(int $id): void
    {
        if (! $this->esGestionador()) {
            return;
        }

        UsuarioExterno::where('uex_codigo', $id)->update(['uex_estado' => 0]);
        $this->notificar('Rol revocado.');
    }

    protected function resetFormAsignacion(): void
    {
        $this->cedulaTarget    = '';
        $this->nombreTarget    = '';
        $this->rolSeleccionado = null;
        $this->resetValidation();
    }

    protected function notificar(string $msg, string $tipo = 'success'): void
    {
        $this->mensaje     = $msg;
        $this->tipoMensaje = $tipo;
    }

    public function limpiarMensaje(): void
    {
        $this->mensaje = '';
    }

    public function render()
    {
        if (! $this->esGestionador()) {
            return view('livewire.sistema-rol-manager', [
                'accesoDenegado' => true,
                'roles'          => collect(),
                'asignaciones'   => collect(),
            ]);
        }

        $roles = RolExterno::orderBy('rex_nombre')->get();

        // Usuarios externos con roles asignados (activos)
        $asignaciones = UsuarioExterno::with('rol')
            ->where('uex_estado', 1)
            ->when($this->busquedaCedula, fn ($q) =>
                $q->where('uex_nombre', 'like', '%' . trim($this->busquedaCedula) . '%')
            )
            ->orderBy('uex_nombre')
            ->get();

        return view('livewire.sistema-rol-manager', [
            'accesoDenegado' => false,
            'roles'          => $roles,
            'asignaciones'   => $asignaciones,
        ]);
    }
}
