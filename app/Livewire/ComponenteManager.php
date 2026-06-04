<?php

namespace App\Livewire;

use App\Models\Componente;
use App\Models\Trayecto;
use App\Helpers\DbHelper;
use App\Services\AcademicCatalog;
use App\Services\IntranetEquipoSeccionService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ComponenteManager extends Component
{
    use WithPagination;

    public $search = '';
    public $filterPrograma = '';
    public $filterTrayecto = '';
    public $viewMode = 'list';
    public $editingId = null;

    public $coordinacion_id = '';
    public $anio = '';

    public Collection $trayectosPrograma;

    public function mount()
    {
        $this->trayectosPrograma = collect();
    }

    public $nombre = '';
    public $es_obligatorio = true;

    public $rows = [];

    protected function rules()
    {
        return [
            'coordinacion_id' => 'required',
            'anio' => 'required|string',
            'rows.*.nombre' => 'required|min:3',
            'rows.*.es_obligatorio' => 'boolean',
        ];
    }

    protected $messages = [
        'rows.*.nombre.required' => 'Debe nombrar el documento en esta fila.',
        'coordinacion_id.required' => 'Debe asignar una PNF / Coordinación rectora.',
        'anio.required' => 'Debe asignarle el trayecto (I, II, III, IV).',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterPrograma()
    {
        $this->resetPage();
    }

    public function updatingFilterTrayecto()
    {
        $this->resetPage();
    }

    public function updatedCoordinacionId($value)
    {
        $this->anio = '';
        $this->loadTrayectosPrograma();
    }

    protected function loadTrayectosPrograma()
    {
        if ($this->coordinacion_id === '') {
            $this->trayectosPrograma = collect();
            return;
        }

        $this->trayectosPrograma = app(IntranetEquipoSeccionService::class)
            ->trayectosEnLapso(null, (int) $this->coordinacion_id);
    }

    public function create()
    {
        $this->resetValidation();
        $this->resetFields();

        $this->loadTrayectosPrograma();
        $this->rows = [['id' => null, 'nombre' => '', 'es_obligatorio' => true]];

        $this->viewMode = 'form';
    }

    public function addRow()
    {
        $this->rows[] = ['id' => null, 'nombre' => '', 'es_obligatorio' => true];
    }

    public function removeRow($index)
    {
        if (count($this->rows) > 1) {
            unset($this->rows[$index]);
            $this->rows = array_values($this->rows);
        }
    }

    public function edit($id)
    {
        $this->resetValidation();
        $this->editingId = $id;

        $comp = Componente::find($id);

        if (!$comp) {
            abort(404);
        }

        $this->coordinacion_id = (string) $comp->coordinacion_id;
        $this->anio = $comp->anio;

        $this->rows = [
            [
                'id' => $comp->id,
                'nombre' => $comp->nombre,
                'es_obligatorio' => (bool) $comp->es_obligatorio,
            ]
        ];

        $this->loadTrayectosPrograma();
        $this->viewMode = 'form';
    }

    public function cancel()
    {
        $this->resetFields();
        $this->viewMode = 'list';
    }

    public function resetFields()
    {
        $this->editingId = null;
        $this->nombre = '';
        $this->coordinacion_id = '';
        $this->anio = '';
        $this->es_obligatorio = true;
        $this->rows = [];
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $createdCount = 0;
            foreach ($this->rows as $row) {
                if (!empty($row['id'])) {
                    $comp = Componente::find($row['id']);
                    if ($comp) {
                        $comp->update([
                            'nombre' => $row['nombre'],
                            'coordinacion_id' => $this->coordinacion_id,
                            'anio' => $this->anio,
                            'es_obligatorio' => $row['es_obligatorio'],
                        ]);
                    }
                } else {
                    Componente::create([
                        'nombre' => $row['nombre'],
                        'coordinacion_id' => $this->coordinacion_id,
                        'anio' => $this->anio,
                        'es_obligatorio' => $row['es_obligatorio'],
                        'estado_logico' => true,
                    ]);
                    $createdCount++;
                }
            }

            if ($createdCount > 0) {
                session()->flash('message', "Componente actualizado y {$createdCount} nuevos componentes agregados con éxito.");
            } else {
                session()->flash('message', 'Componente documental actualizado.');
            }
        } else {
            foreach ($this->rows as $row) {
                Componente::create([
                    'nombre' => $row['nombre'],
                    'coordinacion_id' => $this->coordinacion_id,
                    'anio' => $this->anio,
                    'es_obligatorio' => $row['es_obligatorio'],
                    'estado_logico' => true,
                ]);
            }
            session()->flash('message', count($this->rows) . ' Componentes creados con éxito.');
        }

        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus($id)
    {
        $comp = Componente::find($id);
        if ($comp) {
            $comp->update(['estado_logico' => !$comp->estado_logico]);
            session()->flash('message', 'Estado lógico del componente actualizado.');
        }
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        Componente::find($id)?->delete();
        session()->flash('message', 'Regla de componente eliminada de la base de datos.');
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        $query = Componente::query();

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('anio', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterPrograma !== '') {
            $query->where('coordinacion_id', $this->filterPrograma);
        }

        if ($this->filterTrayecto !== '') {
            $query->where('anio', $this->filterTrayecto);
        }

        $trayectosDb = Trayecto::on(DbHelper::connection())
            ->whereNotNull('tra_nombre')
            ->orderBy('tra_nombre')
            ->pluck('tra_nombre')
            ->unique()
            ->values()
            ->toArray();

        return [
            'listaRegistros' => $query->latest('id')->paginate(10),
            'programas' => app(AcademicCatalog::class)->programasForSelect(),
            'trayectos' => $trayectosDb ?: ['I', 'II', 'III', 'IV', 'V', 'VI'],
        ];
    }

    public function render()
    {
        return view('livewire.componente-manager', $this->with());
    }
}
