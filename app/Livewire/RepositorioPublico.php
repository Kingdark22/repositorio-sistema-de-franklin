<?php

namespace App\Livewire;

use App\Models\Proyecto;
use App\Models\Coordinacion;
use App\Services\ModuloRepositorioService;
use Livewire\Component;
use Livewire\WithPagination;

class RepositorioPublico extends Component
{
    use WithPagination;

    public $search = '';
    public $filterCoordinacion = '';
    public $filterLapso = '';

    public function with()
    {
        return [
            'proyectos' => Proyecto::busquedaPublica(
                $this->search,
                (int) $this->filterCoordinacion ?: null,
                $this->filterLapso
            )->latest()->paginate(9),
            'coordinaciones' => app(ModuloRepositorioService::class)
                ->queryModel(Coordinacion::class)
                ->orderBy('nombre')
                ->get(),
        ];
    }

    public function render()
    {
        return view('livewire.repositorio-publico', $this->with());
    }
}
