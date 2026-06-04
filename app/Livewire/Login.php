<?php

namespace App\Livewire;

use App\Helpers\DbHelper;
use App\Models\User;
use App\Services\IntranetSimulationMirrorService;
use App\Services\UserRoleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Login extends Component
{
    public string $usuario = '';
    public string $password = '';
    public string $error = '';
    public bool $cargando = false;

    public function login()
    {
        $this->error = '';
        $this->cargando = true;

        $inputTrim  = trim($this->usuario);
        $passTrim   = trim($this->password);

        if (empty($inputTrim) || empty($passTrim)) {
            $this->error = 'Por favor ingrese su usuario y contraseña.';
            $this->cargando = false;
            return;
        }

        try {
            $connection = DbHelper::connection();

            $extUser = \Illuminate\Support\Facades\DB::connection($connection)
                ->table('usuario')
                ->where(function ($q) use ($inputTrim) {
                    $q->whereRaw('TRIM(usu_nombre) = ?', [$inputTrim])
                      ->orWhereRaw('TRIM(usu_cedula) = ?', [$inputTrim]);
                })
                ->select(['usu_cedula', 'usu_nombre', 'usu_clave'])
                ->first();

            if (! $extUser) {
                $this->error = 'Usuario o contraseña incorrectos.';
                $this->cargando = false;
                return;
            }

            if (! password_verify(strtoupper($passTrim), trim($extUser->usu_clave ?? ''))) {
                $this->error = 'Usuario o contraseña incorrectos.';
                $this->cargando = false;
                return;
            }

            $cedula = trim($extUser->usu_cedula);

            $user = User::on($connection)->whereRaw('TRIM(usu_cedula) = ?', [$cedula])->first();

            if (! $user) {
                $this->error = 'No se pudo cargar el usuario. Intente de nuevo.';
                $this->cargando = false;
                return;
            }

            request()->session()->regenerate();

            Auth::login($user, true);

            try {
                app(IntranetSimulationMirrorService::class)->mirrorUserContext($cedula);
            } catch (\Throwable) {}

            $roleService = app(UserRoleService::class);
            $roleService->bootstrapSessionRole($user);

            if ($roleService->getActiveRole($user) === null) {
                return $this->redirect(route('acceso-rol.index'), navigate: false);
            }

            return $this->redirect(route('dashboard'), navigate: false);

        } catch (\Throwable $e) {
            Log::error('Login error: ' . $e->getMessage());
            $this->error = 'Error de conexión. Por favor intente de nuevo.';
            $this->cargando = false;
        }
    }

    public function render()
    {
        return view('livewire.login');
    }
}
