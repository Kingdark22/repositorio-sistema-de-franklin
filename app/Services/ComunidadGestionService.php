<?php

namespace App\Services;

use App\Models\Comunidad;
use App\Models\Direccion;
use App\Models\Estado;
use App\Models\Municipio;
use App\Models\Programa;
use App\Models\Seccion;
use App\Helpers\DbHelper;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ComunidadGestionService
{
    /**
     * Reglas de validación para el formulario de comunidades.
     */
    public function reglasValidacion(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'rif' => 'nullable|string|max:50',
            'estado_id' => 'required|integer|exists:estados,est_codigo',
            'municipio_id' => 'required|integer|exists:municipios,mun_codigo',
            'dir_nombre' => 'required|string|max:500',
            'correo' => 'nullable|email|max:150',
            'prefijo_telefono' => 'nullable|in:0424,0414,0412,0422,0416,0426',
            'numero_telefono' => 'nullable|digits:7',
            'trayecto' => 'nullable|string|max:32',
            'programa' => 'nullable|string|max:50',
            'seccion' => 'nullable|string|max:50',
            'nombre_encargado' => 'nullable|string|max:255',
            'apellido_encargado' => 'nullable|string|max:255',
            'telefono_encargado' => 'nullable|string|max:50',
            'contactos' => 'nullable|array',
            'contactos.*.nombre' => 'required|string|max:255',
            'contactos.*.apellido' => 'nullable|string|max:255',
            'contactos.*.correo' => 'nullable|email|max:150',
            'contactos.*.correo_confirmacion' => 'nullable|email|max:150',
            'contactos.*.prefijo' => 'nullable|in:0424,0414,0412,0422,0416,0426',
            'contactos.*.telefono' => 'nullable|string|max:50',
            'contactos.*.cargo' => 'nullable|string|max:100',
        ];
    }

    /**
     * Carga los datos de una comunidad para su edición.
     */
    public function cargarParaEdicion(int $id): array
    {
        $comunidad = Comunidad::with('contactos', 'direccion.municipio.estado')->whereKey($id)->firstOrFail();

        $direccion = $comunidad->direccion;

        return [
            'nombre' => $comunidad->nombre,
            'rif' => $comunidad->rif,
            'correo' => $comunidad->correo,
            'numero_telefono' => $comunidad->numero_telefono,
            'estado_id' => $direccion?->municipio?->est_codigo ? (string) $direccion->municipio->est_codigo : '',
            'municipio_id' => $direccion?->mun_codigo ? (string) $direccion->mun_codigo : '',
            'dir_nombre' => $direccion?->dir_nombre ?? '',
            'trayecto' => $comunidad->trayecto ?? '',
            'programa' => $comunidad->programa ?? '',
            'seccion' => $comunidad->seccion ?? '',
            'nombre_encargado' => $comunidad->nombre_encargado ?? '',
            'apellido_encargado' => $comunidad->apellido_encargado ?? '',
            'telefono_encargado' => $comunidad->telefono_encargado ?? '',
            'contactos' => $comunidad->contactos->map(fn ($c) => [
                'nombre' => $c->ccon_nombre,
                'apellido' => $c->ccon_apellido ?? '',
                'correo' => $c->ccon_correo ?? '',
                'correo_confirmacion' => $c->ccon_correo ?? '',
                'prefijo' => $c->ccon_telefono ? (strlen(trim($c->ccon_telefono)) >= 10 ? substr(trim($c->ccon_telefono), 0, 4) : '0424') : '0424',
                'telefono' => $c->ccon_telefono ? (strlen(trim($c->ccon_telefono)) >= 7 ? substr(trim($c->ccon_telefono), -7) : trim($c->ccon_telefono)) : '',
                'cargo' => $c->ccon_cargo ?? '',
            ])->toArray(),
        ];
    }

    /**
     * Guarda o actualiza una comunidad.
     */
    public function guardar(?int $id, array $datos): void
    {
        $dirNombre = trim($datos['dir_nombre'] ?? '');

        if ($dirNombre !== '' && !empty($datos['municipio_id'])) {
            $direccion = Direccion::updateOrCreate(
                ['dir_nombre' => $dirNombre, 'mun_codigo' => $datos['municipio_id']],
                ['dir_nombre' => $dirNombre, 'mun_codigo' => $datos['municipio_id']]
            );
            $direccionId = $direccion->dir_codigo;
        } else {
            $direccionId = null;
        }

        $payload = [
            'nombre' => $datos['nombre'],
            'rif' => $datos['rif'],
            'correo' => $datos['correo'],
            'numero_telefono' => !empty($datos['prefijo_telefono']) && !empty($datos['numero_telefono'])
                ? $datos['prefijo_telefono'] . $datos['numero_telefono']
                : ($datos['numero_telefono'] ?? null),
            'dir_codigo' => $direccionId,
            'trayecto' => $datos['trayecto'] !== '' ? $datos['trayecto'] : null,
            'programa' => $datos['programa'] !== '' ? $datos['programa'] : null,
            'seccion' => $datos['seccion'] !== '' ? $datos['seccion'] : null,
            'nombre_encargado' => $datos['nombre_encargado'] ?? '-',
            'apellido_encargado' => $datos['apellido_encargado'] ?? '-',
            'telefono_encargado' => $datos['telefono_encargado'] ?? '-',
        ];

        $comunidad = Comunidad::guardar($payload, $id);

        if (isset($datos['contactos'])) {
            $comunidad->contactos()->delete();
            foreach ($datos['contactos'] as $contacto) {
                $telefono = '';
                $prefijo = $contacto['prefijo'] ?? '';
                $numero = $contacto['telefono'] ?? '';
                if ($numero !== '') {
                    $telefono = $prefijo !== '' ? $prefijo . $numero : $numero;
                }
                $comunidad->contactos()->create([
                    'ccon_nombre' => $contacto['nombre'],
                    'ccon_apellido' => $contacto['apellido'] ?? null,
                    'ccon_correo' => $contacto['correo'] ?? null,
                    'ccon_telefono' => $telefono,
                    'ccon_cargo' => $contacto['cargo'] ?? null,
                ]);
            }
        }
    }

    /**
     * Elimina una comunidad.
     */
    public function eliminar(int $id): void
    {
        $comunidad = Comunidad::findOrFail($id);
        $direccionId = $comunidad->dir_codigo;
        $comunidad->delete();
        if ($direccionId) {
            Direccion::where('dir_codigo', $direccionId)->whereDoesntHave('comunidad')->delete();
        }
    }

    /**
     * Obtiene los datos para la vista de listado.
     */
    public function datosVistaListado(array $filtros, int $page): array
    {
        $termino = trim($filtros['search'] ?? '');

        $comunidades = Comunidad::with('contactos', 'direccion.municipio.estado')
            ->when($termino !== '', function ($q) use ($termino) {
                $q->where('nombre', 'like', '%' . $termino . '%')
                    ->orWhere('rif', 'like', '%' . $termino . '%');
            })
            ->orderByDesc((new Comunidad())->getKeyName())
            ->paginate(10, ['*'], 'page', $page);

        $programaIds = $comunidades->pluck('programa')->filter()->unique()->values();
        $seccionIds = $comunidades->pluck('seccion')->filter()->unique()->values();

        $conn = DbHelper::connection();
        $programasMap = collect();
        $seccionesMap = collect();

        if ($programaIds->isNotEmpty()) {
            try {
                $programasMap = Programa::on($conn)->whereIn('pro_codigo', $programaIds)
                    ->pluck('pro_siglas', 'pro_codigo');
            } catch (\Throwable $e) {
                Log::error('ComunidadGestionService: Error cargando siglas de programas: ' . $e->getMessage());
            }
        }

        if ($seccionIds->isNotEmpty()) {
            try {
                $seccionesMap = Seccion::on($conn)->whereIn('sec_codigo', $seccionIds)
                    ->pluck('sec_nombre', 'sec_codigo');
            } catch (\Throwable $e) {
                Log::error('ComunidadGestionService: Error cargando nombres de secciones: ' . $e->getMessage());
            }
        }

        return [
            'comunidades' => $comunidades,
            'programasMap' => $programasMap,
            'seccionesMap' => $seccionesMap,
        ];
    }

    /**
     * Obtiene los datos necesarios para el formulario (catálogos externos).
     */
    public function datosVistaFormulario(?string $programaId = null, ?string $estadoId = null): array
    {
        $conn = DbHelper::connection();

        try {
            $programas = Programa::on($conn)
                ->orderBy('pro_nombre')
                ->get();
        } catch (\Throwable $e) {
            Log::error('ComunidadGestionService: Error cargando programas: ' . $e->getMessage());
            $programas = collect();
        }

        $secciones = collect();
        $trayectos = collect();

        if ($programaId) {
            try {
                $secciones = Seccion::on($conn)
                    ->join('malla', 'seccion.sec_cod_malla', '=', 'malla.mal_codigo')
                    ->where('malla.mal_cod_programa', $programaId)
                    ->select('seccion.sec_codigo', 'seccion.sec_nombre')
                    ->distinct()
                    ->orderBy('seccion.sec_nombre')
                    ->get();

                $trayectos = \App\Models\Trayecto::on($conn)
                    ->select('tra_codigo', 'tra_nombre')
                    ->orderBy('tra_nombre')
                    ->get();
            } catch (\Throwable $e) {
                Log::error('ComunidadGestionService: Error cargando secciones/trayectos para programa ' . $programaId . ': ' . $e->getMessage());
            }
        }

        $estados = Estado::orderBy('est_nombre')->get();
        $municipios = collect();

        if ($estadoId) {
            $municipios = Municipio::where('est_codigo', $estadoId)->orderBy('mun_nombre')->get();
        }

        return [
            'programas' => $programas,
            'secciones' => $secciones,
            'trayectos' => $trayectos,
            'estados' => $estados,
            'municipios' => $municipios,
        ];
    }
}
