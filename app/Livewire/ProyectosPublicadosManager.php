<?php

namespace App\Livewire;

use App\Models\ComentarioProyecto;
use App\Models\ProyectoPublicado;
use Livewire\Component;

class ProyectosPublicadosManager extends Component
{
    public $selectedPubId = null;
    public $nuevoComentario = '';

    public string $mensaje = '';
    public string $tipoMensaje = 'success';

    public function seleccionar($pubId): void
    {
        $this->selectedPubId = $pubId;
        $this->nuevoComentario = '';
    }

    public function cerrar(): void
    {
        $this->selectedPubId = null;
        $this->nuevoComentario = '';
    }

    public function comentar(): void
    {
        $this->validate([
            'nuevoComentario' => 'required|min:3|max:1000',
        ]);

        if (!$this->selectedPubId) {
            return;
        }

        $pub = ProyectoPublicado::find($this->selectedPubId);
        if (!$pub) {
            return;
        }

        ComentarioProyecto::create([
            'descripcion' => trim($this->nuevoComentario),
            'proyecto_id' => $pub->proyecto_id,
        ]);

        $this->nuevoComentario = '';
        $this->tipoMensaje = 'success';
        $this->mensaje = 'Comentario agregado correctamente.';
    }

    public function limpiarMensaje(): void
    {
        $this->mensaje = '';
    }

    public function render()
    {
        $publicaciones = ProyectoPublicado::where('estado', 'publicado')
            ->with('proyecto')
            ->orderBy('id', 'desc')
            ->get();

        $comentarios = collect();
        if ($this->selectedPubId) {
            $pub = ProyectoPublicado::find($this->selectedPubId);
            if ($pub) {
                $comentarios = ComentarioProyecto::where('proyecto_id', $pub->proyecto_id)
                    ->orderBy('id', 'desc')
                    ->get();
            }
        }

        return view('livewire.proyectos-publicados-manager', [
            'publicaciones' => $publicaciones,
            'comentarios' => $comentarios,
        ]);
    }
}
