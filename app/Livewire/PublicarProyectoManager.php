<?php

namespace App\Livewire;

use App\Models\Proyecto;
use App\Models\ProyectoPublicado;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PublicarProyectoManager extends Component
{
    public string $busqueda = '';
    public array $seleccionados = [];

    public string $mensaje = '';
    public string $tipoMensaje = 'success';

    public function publicar(): void
    {
        $ids = array_keys(array_filter($this->seleccionados));
        if (empty($ids)) {
            $this->tipoMensaje = 'error';
            $this->mensaje = 'Selecciona al menos un proyecto.';
            return;
        }

        $publicados = 0;
        foreach ($ids as $pryCodigo) {
            $existe = ProyectoPublicado::where('proyecto_id', $pryCodigo)->exists();
            if (!$existe) {
                ProyectoPublicado::create([
                    'proyecto_id' => $pryCodigo,
                    'estado' => 'publicado',
                ]);
                $publicados++;
            }
        }

        $this->seleccionados = [];
        $this->tipoMensaje = 'success';
        $this->mensaje = $publicados > 0
            ? "$publicados proyecto(s) publicado(s) correctamente."
            : 'Los proyectos seleccionados ya estaban publicados.';
    }

    public function limpiarMensaje(): void
    {
        $this->mensaje = '';
    }

    public function render()
    {
        $user = Auth::user();

        $proyectos = Proyecto::where('estado_logico', true)
            ->when($this->busqueda, fn ($q) => $q->where('titulo', 'like', '%' . $this->busqueda . '%'))
            ->orderBy('fecha_subida', 'desc')
            ->get();

        $publicados = ProyectoPublicado::where('estado', 'publicado')
            ->get()
            ->pluck('proyecto_id')
            ->toArray();

        return view('livewire.publicar-proyecto-manager', [
            'proyectos' => $proyectos,
            'publicados' => $publicados,
        ]);
    }
}
