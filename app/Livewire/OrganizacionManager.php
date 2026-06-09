<?php

namespace App\Livewire;

use App\Models\Organizacion;
use App\Models\Departamento;
use App\Models\OrgContacto;
use App\Services\UserRoleService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrganizacionManager extends Component
{
    // ── Listado ──────────────────────────────────────────────
    public string $busqueda = '';

    // ── Formulario organización ───────────────────────────────
    public bool $mostrarFormOrg = false;
    public string $org_nombre_key = ''; // Para identificar cuál organización se edita

    public string $org_nombre    = '';
    public string $org_rif       = '';
    public string $org_correo    = '';
    public string $org_direccion = '';
    public string $org_nombre_contacto   = '';
    public string $org_apellido_contacto = '';
    public string $org_numero_contacto   = '';

    // ── Departamentos ─────────────────────────────────────────
    public ?string $orgSeleccionadaNombre = null;
    public bool $mostrarFormDep = false;
    public ?int $editandoDepId  = null;

    public string $dep_nombre    = '';
    public string $dep_cargo     = '';
    public string $dep_nombre_contacto   = '';
    public string $dep_apellido_contacto = '';
    public string $dep_numero_contacto   = '';

    // ── Departamentos en Formulario Organización ──────────────
    public array $departamentosForm = [];
    public array $contactos = [];
    public array $contactosDep = [];
    public bool $mostrarModalDeps = false;
    public string $nuevo_dep_nombre = '';
    public string $nuevo_dep_nombre_contacto   = '';
    public string $nuevo_dep_apellido_contacto = '';
    public string $nuevo_dep_numero_contacto   = '';

    // ── Estado ────────────────────────────────────────────────
    public string $mensaje     = '';
    public string $tipoMensaje = 'success'; // success | error

    // ── Acceso ────────────────────────────────────────────────
    protected function esGestionador(): bool
    {
        $user = Auth::user();

        return $user && app(UserRoleService::class)->esGestionador($user);
    }

    public function mount(): void
    {
        if (! $this->esGestionador()) {
            return;
        }
    }

    // ─────────────────────────────────────────────────────────
    // Organizaciones CRUD
    // ─────────────────────────────────────────────────────────

    public function abrirFormNuevaOrg(): void
    {
        if ($this->mostrarFormOrg) {
            $this->mostrarFormOrg = false;
            return;
        }
        $this->resetFormOrg();
        $this->org_nombre_key = '';
        $this->mostrarFormOrg = true;
        $this->departamentosForm = [];
    }

    public function editarOrg(string $nombre): void
    {
        $org = Organizacion::where('nombre', $nombre)->first();
        if (! $org) {
            return;
        }

        $this->org_nombre_key        = $org->nombre;
        $this->org_nombre            = $org->nombre;
        $this->org_rif               = $org->rif       ?? '';
        $this->org_correo            = $org->correo    ?? '';
        $this->org_direccion         = $org->direccion ?? '';
        $this->org_nombre_contacto   = $org->nombre_contacto   ?? '';
        $this->org_apellido_contacto = $org->apellido_contacto ?? '';
        $this->org_numero_contacto   = $org->numero_contacto   ?? '';

        $this->departamentosForm = [];
        $depCodigos = Organizacion::where('nombre', $nombre)
            ->whereNotNull('org_dep_codigo')
            ->pluck('org_dep_codigo');
        if ($depCodigos->isNotEmpty()) {
            $departamentos = Departamento::whereIn('dep_codigo', $depCodigos)->get()->keyBy('dep_codigo');
            foreach ($depCodigos as $dc) {
                $dep = $departamentos->get($dc);
                if ($dep) {
                    $depContact = OrgContacto::where('dep_codigo', $dep->id)->first();
                    $this->departamentosForm[] = [
                        'id'               => $dep->id,
                        'nombre'           => $dep->nombre,
                        'cargo'            => $dep->cargo ?? '',
                        'nombre_contacto'  => $depContact->oco_nombre ?? '',
                        'apellido_contacto'=> $depContact->oco_apellido ?? '',
                        'numero_contacto'  => $depContact->oco_telefono ?? '',
                        'is_new'           => false,
                        'is_deleted'       => false,
                    ];
                }
            }
        }

        $this->contactos = OrgContacto::where('org_codigo', $org->id)->whereNull('dep_codigo')->get()->map(fn($c) => [
            'nombre' => $c->oco_nombre ?? '',
            'apellido' => $c->oco_apellido ?? '',
            'cargo' => $c->oco_cargo ?? '',
            'correo' => $c->oco_correo ?? '',
            'telefono' => $c->oco_telefono ?? '',
        ])->toArray();

        $this->mostrarFormOrg = true;
    }

    public function abrirModalDeps(): void
    {
        $this->mostrarModalDeps = true;
    }

    public function cerrarModalDeps(): void
    {
        $this->mostrarModalDeps = false;
        $this->nuevo_dep_nombre = '';
        $this->nuevo_dep_nombre_contacto   = '';
        $this->nuevo_dep_apellido_contacto = '';
        $this->nuevo_dep_numero_contacto   = '';
    }

    public function agregarDepartamentoFila(): void
    {
        $this->validate([
            'nuevo_dep_nombre' => 'required|min:2|max:255',
            'nuevo_dep_nombre_contacto'   => 'nullable|max:255',
            'nuevo_dep_apellido_contacto' => 'nullable|max:255',
            'nuevo_dep_numero_contacto'   => 'nullable|max:20',
        ], [
            'nuevo_dep_nombre.required' => 'El nombre del departamento es obligatorio.',
            'nuevo_dep_nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
        ]);

        $nombreNuevo = trim($this->nuevo_dep_nombre);
        foreach ($this->departamentosForm as $d) {
            if (!($d['is_deleted'] ?? false) && mb_strtolower($d['nombre']) === mb_strtolower($nombreNuevo)) {
                $this->addError('nuevo_dep_nombre', 'Este departamento ya está en la lista.');
                return;
            }
        }

        $this->departamentosForm[] = [
            'id'               => null,
            'nombre'           => $nombreNuevo,
            'cargo'            => '',
            'nombre_contacto'  => $this->nuevo_dep_nombre_contacto ? trim($this->nuevo_dep_nombre_contacto) : '',
            'apellido_contacto'=> $this->nuevo_dep_apellido_contacto ? trim($this->nuevo_dep_apellido_contacto) : '',
            'numero_contacto'  => $this->nuevo_dep_numero_contacto ? trim($this->nuevo_dep_numero_contacto) : '',
            'is_new'           => true,
            'is_deleted'       => false,
        ];

        $this->nuevo_dep_nombre = '';
        $this->nuevo_dep_nombre_contacto = '';
        $this->nuevo_dep_apellido_contacto = '';
        $this->nuevo_dep_numero_contacto = '';
    }

    public function agregarContacto(): void
    {
        $this->contactos[] = ['nombre' => '', 'apellido' => '', 'cargo' => '', 'correo' => '', 'telefono' => ''];
    }

    public function quitarContacto(int $index): void
    {
        if (isset($this->contactos[$index])) {
            unset($this->contactos[$index]);
            $this->contactos = array_values($this->contactos);
        }
    }

    public function agregarContactoDep(): void
    {
        $this->contactosDep[] = ['nombre' => '', 'apellido' => '', 'correo' => '', 'telefono' => ''];
    }

    public function quitarContactoDep(int $index): void
    {
        if (isset($this->contactosDep[$index])) {
            unset($this->contactosDep[$index]);
            $this->contactosDep = array_values($this->contactosDep);
        }
    }

    public function removerDepartamentoFila(int $index): void
    {
        if (isset($this->departamentosForm[$index])) {
            if ($this->departamentosForm[$index]['is_new']) {
                unset($this->departamentosForm[$index]);
                $this->departamentosForm = array_values($this->departamentosForm);
            } else {
                $this->departamentosForm[$index]['is_deleted'] = true;
            }
        }
    }

    public function guardarOrg(): void
    {
        if (! $this->esGestionador()) {
            return;
        }

        $this->validate([
            'org_nombre'           => 'required|min:3|max:255',
            'org_rif'              => 'nullable|max:20',
            'org_correo'           => 'nullable|email|max:255',
            'org_direccion'        => 'nullable|max:1000',
            'org_nombre_contacto'  => 'nullable|max:255',
            'org_apellido_contacto'=> 'nullable|max:255',
            'org_numero_contacto'  => 'nullable|max:20',
        ]);

        $nombreNuevo = trim($this->org_nombre);
        $rifNuevo    = $this->org_rif ? trim($this->org_rif) : null;
        $dirNueva    = $this->org_direccion ? trim($this->org_direccion) : null;
        $orgPayload = [
            'nombre'            => $nombreNuevo,
            'rif'               => $this->org_rif ?: '-',
            'correo'            => $this->org_correo ?: '-',
            'direccion'         => $dirNueva,
        ];

        if ($this->org_nombre_key) {
            $orgs = Organizacion::where('nombre', $this->org_nombre_key)->get();
            foreach ($orgs as $orgRow) {
                $orgRow->fill($orgPayload);
                $orgRow->save();
            }
            $org = $orgs->first();

            foreach ($this->departamentosForm as $d) {
                if ($d['is_deleted'] ?? false) {
                    if ($d['id']) {
                        Organizacion::where('dep_codigo', $d['id'])->delete();
                        $dep = Departamento::find($d['id']);
                        if ($dep) $dep->delete();
                    }
                } elseif ($d['is_new'] ?? false) {
                    $dep = Departamento::create([
                        'nombre'            => $d['nombre'],
                        'cargo'             => $d['cargo'] ?? '',
                    ]);
                    Organizacion::create($orgPayload + ['dep_codigo' => $dep->id]);
                    if ($d['nombre_contacto'] || $d['apellido_contacto'] || $d['numero_contacto']) {
                        OrgContacto::create([
                            'dep_codigo' => $dep->id,
                            'oco_nombre' => $d['nombre_contacto'] ?? '',
                            'oco_apellido' => $d['apellido_contacto'] ?? '',
                            'oco_telefono' => $d['numero_contacto'] ?? '',
                        ]);
                    }
                } else {
                    $dep = Departamento::where('id', $d['id'])->first();
                    if ($dep) {
                        $dep->nombre            = $d['nombre'];
                        $dep->cargo             = $d['cargo'] ?? '';
                        $dep->save();
                    }
                }
            }

            $activeRowsCount = Organizacion::where('nombre', $nombreNuevo)->whereNotNull('org_dep_codigo')->count();
            if ($activeRowsCount === 0) {
                $hasNullRow = Organizacion::where('nombre', $nombreNuevo)->whereNull('org_dep_codigo')->exists();
                if (!$hasNullRow) {
                    Organizacion::create($orgPayload + ['dep_codigo' => null]);
                }
            } else {
                Organizacion::where('nombre', $nombreNuevo)->whereNull('org_dep_codigo')->delete();
            }

            if ($org) {
                OrgContacto::where('org_codigo', $org->id)->whereNull('dep_codigo')->delete();
                foreach ($this->contactos as $ct) {
                    OrgContacto::create([
                        'org_codigo' => $org->id,
                        'oco_nombre' => $ct['nombre'] ?? '',
                        'oco_apellido' => $ct['apellido'] ?? '',
                        'oco_cargo' => $ct['cargo'] ?? '',
                        'oco_correo' => $ct['correo'] ?? '',
                        'oco_telefono' => $ct['telefono'] ?? '',
                    ]);
                }
            }

            $this->notificar('Organización actualizada correctamente.');
        } else {
            $existeNombre = Organizacion::where('nombre', $nombreNuevo)->exists();
            if ($existeNombre) {
                $this->addError('org_nombre', 'Ya existe una organización registrada con este nombre.');
                return;
            }

            $hasActiveDeps = false;
            foreach ($this->departamentosForm as $d) {
                if (!($d['is_deleted'] ?? false)) {
                    $hasActiveDeps = true;
                    $dep = Departamento::create([
                        'nombre'            => $d['nombre'],
                        'cargo'             => $d['cargo'] ?? '',
                    ]);
                    Organizacion::create($orgPayload + ['dep_codigo' => $dep->id]);
                    if ($d['nombre_contacto'] || $d['apellido_contacto'] || $d['numero_contacto']) {
                        OrgContacto::create([
                            'dep_codigo' => $dep->id,
                            'oco_nombre' => $d['nombre_contacto'] ?? '',
                            'oco_apellido' => $d['apellido_contacto'] ?? '',
                            'oco_telefono' => $d['numero_contacto'] ?? '',
                        ]);
                    }
                }
            }

            if (!$hasActiveDeps) {
                $orgRow = Organizacion::create($orgPayload + ['dep_codigo' => null]);
            } else {
                $orgRow = Organizacion::where('nombre', $nombreNuevo)->first();
            }

            if ($orgRow) {
                foreach ($this->contactos as $ct) {
                    OrgContacto::create([
                        'org_codigo' => $orgRow->id,
                        'oco_nombre' => $ct['nombre'] ?? '',
                        'oco_apellido' => $ct['apellido'] ?? '',
                        'oco_cargo' => $ct['cargo'] ?? '',
                        'oco_correo' => $ct['correo'] ?? '',
                        'oco_telefono' => $ct['telefono'] ?? '',
                    ]);
                }
            }

            $this->notificar('Organización registrada correctamente.');
        }

        $this->resetFormOrg();
        $this->mostrarFormOrg = false;
    }

    public function eliminarOrg(string $nombre): void
    {
        if (! $this->esGestionador()) {
            return;
        }

        $orgRows = Organizacion::where('nombre', $nombre)->get();
        foreach ($orgRows as $row) {
            if ($row->dep_codigo) {
                Organizacion::where('dep_codigo', $row->dep_codigo)->delete();
                $dep = Departamento::find($row->dep_codigo);
                if ($dep) $dep->delete();
            }
        }

        Organizacion::where('nombre', $nombre)->delete();

        $this->notificar('Organización eliminada correctamente.');

        if ($this->orgSeleccionadaNombre === $nombre) {
            $this->orgSeleccionadaNombre = null;
        }
    }

    public function cancelarFormOrg(): void
    {
        $this->resetFormOrg();
        $this->mostrarFormOrg = false;
    }

    protected function resetFormOrg(): void
    {
        $this->org_nombre_key = '';
        $this->org_nombre     = '';
        $this->org_rif        = '';
        $this->org_correo     = '';
        $this->org_direccion  = '';
        $this->org_nombre_contacto   = '';
        $this->org_apellido_contacto = '';
        $this->org_numero_contacto   = '';
        $this->departamentosForm = [];
        $this->contactos = [];
        $this->mostrarModalDeps = false;
        $this->nuevo_dep_nombre = '';
        $this->nuevo_dep_nombre_contacto   = '';
        $this->nuevo_dep_apellido_contacto = '';
        $this->nuevo_dep_numero_contacto   = '';
        $this->resetValidation();
    }

    // ─────────────────────────────────────────────────────────
    // Seleccionar organización → ver departamentos
    // ─────────────────────────────────────────────────────────

    public function seleccionarOrg(string $nombre): void
    {
        $this->orgSeleccionadaNombre = $nombre;
        $this->resetFormDep();
        $this->mostrarFormDep = false;
    }

    public function cerrarDepartamentos(): void
    {
        $this->orgSeleccionadaNombre = null;
        $this->resetFormDep();
    }

    // ─────────────────────────────────────────────────────────
    // Departamentos CRUD (Panel separado)
    // ─────────────────────────────────────────────────────────

    public function abrirFormNuevoDep(): void
    {
        $this->resetFormDep();
        $this->editandoDepId  = null;
        $this->mostrarFormDep = true;
    }

    public function editarDep(int $id): void
    {
        $dep = Departamento::find($id);
        if (! $dep) {
            return;
        }

        $this->editandoDepId = $id;
        $this->dep_nombre    = $dep->nombre;
        $this->dep_cargo     = $dep->cargo     ?? '';
        $this->dep_nombre_contacto   = $dep->nombre_contacto   ?? '';
        $this->dep_apellido_contacto = $dep->apellido_contacto ?? '';
        $this->dep_numero_contacto   = $dep->numero_contacto   ?? '';
        $this->contactosDep = OrgContacto::where('dep_codigo', $id)->get()->map(fn($c) => [
            'nombre' => $c->oco_nombre ?? '',
            'apellido' => $c->oco_apellido ?? '',
            'correo' => $c->oco_correo ?? '',
            'telefono' => $c->oco_telefono ?? '',
        ])->toArray();
        $this->mostrarFormDep = true;
    }

    public function guardarDep(): void
    {
        if (! $this->esGestionador() || ! $this->orgSeleccionadaNombre) {
            return;
        }

        $this->validate([
            'dep_nombre'           => 'required|min:2|max:255',
            'dep_cargo'            => 'nullable|max:2000',
            'dep_nombre_contacto'  => 'nullable|max:255',
            'dep_apellido_contacto'=> 'nullable|max:255',
            'dep_numero_contacto'  => 'nullable|max:20',
        ]);

        $nombreOrg = $this->orgSeleccionadaNombre;
        $orgRow = Organizacion::where('nombre', $nombreOrg)->first();
        if (!$orgRow) {
            return;
        }

        $depData = [
            'nombre'            => trim($this->dep_nombre),
            'cargo'             => $this->dep_cargo ? trim($this->dep_cargo) : '',
        ];

        if ($this->editandoDepId) {
            $dep = Departamento::where('id', $this->editandoDepId)->first();
            if ($dep) {
                $dep->fill($depData);
                $dep->save();
            }
            if ($dep) {
                OrgContacto::where('dep_codigo', $dep->id)->delete();
                foreach ($this->contactosDep as $ct) {
                    OrgContacto::create([
                        'dep_codigo' => $dep->id,
                        'oco_nombre' => $ct['nombre'] ?? '',
                        'oco_apellido' => $ct['apellido'] ?? '',
                        'oco_correo' => $ct['correo'] ?? '',
                        'oco_telefono' => $ct['telefono'] ?? '',
                    ]);
                }
            }
            $this->notificar('Departamento actualizado.');
        } else {
            $dep = Departamento::create($depData);

            Organizacion::create([
                'nombre'            => $orgRow->nombre,
                'rif'               => $orgRow->rif,
                'correo'            => $orgRow->correo,
                'direccion'         => $orgRow->direccion,
                'dep_codigo'        => $dep->id,
            ]);

            Organizacion::where('nombre', $orgRow->nombre)->whereNull('org_dep_codigo')->delete();

            foreach ($this->contactosDep as $ct) {
                OrgContacto::create([
                    'dep_codigo' => $dep->id,
                    'oco_nombre' => $ct['nombre'] ?? '',
                    'oco_apellido' => $ct['apellido'] ?? '',
                    'oco_correo' => $ct['correo'] ?? '',
                    'oco_telefono' => $ct['telefono'] ?? '',
                ]);
            }

            $this->notificar('Departamento registrado.');
        }

        $this->resetFormDep();
        $this->mostrarFormDep = false;
    }

    public function eliminarDep(int $id): void
    {
        if (! $this->esGestionador()) {
            return;
        }

        Organizacion::where('dep_codigo', $id)->delete();
        $dep = Departamento::find($id);
        if ($dep) $dep->delete();

        if ($this->orgSeleccionadaNombre) {
            $count = Organizacion::where('nombre', $this->orgSeleccionadaNombre)->whereNotNull('org_dep_codigo')->count();
            if ($count === 0) {
                $orgRow = Organizacion::where('nombre', $this->orgSeleccionadaNombre)->first();
                if ($orgRow) {
                    Organizacion::create([
                        'nombre'            => $orgRow->nombre,
                        'rif'               => $orgRow->rif,
                        'correo'            => $orgRow->correo,
                        'direccion'         => $orgRow->direccion,
                        'dep_codigo'        => null,
                    ]);
                }
            }
        }

        $this->notificar('Departamento eliminado.');
    }

    public function cancelarFormDep(): void
    {
        $this->resetFormDep();
        $this->mostrarFormDep = false;
    }

    protected function resetFormDep(): void
    {
        $this->editandoDepId = null;
        $this->dep_nombre    = '';
        $this->dep_cargo     = '';
        $this->dep_nombre_contacto   = '';
        $this->dep_apellido_contacto = '';
        $this->dep_numero_contacto   = '';
        $this->contactosDep = [];
        $this->resetValidation();
    }

    // ─────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────

    protected function notificar(string $msg, string $tipo = 'success'): void
    {
        $this->mensaje     = $msg;
        $this->tipoMensaje = $tipo;
    }

    public function limpiarMensaje(): void
    {
        $this->mensaje = '';
    }

    // ─────────────────────────────────────────────────────────
    // Render
    // ─────────────────────────────────────────────────────────

    public function render()
    {
        if (! $this->esGestionador()) {
            return view('livewire.organizacion-manager', [
                'accesoDenegado' => true,
                'organizaciones' => collect(),
                'deps'           => collect(),
            ]);
        }

        // Listado de organizaciones únicas agrupadas por sus datos comunes
        $organizaciones = Organizacion::when($this->busqueda, fn ($q) => $q->where('nombre', 'like', $this->busqueda . '%'))
            ->orderBy('nombre')
            ->get()
            ->groupBy(fn ($org) => $org->nombre)
            ->map(fn ($rows) => $rows->first());

        $deps = collect();
        if ($this->orgSeleccionadaNombre) {
            $depCodigos = Organizacion::where('nombre', $this->orgSeleccionadaNombre)
                ->whereNotNull('org_dep_codigo')
                ->get()
                ->pluck('dep_codigo');

            $deps = Departamento::whereIn('dep_codigo', $depCodigos)
                ->orderBy('nombre')
                ->get();
        }

        return view('livewire.organizacion-manager', [
            'accesoDenegado' => false,
            'organizaciones' => $organizaciones,
            'deps'           => $deps,
        ]);
    }
}
