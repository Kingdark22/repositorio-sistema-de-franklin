<div>
    <style>
        .cm-btn {
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 6px; padding: 0.55rem 0.95rem;
            font-size: 0.92rem; font-weight: 600;
            border: 1px solid transparent; cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
            text-decoration: none;
        }
        .cm-btn:hover { transform: translateY(-1px); }
        .cm-btn-primary { background: #19692e; border-color: #154f26; color: #fff; }
        .cm-btn-danger { background: #c82333; border-color: #a71d2a; color: #fff; }
        .cm-btn-secondary { background: #f4f4f4; border: 1px solid #c2c2c2; color: #222; }
        .cm-btn-success { background: #198754; border-color: #166f43; color: #fff; }
        .cm-btn-sm { padding: 0.35rem 0.75rem; font-size: 0.85rem; }
    </style>

    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Publicar Proyectos</h2>

    @if($mensaje)
        <div style="background-color: {{ $tipoMensaje === 'success' ? '#d4edda' : '#f8d7da' }}; color: {{ $tipoMensaje === 'success' ? '#155724' : '#721c24' }}; border: 1px solid {{ $tipoMensaje === 'success' ? '#c3e6cb' : '#f5c6cb' }}; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:12px; display: flex; justify-content: space-between; align-items: center;">
            <span>{{ $mensaje }}</span>
            <a href="#" wire:click.prevent="limpiarMensaje" style="font-size:16px; font-weight:bold; text-decoration:none; color:inherit;">&times;</a>
        </div>
    @endif

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Seleccionar proyectos</legend>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <div>
                <b>Buscar:</b>
                <input wire:model.live.debounce.300ms="busqueda" type="text"
                    style="width: 300px; padding: 3px 6px; border-radius: 4px; border: 1px solid #999;"
                    placeholder="T&iacute;tulo del proyecto...">
            </div>
            <button type="button" wire:click="publicar" class="cm-btn cm-btn-success" style="font-size:14px; padding:8px 18px;">
                Publicar seleccionados
            </button>
        </div>

        <table width="100%" border="1" cellpadding="5" cellspacing="0"
            style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px;">
            <thead>
                <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                    <th width="5%" align="center">Sel.</th>
                    <th width="5%">N&deg;</th>
                    <th width="30%">T&iacute;tulo</th>
                    <th width="30%">Resumen</th>
                    <th width="10%">F. Subida</th>
                    <th width="20%">Estado</th>
                </tr>
            </thead>
            <tbody class="Texto">
                @forelse($proyectos as $p)
                    @php
                        $estaPublicado = in_array($p->id, $publicados);
                    @endphp
                    <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                        <td align="center">
                            @if($estaPublicado)
                                <span style="color:#19692e; font-weight:bold;">&check;</span>
                            @else
                                <input type="checkbox" wire:model="seleccionados.{{ $p->id }}" value="1">
                            @endif
                        </td>
                        <td align="center">{{ $loop->iteration }}</td>
                        <td>{{ $p->titulo }}</td>
                        <td style="font-size:10px;">{{ \Illuminate\Support\Str::limit($p->resumen, 80) }}</td>
                        <td align="center">{{ $p->fecha_subida ? $p->fecha_subida->format('d/m/Y') : '-' }}</td>
                        <td align="center">
                            @if($estaPublicado)
                                <span style="color:#19692e; font-weight:bold;">Publicado</span>
                            @else
                                <span style="color:#888;">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" align="center" style="padding:20px;">No hay proyectos disponibles.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </fieldset>
</div>
