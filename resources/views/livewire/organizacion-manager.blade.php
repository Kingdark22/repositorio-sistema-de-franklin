<div>
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
        .cm-btn-danger {
            background: #c82333;
            border-color: #a71d2a;
            color: #fff;
        }
        .cm-btn-secondary {
            background: #f4f4f4;
            border: 1px solid #c2c2c2;
            color: #222;
        }
        .cm-btn-success {
            background: #198754;
            border-color: #166f43;
            color: #fff;
        }
        .cm-btn-warning {
            background: #f0b606;
            border-color: #d99e00;
            color: #212529;
        }
        .cm-btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }
    </style>

    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Organizaciones</h2>

    @if($mensaje)
        <div style="background-color: {{ $tipoMensaje === 'success' ? '#d4edda' : '#f8d7da' }}; color: {{ $tipoMensaje === 'success' ? '#155724' : '#721c24' }}; border: 1px solid {{ $tipoMensaje === 'success' ? '#c3e6cb' : '#f5c6cb' }}; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:12px; display: flex; justify-content: space-between; align-items: center;">
            <span>{{ $mensaje }}</span>
            <a href="#" wire:click.prevent="limpiarMensaje" style="font-size: 16px; font-weight: bold; text-decoration: none; color: inherit;">&times;</a>
        </div>
    @endif

    @if($accesoDenegado)
        <p style="color:red; font-weight:bold; text-align:center; margin-top:30px;">
            Acceso restringido. Solo el Gestionador puede administrar organizaciones.
        </p>
    @elseif($orgSeleccionadaNombre)
        {{-- ═══════════════════════════════════════════════════
             PANEL DEPARTAMENTOS
             ═══════════════════════════════════════════════════ --}}
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
            <legend style="font-weight: bold; font-style: italic; padding: 0 5px;">
                Departamentos de: <strong>{{ $orgSeleccionadaNombre }}</strong>
            </legend>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <button type="button" wire:click="cerrarDepartamentos" class="cm-btn cm-btn-secondary cm-btn-sm">
                    &larr; Volver
                </button>
                <button type="button" wire:click="abrirFormNuevoDep" class="cm-btn cm-btn-success cm-btn-sm">
                    + Nuevo Departamento
                </button>
            </div>

            @if($mostrarFormDep)
                <fieldset style="border: 1px solid #ccc; border-radius: 4px; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size:12px; padding: 0 5px;">
                        {{ $editandoDepId ? 'Editar Departamento' : 'Registrar Departamento' }}
                    </legend>
                    <table width="100%" border="0" cellpadding="5" cellspacing="0" style="font-size: 11px;">
                        <tr>
                            <td width="15%"><b>Nombre:</b></td>
                            <td width="35%">
                                <input type="text" wire:model="dep_nombre" style="width: 90%;"> <span class="obligatorio">*</span>
                                @error('dep_nombre')<br><span style="color:red;font-size:10px;">{{ $message }}</span>@enderror
                            </td>
                            <td width="15%"><b>Se encarga de:</b></td>
                            <td width="35%">
                                <input type="text" wire:model="dep_cargo" style="width: 90%;" placeholder="Descripción opcional">
                                @error('dep_cargo')<br><span style="color:red;font-size:10px;">{{ $message }}</span>@enderror
                            </td>
                        </tr>
                    </table>

                    {{-- Contactos del departamento --}}
                    <div style="margin-top: 15px; border-top: 1px solid #ccc; padding-top: 12px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                            <b style="font-size: 12px;">Personas de Contacto</b>
                            <button type="button" wire:click="agregarContactoDep" class="cm-btn cm-btn-primary cm-btn-sm">+ Agregar contacto</button>
                        </div>
                        @if (empty($contactosDep))
                            <div style="background: #fdfdfd; border: 1px dashed #bbb; border-radius: 4px; padding: 12px; text-align: center; color: #555; font-size: 11px; font-style: italic;">
                                No hay contactos registrados para este departamento.
                            </div>
                        @else
                            @foreach ($contactosDep as $i => $ct)
                                <div style="border: 1px solid #bbb; border-radius: 4px; margin-bottom: 10px; overflow: hidden;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; background: #8bb2b7; font-size: 11px; font-weight: bold;">
                                        <span>Contacto #{{ $loop->iteration }}</span>
                                        <button type="button" wire:click="quitarContactoDep({{ $i }})" wire:confirm="¿Quitar este contacto?" class="cm-btn cm-btn-danger cm-btn-sm" style="padding: 2px 8px; font-size: 10px;">Quitar</button>
                                    </div>
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; padding: 10px; font-size: 11px;">
                                        <div>
                                            <label style="display:block; font-weight:bold; margin-bottom:3px;">Nombre:</label>
                                            <input wire:model="contactosDep.{{ $i }}.nombre" type="text" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;">
                                        </div>
                                        <div>
                                            <label style="display:block; font-weight:bold; margin-bottom:3px;">Apellido:</label>
                                            <input wire:model="contactosDep.{{ $i }}.apellido" type="text" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;">
                                        </div>
                                        <div>
                                            <label style="display:block; font-weight:bold; margin-bottom:3px;">Correo:</label>
                                            <input wire:model="contactosDep.{{ $i }}.correo" type="email" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;" placeholder="correo@ejemplo.com">
                                        </div>
                                        <div>
                                            <label style="display:block; font-weight:bold; margin-bottom:3px;">Teléfono:</label>
                                            <input wire:model="contactosDep.{{ $i }}.telefono" type="text" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;" placeholder="0412-1234567">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div style="margin-top: 12px; text-align: center;">
                        <button type="button" wire:click="cancelarFormDep" class="cm-btn cm-btn-danger cm-btn-sm" style="margin-right: 8px;">Cancelar</button>
                        <button type="button" wire:click="guardarDep" class="cm-btn cm-btn-primary cm-btn-sm">
                            {{ $editandoDepId ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </fieldset>
            @endif

            <table width="100%" border="1" cellpadding="5" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="5%">N&deg;</th>
                        <th width="25%">Nombre</th>
                        <th width="15%">Cargo</th>
                        <th width="40%">Contactos</th>
                        <th width="15%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @forelse($deps as $dep)
                        @php
                            $contactoList = $dep->contactos->map(fn($c) => trim($c->oco_nombre . ' ' . ($c->oco_apellido ?? '')))->filter()->implode(', ');
                        @endphp
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                            <td align="center">{{ $loop->iteration }}</td>
                            <td>{{ $dep->nombre }}</td>
                            <td>{{ $dep->cargo ?: '-' }}</td>
                            <td>{{ $contactoList ?: '-' }}</td>
                            <td align="center">
                                <div style="display: inline-flex; align-items: center; gap: 4px;">
                                    <button type="button" wire:click.prevent="editarDep({{ $dep->id }})"
                                        class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                    <button type="button" wire:click.prevent="eliminarDep({{ $dep->id }})"
                                        wire:confirm="¿Eliminar este departamento?"
                                        class="cm-btn cm-btn-danger cm-btn-sm">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" align="center" style="padding: 20px;">No hay departamentos registrados para esta organizaci&oacute;n.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </fieldset>
    @elseif($mostrarFormOrg)
        {{-- ═══════════════════════════════════════════════════
             FORMULARIO REGISTRO/EDICIÓN
             ═══════════════════════════════════════════════════ --}}
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $org_nombre_key ? 'Editar Organización' : 'Registrar Organización' }}
            </legend>
            <table width="100%" border="0" cellpadding="5" cellspacing="0" style="font-size: 11px;">
                <tr>
                    <td width="15%"><b>Nombre:</b></td>
                    <td width="35%">
                        <input type="text" wire:model="org_nombre" style="width: 90%;"> <span class="obligatorio">*</span>
                        @error('org_nombre')<br><span style="color:red;font-size:10px;">{{ $message }}</span>@enderror
                    </td>
                    <td width="15%"><b>RIF:</b></td>
                    <td width="35%">
                        <input type="text" wire:model="org_rif" style="width: 90%;">
                        @error('org_rif')<br><span style="color:red;font-size:10px;">{{ $message }}</span>@enderror
                    </td>
                </tr>
                <tr>
                    <td><b>Correo:</b></td>
                    <td colspan="3">
                        <input type="email" wire:model="org_correo" style="width: 95%;">
                        @error('org_correo')<br><span style="color:red;font-size:10px;">{{ $message }}</span>@enderror
                    </td>
                </tr>
                <tr>
                    <td><b>Dirección:</b></td>
                    <td colspan="3">
                        <textarea wire:model="org_direccion" rows="2" style="width: 95%; height: 50px;"></textarea>
                        @error('org_direccion')<br><span style="color:red;font-size:10px;">{{ $message }}</span>@enderror
                    </td>
                </tr>
            </table>

            {{-- Personas de Contacto --}}
            <div style="margin-top: 20px; border-top: 2px solid #8b0000; padding-top: 15px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <h4 style="margin: 0; font-size: 13px; font-weight: bold; color: #8b0000; font-style: italic;">Personas de Contacto</h4>
                    <button type="button" wire:click="agregarContacto" class="cm-btn cm-btn-primary cm-btn-sm">+ Agregar contacto</button>
                </div>
                @if (empty($contactos))
                    <div style="background: #fdfdfd; border: 1px dashed #bbb; border-radius: 6px; padding: 15px; text-align: center; color: #555; font-size: 11px; font-style: italic;">
                        No hay personas de contacto registradas para esta organizaci&oacute;n.
                    </div>
                @else
                    @foreach ($contactos as $i => $ct)
                        <div style="border: 1px solid #bbb; border-radius: 6px; margin-bottom: 12px; overflow: hidden;">
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 12px; background: #8bb2b7; font-size: 11px; font-weight: bold;">
                                <span>Contacto #{{ $loop->iteration }}</span>
                                <button type="button" wire:click="quitarContacto({{ $i }})" wire:confirm="¿Quitar este contacto?" class="cm-btn cm-btn-danger cm-btn-sm" style="padding: 2px 8px; font-size: 10px;">Quitar</button>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; padding: 12px; font-size: 11px;">
                                <div>
                                    <label style="display:block; font-weight:bold; margin-bottom:3px;">Nombre:</label>
                                    <input wire:model="contactos.{{ $i }}.nombre" type="text" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;">
                                    @error('contactos.' . $i . '.nombre')<span style="color:red;font-size:10px;display:block;margin-top:2px;">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label style="display:block; font-weight:bold; margin-bottom:3px;">Apellido:</label>
                                    <input wire:model="contactos.{{ $i }}.apellido" type="text" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;">
                                </div>
                                <div>
                                    <label style="display:block; font-weight:bold; margin-bottom:3px;">Cargo:</label>
                                    <input wire:model="contactos.{{ $i }}.cargo" type="text" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;" placeholder="Cargo...">
                                </div>
                                <div>
                                    <label style="display:block; font-weight:bold; margin-bottom:3px;">Correo:</label>
                                    <input wire:model="contactos.{{ $i }}.correo" type="email" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;" placeholder="correo@ejemplo.com">
                                </div>
                                <div>
                                    <label style="display:block; font-weight:bold; margin-bottom:3px;">Teléfono:</label>
                                    <input wire:model="contactos.{{ $i }}.telefono" type="text" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;" placeholder="0412-1234567">
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Acordeón de departamentos --}}
            @php
                $totalDeps = collect($departamentosForm)->filter(fn($d) => !($d['is_deleted'] ?? false))->count();
            @endphp
            <div style="margin-top: 12px; border: 1px solid #ccc; border-radius: 4px;">
                <button type="button" wire:click="abrirModalDeps"
                    style="width:100%; background:{{ $mostrarModalDeps ? '#e8e8e8' : '#f5f5f5' }}; border:none; padding:8px 12px; cursor:pointer; text-align:left; font-weight:bold; font-size:12px; display:flex; justify-content:space-between; align-items:center;">
                    <span>{{ $totalDeps > 0 ? 'Departamentos (' . $totalDeps . ')' : '+ Agregar Departamentos' }}</span>
                    <span style="font-size:14px;">{{ $mostrarModalDeps ? '▲' : '▼' }}</span>
                </button>
                @if($mostrarModalDeps)
                    <div style="padding:16px; border-top:1px solid #ccc;">
                        {{-- Formulario para agregar departamento --}}
                        <div style="background:#f5f7fa; border:1px solid #dde4eb; border-radius:6px; padding:18px; margin-bottom:14px;">
                            <div style="font-size:13px; font-weight:bold; color:#2c4a58; margin-bottom:14px; padding-bottom:8px; border-bottom:1px solid #dde4eb;">
                                Registrar nuevo departamento
                            </div>
                            <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
                                <div style="flex:1 1 200px; min-width:180px;">
                                    <label style="display:block; font-size:11px; font-weight:bold; color:#555; margin-bottom:4px;">Departamento <span style="color:red;">*</span></label>
                                    <input type="text" wire:model="nuevo_dep_nombre" placeholder="Nombre del departamento"
                                        style="width:100%; padding:8px 10px; border:1px solid #c0c8d0; border-radius:4px; font-size:12px; box-sizing:border-box;">
                                </div>
                                <div style="flex:1 1 180px; min-width:160px;">
                                    <label style="display:block; font-size:11px; font-weight:bold; color:#555; margin-bottom:4px;">Nombre contacto</label>
                                    <input type="text" wire:model="nuevo_dep_nombre_contacto" placeholder="Nombre"
                                        style="width:100%; padding:8px 10px; border:1px solid #c0c8d0; border-radius:4px; font-size:12px; box-sizing:border-box;">
                                </div>
                                <div style="flex:1 1 180px; min-width:160px;">
                                    <label style="display:block; font-size:11px; font-weight:bold; color:#555; margin-bottom:4px;">Apellido contacto</label>
                                    <input type="text" wire:model="nuevo_dep_apellido_contacto" placeholder="Apellido"
                                        style="width:100%; padding:8px 10px; border:1px solid #c0c8d0; border-radius:4px; font-size:12px; box-sizing:border-box;">
                                </div>
                                <div style="flex:1 1 160px; min-width:140px;">
                                    <label style="display:block; font-size:11px; font-weight:bold; color:#555; margin-bottom:4px;">Teléfono</label>
                                    <input type="text" wire:model="nuevo_dep_numero_contacto" placeholder="0412-1234567"
                                        style="width:100%; padding:8px 10px; border:1px solid #c0c8d0; border-radius:4px; font-size:12px; box-sizing:border-box;">
                                </div>
                                <div style="flex:0 0 auto;">
                                    <button type="button" wire:click="agregarDepartamentoFila"
                                        class="cm-btn cm-btn-primary"
                                        style="padding:8px 18px; font-size:12px; white-space:nowrap;">+ Añadir departamento</button>
                                </div>
                            </div>
                            @error('nuevo_dep_nombre')<div style="color:red;font-size:11px;margin-top:6px;">{{ $message }}</div>@enderror
                        </div>

                        {{-- Lista de departamentos agregados --}}
                        @php
                            $depsVisibles = collect($departamentosForm)->filter(fn($d) => !($d['is_deleted'] ?? false));
                        @endphp

                        <div style="font-size:12px; font-weight:bold; color:#2c4a58; margin-bottom:8px;">
                            Departamentos agregados ({{ $depsVisibles->count() }})
                        </div>

                        @if($depsVisibles->isEmpty())
                            <div style="background:#fafafa; border:1px dashed #bbb; border-radius:6px; padding:24px; text-align:center; color:#888; font-size:12px; font-style:italic;">
                                No se han agregado departamentos. Complete el formulario de arriba y presione <strong>+ Añadir departamento</strong>.
                            </div>
                        @else
                            <table width="100%" border="0" cellpadding="0" cellspacing="0"
                                style="font-size:12px; border-collapse:collapse;">
                                <thead>
                                    <tr style="background:#6d929b; color:#fff; font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">
                                        <th align="center" style="padding:10px 8px; width:40px;">N&deg;</th>
                                        <th align="left" style="padding:10px 8px;">Departamento</th>
                                        <th align="center" style="padding:10px 8px; width:90px;">Acci&oacute;n</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($departamentosForm as $index => $d)
                                        @if(!($d['is_deleted'] ?? false))
                                            <tr style="border-bottom:1px solid #e0e5ea; background-color: {{ $loop->iteration % 2 == 0 ? '#f8f9fa' : '#ffffff' }};">
                                                <td align="center" style="padding:10px 8px; color:#666;">{{ $loop->iteration }}</td>
                                                <td style="padding:10px 8px;">
                                                    <div style="font-weight:bold; color:#333; margin-bottom:2px;">{{ $d['nombre'] }}</div>
                                                    @if($d['nombre_contacto'] || $d['apellido_contacto'] || $d['numero_contacto'])
                                                        <div style="font-size:11px; color:#777;">
                                                            <span style="color:#555;">Contacto:</span>
                                                            {{ $d['nombre_contacto'] }} {{ $d['apellido_contacto'] }}
                                                            @if($d['numero_contacto']) &middot; {{ $d['numero_contacto'] }} @endif
                                                        </div>
                                                    @endif
                                                </td>
                                                <td align="center" style="padding:10px 8px;">
                                                    <button type="button" wire:click="removerDepartamentoFila({{ $index }})"
                                                        class="cm-btn cm-btn-danger cm-btn-sm"
                                                        style="font-size:11px; padding:5px 12px;">Remover</button>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endif
            </div>

            <div style="margin-top: 15px; text-align: center;">
                <button type="button" wire:click="cancelarFormOrg" class="cm-btn cm-btn-danger" style="margin-right: 10px;">Cancelar</button>
                <button type="button" wire:click="guardarOrg" class="cm-btn cm-btn-primary">
                    {{ $org_nombre_key ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </fieldset>
    @else
        {{-- ═══════════════════════════════════════════════════
             LISTADO DE ORGANIZACIONES
             ═══════════════════════════════════════════════════ --}}
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Organizaciones</legend>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div>
                    <b>Buscar:</b>
                    <input wire:model.live.debounce.300ms="busqueda" type="text"
                        style="width: 300px; padding: 3px 6px; border-radius: 4px; border: 1px solid #999;"
                        placeholder="Nombre de la organizaci&oacute;n...">
                </div>
                <button type="button" wire:click="abrirFormNuevaOrg" class="cm-btn cm-btn-success" style="font-size: 14px; padding: 8px 18px;">
                    + Nueva Organizaci&oacute;n
                </button>
            </div>

            <table width="100%" border="1" cellpadding="5" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="5%">N&deg;</th>
                        <th width="20%">Nombre</th>
                        <th width="10%">RIF</th>
                        <th width="10%">Cargo</th>
                        <th width="25%">Direcci&oacute;n</th>
                        <th width="20%">Contactos</th>
                        <th width="10%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @forelse($organizaciones as $org)
                        @php
                            $contactoList = $org->contactos->map(fn($c) => trim($c->oco_nombre . ' ' . ($c->oco_apellido ?? '')))->filter()->implode(', ');
                        @endphp
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                            <td align="center">{{ $loop->iteration }}</td>
                            <td>
                                <a href="#" wire:click.prevent="seleccionarOrg('{{ addslashes($org->nombre) }}')"
                                    style="color: #000; text-decoration: underline; font-weight: bold;">
                                    {{ $org->nombre }}
                                </a>
                            </td>
                            <td>{{ $org->rif ?? '-' }}</td>
                            <td>{{ $org->cargo ?? '-' }}</td>
                            <td>{{ $org->direccion ?? '-' }}</td>
                            <td style="font-size:10px;">{{ $contactoList ?: '-' }}</td>
                            <td align="center">
                                <div style="display: inline-flex; align-items: center; gap: 4px;">
                                    <button type="button" wire:click.prevent="editarOrg('{{ addslashes($org->nombre) }}')"
                                        class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                    <button type="button" wire:click.prevent="eliminarOrg('{{ addslashes($org->nombre) }}')"
                                        wire:confirm="¿Eliminar esta organización y todos sus departamentos?"
                                        class="cm-btn cm-btn-danger cm-btn-sm">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" align="center" style="padding: 20px;">No hay organizaciones registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </fieldset>
    @endif
</div>
