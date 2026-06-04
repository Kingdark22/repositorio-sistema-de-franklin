<div style="font-family: Arial, Helvetica, sans-serif; margin-top: 10px;">
    <style>
        .cm-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            padding: 0.55rem 0.95rem;
            font-size: 0.92rem;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
            text-decoration: none;
        }

        .cm-btn:hover {
            transform: translateY(-1px);
        }

        .cm-btn-primary {
            background: #19692e;
            border-color: #154f26;
            color: #fff;
        }

        .cm-btn-secondary {
            background: #f4f4f4;
            border-color: #c2c2c2;
            color: #222;
        }

        .cm-btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }
    </style>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder;">Actualizar Registro</h2>

    @if (session()->has('message'))
        <div
            style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="updateProfile" style="margin: 0; padding: 0;">
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px 15px 15px 15px; margin-top: 10px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Datos Personales</legend>
            <table width="100%" border="0" cellpadding="2" cellspacing="0">
                <tr>
                    <td width="33%"><b>Nombres:</b></td>
                    <td width="33%"><b>Apellidos:</b></td>
                    <td width="34%"><b>Email:</b></td>
                </tr>
                <tr>
                    <td valign="top">
                        <input wire:model="nombre" type="text">
                        <span class="obligatorio">*</span>
                        @error('nombre')
                            <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                        @enderror
                    </td>
                    <td valign="top">
                        <input wire:model="apellido" type="text">
                        <span class="obligatorio">*</span>
                        @error('apellido')
                            <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                        @enderror
                    </td>
                    <td valign="top">
                        <input wire:model="email" type="email">
                        <span class="obligatorio">*</span>
                        @error('email')
                            <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td><b>G&eacute;nero:</b></td>
                    <td><b>Fecha de Nacimiento:</b></td>
                    <td></td>
                </tr>
                <tr>
                    <td valign="top" style="padding-top: 12px;">
                        <input wire:model="sexo" type="radio" value="F"> Femenino
                        &nbsp;&nbsp;
                        <input wire:model="sexo" type="radio" value="M"> Masculino
                        <span class="obligatorio">*</span>
                        @error('sexo')
                            <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                        @enderror
                    </td>
                    <td valign="top">
                        <input wire:model="fecha_nacimiento" type="date">
                        <span class="obligatorio">*</span>
                        @error('fecha_nacimiento')
                            <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                        @enderror
                    </td>
                    <td valign="top">
                    </td>
                </tr>
            </table>
        </fieldset>

        <br>

        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px 15px 15px 15px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Seguridad y Acceso
            </legend>
            <table width="100%" border="0" cellpadding="2" cellspacing="0">
                <tr>
                    <td width="50%"><b>Nueva Contrase&ntilde;a (Opcional):</b></td>
                    <td width="50%"><b>Confirmar Contrase&ntilde;a:</b></td>
                </tr>
                <tr>
                    <td valign="top">
                        <input wire:model="password" type="password">
                        @error('password')
                            <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                        @enderror
                    </td>
                    <td valign="top">
                        <input wire:model="password_confirmation" type="password">
                    </td>
                </tr>
            </table>
        </fieldset>

        <div style="margin-top: 15px; font-size: 13px;">
            Los campos con <span class="obligatorio">*</span> son obligatorios
        </div>
        <div style="text-align: center; margin-top: 10px; margin-bottom: 20px;">
            <button type="submit" class="cm-btn cm-btn-primary">Guardar</button>
        </div>
    </form>
</div>
