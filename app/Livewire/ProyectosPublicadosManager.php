<?php

namespace App\Livewire;

use App\Models\ComentarioProyecto;
use App\Models\Organizacion;
use App\Models\Proyecto;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ProyectosPublicadosManager extends Component
{
    public $selectedPubId = null;
    public $nuevoComentario = '';

    public string $mensaje = '';
    public string $tipoMensaje = 'success';

    public array $selectedProjects = [];
    public bool $showEmailPanel = false;

    public string $searchOrg = '';
    public array $selectedEmails = [];
    public string $emailSubject = '';
    public string $emailBody = '';
    public bool $selectAllOrgs = false;

    public string $search = '';
    public string $filterComunidadId = '';

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

        $pub = Proyecto::find($this->selectedPubId);
        if (!$pub) {
            return;
        }

        ComentarioProyecto::create([
            'descripcion' => trim($this->nuevoComentario),
            'proyecto_id' => $pub->id,
        ]);

        $this->nuevoComentario = '';
        $this->tipoMensaje = 'success';
        $this->mensaje = 'Comentario agregado correctamente.';
    }

    public function limpiarMensaje(): void
    {
        $this->mensaje = '';
    }

    public function toggleProject($id): void
    {
        if (in_array($id, $this->selectedProjects)) {
            $this->selectedProjects = array_values(array_diff($this->selectedProjects, [$id]));
        } else {
            $this->selectedProjects[] = (int) $id;
        }
    }

    public function selectAll(): void
    {
        $this->selectedProjects = $this->proyectosQuery()->pluck('id')->toArray();
    }

    public function deselectAll(): void
    {
        $this->selectedProjects = [];
    }

    public function openEmailPanel(): void
    {
        $this->showEmailPanel = true;
        $this->searchOrg = '';
        $this->selectedEmails = [];
        $this->emailSubject = 'Proyectos aprobados - Sistema de Gestión';
        $this->emailBody = 'Se adjuntan los proyectos aprobados seleccionados.';
        $this->selectAllOrgs = false;
    }

    public function closeEmailPanel(): void
    {
        $this->showEmailPanel = false;
    }

    public function updatedSearchOrg(): void
    {
        $this->selectAllOrgs = false;
    }

    public function toggleSelectAllEmails(): void
    {
        $this->selectAllOrgs = !$this->selectAllOrgs;
        if ($this->selectAllOrgs) {
            $organizations = $this->loadOrganizations();
            $allEmails = [];
            foreach ($organizations as $org) {
                if ($org->correo) {
                    $allEmails[] = $org->correo;
                }
                foreach ($org->contactos as $contacto) {
                    if ($contacto->oco_correo) {
                        $allEmails[] = $contacto->oco_correo;
                    }
                }
            }
            $this->selectedEmails = array_values(array_unique($allEmails));
        } else {
            $this->selectedEmails = [];
        }
    }

    public function toggleEmail($index): void
    {
        $idx = (int) $index;
        $emails = $this->buildEmailList();
        if (!isset($emails[$idx])) {
            return;
        }
        $email = $emails[$idx];
        if (in_array($email, $this->selectedEmails)) {
            $this->selectedEmails = array_values(array_diff($this->selectedEmails, [$email]));
        } else {
            $this->selectedEmails[] = $email;
        }
    }

    protected function buildEmailList(): array
    {
        $orgs = $this->loadOrganizations();
        $list = [];
        foreach ($orgs as $org) {
            if ($org->correo) {
                $list[] = $org->correo;
            }
            foreach ($org->contactos as $contacto) {
                if ($contacto->oco_correo) {
                    $list[] = $contacto->oco_correo;
                }
            }
        }
        return $list;
    }

    protected function loadOrganizations()
    {
        $query = Organizacion::with('contactos');
        if ($this->searchOrg !== '') {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->searchOrg . '%')
                    ->orWhere('correo', 'like', '%' . $this->searchOrg . '%')
                    ->orWhere('rif', 'like', '%' . $this->searchOrg . '%');
            });
        }
        return $query->orderBy('nombre')->get();
    }

    public function sendProjects(): void
    {
        $this->validate([
            'selectedProjects' => 'required|array|min:1',
            'selectedEmails' => 'required|array|min:1',
            'emailSubject' => 'required|min:5|max:255',
            'emailBody' => 'required|min:10',
        ], [
            'selectedProjects.required' => 'Debe seleccionar al menos un proyecto.',
            'selectedEmails.required' => 'Debe seleccionar al menos un destinatario.',
            'emailSubject.required' => 'El asunto es obligatorio.',
            'emailBody.required' => 'El mensaje es obligatorio.',
        ]);

        $proyectos = Proyecto::whereIn('id', $this->selectedProjects)
            ->where('estado_validacion', 'aprobado')
            ->get();

        if ($proyectos->isEmpty()) {
            $this->tipoMensaje = 'error';
            $this->mensaje = 'No se encontraron proyectos válidos para enviar.';
            return;
        }

        try {
            Mail::raw($this->emailBody, function ($message) use ($proyectos) {
                $message->to($this->selectedEmails)
                    ->subject($this->emailSubject);

                foreach ($proyectos as $proyecto) {
                    if ($proyecto->archivo_path) {
                        $path = storage_path('app/public/' . $proyecto->archivo_path);
                        if (file_exists($path)) {
                            $message->attach($path, [
                                'as' => 'proyecto_' . $proyecto->id . '.pdf',
                                'mime' => 'application/pdf',
                            ]);
                        }
                    }
                }
            });

            $this->tipoMensaje = 'success';
            $this->mensaje = 'Proyectos enviados correctamente a ' . count($this->selectedEmails) . ' destinatario(s).';
            $this->showEmailPanel = false;
            $this->selectedEmails = [];
        } catch (\Throwable $e) {
            $this->tipoMensaje = 'error';
            $this->mensaje = 'Error al enviar: ' . $e->getMessage();
        }
    }

    protected function proyectosQuery()
    {
        $query = Proyecto::with('comunidad')
            ->where('estado_validacion', 'aprobado')
            ->where('estado_logico', true);

        if ($this->filterComunidadId !== '') {
            $query->where('com_codigo', (int) $this->filterComunidadId);
        }

        if ($this->search !== '') {
            $term = '%' . mb_strtolower(trim($this->search)) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(pry_titulo) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(pry_resumen) LIKE ?', [$term]);
            });
        }

        return $query->orderBy('id', 'desc');
    }

    public function render()
    {
        $proyectos = $this->proyectosQuery()->get();

        $comentarios = collect();
        if ($this->selectedPubId) {
            $proyecto = Proyecto::find($this->selectedPubId);
            if ($proyecto) {
                $comentarios = ComentarioProyecto::where('proyecto_id', $proyecto->id)
                    ->orderBy('id', 'desc')
                    ->get();
            }
        }

        $comunidades = \App\Models\Comunidad::orderBy('nombre')->get();

        $organizations = $this->showEmailPanel ? $this->loadOrganizations() : collect();

        $emailList = $this->showEmailPanel ? $this->buildEmailList() : [];

        return view('livewire.proyectos-publicados-manager', [
            'proyectos' => $proyectos,
            'comentarios' => $comentarios,
            'comunidades' => $comunidades,
            'organizations' => $organizations,
            'emailList' => $emailList,
        ]);
    }
}
