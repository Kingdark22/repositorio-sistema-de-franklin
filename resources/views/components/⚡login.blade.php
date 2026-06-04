<?php

use App\Helpers\DbHelper;
use App\Models\User;
use App\Services\IntranetSimulationMirrorService;
use App\Services\UserRoleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

new class extends Component
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

            // Buscar por cédula o nombre de usuario
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

            // Verificar contraseña (almacenada con password_hash, entrada en mayúsculas)
            if (! password_verify(strtoupper($passTrim), trim($extUser->usu_clave ?? ''))) {
                $this->error = 'Usuario o contraseña incorrectos.';
                $this->cargando = false;
                return;
            }

            $cedula = trim($extUser->usu_cedula);

            // Buscar modelo User para Auth::login
            $user = User::on($connection)->whereRaw('TRIM(usu_cedula) = ?', [$cedula])->first();

            if (! $user) {
                $this->error = 'No se pudo cargar el usuario. Intente de nuevo.';
                $this->cargando = false;
                return;
            }

            // Regenerar sesión para evitar session fixation
            request()->session()->regenerate();

            Auth::login($user, true);

            // Espejear contexto de intranet en simulación si aplica
            try {
                app(IntranetSimulationMirrorService::class)->mirrorUserContext($cedula);
            } catch (\Throwable) {}

            // Configurar rol inicial
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
};
?>

<div id="contenedor">
    <div id="arriba">
        <img src="{{ asset('imagenes/barras.jpeg') }}" alt="Encabezado Institucional" style="width: 100%; height: 100%; object-fit: fill; display: block;">
    </div>

    <div id="centro_login">
        <h2 style="font-size: 22px; font-weight: bold; margin-top: 30px; margin-bottom: 30px;">
            Inicie Sesión en el Software para la Gestión Académica
        </h2>

        <form wire:submit="login">
            <table align="center" style="margin-bottom: 20px;">
                <tr>
                    <td align="right" style="font-weight: bold; padding: 10px 10px 10px 0; font-size: 15px;">Usuario:</td>
                    <td align="left" style="padding: 5px 0;">
                        <input wire:model="usuario" id="inp-usuario" type="text" placeholder="CÉDULA O USUARIO" required autocomplete="username">
                        <span style="color: red; font-weight: bold; font-size: 16px; margin-left: 5px;">*</span>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="font-weight: bold; padding: 10px 10px 10px 0; font-size: 15px;">Contraseña:</td>
                    <td align="left" style="padding: 5px 0;">
                        <input wire:model="password" id="inp-password" type="password" placeholder="CONTRASEÑA" required autocomplete="current-password">
                        <span style="color: red; font-weight: bold; font-size: 16px; margin-left: 5px;">*</span>
                    </td>
                </tr>
            </table>

            @if($error)
                <div style="color: red; font-weight: bold; margin-bottom: 20px;">
                    {{ $error }}
                </div>
            @endif

            <div style="margin-bottom: 30px;">
                <button type="submit" id="btn-login" class="boton" style="margin-bottom: 30px;" wire:loading.attr="disabled">
                    <span wire:loading.remove>Iniciar sesión</span>
                    <span wire:loading>Verificando...</span>
                </button>
            </div>
        </form>

        <div style="text-align: left; padding: 0 10px; margin-top: 80px;">
            <p style="margin-bottom: 15px; font-size: 14px;">Los campos con <span style="color: red; font-weight: bold;">*</span> son obligatorios</p>
            <p style="margin: 0; font-size: 12px; font-weight: normal; line-height: 1.4;">
                Nota:<br>
                -Si es la primera vez que ingresa, su usuario y contraseña es la cédula.<br>
                -Debe cambiar la contraseña cuando inicie sesión por primera vez.
            </p>
        </div>
    </div>

    <div id="abajo" style="margin-top: 0;">
        Todos los Derechos Reservados 2014 UPTP - Créditos Unidad de Sistemas / Desarrollo de Software.
    </div>
</div>
