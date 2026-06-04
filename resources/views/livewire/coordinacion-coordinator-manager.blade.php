<div>
    <h2 class="titulo" style="margin-bottom: 16px; font-weight: bolder;">Coordinadores de coordinaci&oacute;n</h2>

    <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin-bottom: 14px; font-size: 11px;">
        Los coordinadores se definen en <strong>intranet</strong> (<code>usuario.usu_cod_rol</code>).
        Este m&oacute;dulo <strong>no crea tablas</strong> ni duplica datos acad&eacute;micos en MySQL repositorio.
        La asignaci&oacute;n a un PNF/coordinaci&oacute;n se gestiona en intranet.
    </div>

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 18px;">
        <legend style="font-weight: bold; font-style: italic;">Coordinadores (intranet)</legend>
        <table width="100%" border="1" cellpadding="5" style="border-collapse: collapse; font-size: 11px;">
            <thead>
                <tr style="background: #8bb2b7; font-weight: bold; text-align: center;">
                    <th>C&eacute;dula</th>
                    <th>Nombre</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coordinadoresIntranet as $c)
                    <tr style="background: {{ $loop->even ? '#eee' : '#fff' }};">
                        <td align="center">{{ $c->cedula }}</td>
                        <td>{{ mb_strtoupper($c->apellido) }} {{ mb_strtoupper($c->nombre) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" align="center" style="padding: 12px;">No hay usuarios con rol coordinador en intranet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </fieldset>

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
        <legend style="font-weight: bold; font-style: italic;">Docentes activos (referencia, lapso {{ $lapsoVigente ?? '—' }})</legend>
        <p style="font-size: 10px; color: #555; margin-bottom: 8px;">
            Cat&aacute;logo local de coordinaciones (solo lectura): 
            @foreach($coordinacionesCatalogo as $coord)
                {{ $coord->nombre }}@if(!$loop->last), @endif
            @endforeach
        </p>
        <input wire:model.debounce.500ms="search" type="text" placeholder="Buscar docente…" style="width: 240px; margin-bottom: 8px; font-size: 11px;">
        <table width="100%" border="1" cellpadding="4" style="font-size: 10px; border-collapse: collapse;">
            <thead>
                <tr style="background: #ddd; font-weight: bold;">
                    <th>Docente</th>
                    <th>Secci&oacute;n / lapso</th>
                </tr>
            </thead>
            <tbody>
                @foreach($docentes as $d)
                    <tr>
                        <td>{{ $d->apellido }}, {{ $d->nombre }} ({{ $d->cedula }})</td>
                        <td>{{ $d->lapso_nombre }} — {{ $d->trayecto_nombre ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $docentes->links() }}
    </fieldset>
</div>
