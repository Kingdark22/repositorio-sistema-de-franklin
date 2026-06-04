@section('title', 'Gestión de Roles del Sistema')
@section('header', 'Gestión de Roles del Sistema')

<div>

{{-- ACCESO DENEGADO --}}
@if($accesoDenegado)
<p style="color:red; font-weight:bold; text-align:center; margin-top:30px;">
    Acceso restringido. Solo el Gestionador puede administrar roles del sistema.
</p>
@else

{{-- MENSAJE --}}
@if($mensaje)
<p style="color:{{ $tipoMensaje === 'success' ? 'green' : 'red' }}; font-weight:bold; margin-bottom:8px;">
    {{ $mensaje }}
    <a href="#" wire:click.prevent="limpiarMensaje" style="font-size:11px; margin-left:8px; color:#888;">[X]</a>
</p>
@endif

{{-- FORMULARIO ASIGNACIÓN --}}
<fieldset>
    <legend>Asignar Rol a Usuario Externo</legend>

    <table border="0" cellpadding="5" cellspacing="2">
        <tr>
            <td class="obligatorio">Cédula del usuario *</td>
            <td>
                <input type="text" wire:model="cedulaTarget" placeholder="Ej: 12345678"
                       style="width:160px; text-transform:none;">
                @error('cedulaTarget')<br><span style="color:red;font-size:11px;">{{ $message }}</span>@enderror
            </td>
        </tr>
        <tr>
            <td class="obligatorio">Nombre / Referencia *</td>
            <td>
                <input type="text" wire:model="nombreTarget" placeholder="Nombre completo"
                       style="width:240px;">
                @error('nombreTarget')<br><span style="color:red;font-size:11px;">{{ $message }}</span>@enderror
            </td>
        </tr>
        <tr>
            <td class="obligatorio">Rol del Sistema *</td>
            <td>
                <select wire:model="rolSeleccionado">
                    <option value="">-- Seleccione --</option>
                    @foreach($roles as $rol)
                        <option value="{{ $rol->rex_codigo }}">{{ $rol->rex_nombre }}</option>
                    @endforeach
                </select>
                @error('rolSeleccionado')<br><span style="color:red;font-size:11px;">{{ $message }}</span>@enderror
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <button class="boton" wire:click="asignarRol" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="asignarRol">Asignar Rol</span>
                    <span wire:loading wire:target="asignarRol">Guardando...</span>
                </button>
            </td>
        </tr>
    </table>
</fieldset>

<br>

{{-- TABLA DE ASIGNACIONES ACTIVAS --}}
<fieldset>
    <legend>Usuarios Externos con Roles Asignados</legend>

    <table width="100%" border="0" cellpadding="3">
        <tr>
            <td>
                <input type="text" wire:model.live.debounce.400ms="busquedaCedula"
                       placeholder="Filtrar por cédula..."
                       style="width:160px; text-transform:none;">
            </td>
        </tr>
    </table>

    <br>

    @if($asignaciones->isEmpty())
        <p style="color:#666; margin-left:10px;">No hay usuarios con roles del sistema asignados.</p>
    @else
    <table class="tabla" width="100%" border="1" cellpadding="4" cellspacing="0"
           style="border-collapse:collapse; font-size:13px;">
        <thead>
            <tr style="background:#C3E0E4; font-weight:bold;">
                <th align="left">Cédula / Usuario</th>
                <th align="left">Rol Asignado</th>
                <th align="left">Estado</th>
                <th align="center" width="100">Acción</th>
            </tr>
        </thead>
        <tbody>
        @foreach($asignaciones as $asig)
            <tr>
                <td>{{ $asig->uex_nombre }}</td>
                <td>{{ $asig->rol?->rex_nombre ?? '-' }}</td>
                <td><span style="color:green; font-weight:bold;">Activo</span></td>
                <td align="center">
                    <button class="boton"
                            wire:click="revocarRol({{ $asig->uex_codigo }})"
                            wire:confirm="¿Revocar este rol al usuario?"
                            style="font-size:11px; padding:0 8px; background:#c00;">
                        Revocar
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif

</fieldset>

@endif

</div>
