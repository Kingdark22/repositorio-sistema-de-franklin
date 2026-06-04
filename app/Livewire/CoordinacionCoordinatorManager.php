<?php

namespace App\Livewire;

use App\Helpers\DualDatabase;
use App\Services\AcademicCatalog;
use App\Services\IntranetProfessorService;
use App\Services\ModuloRepositorioService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CoordinacionCoordinatorManager extends Component
{
    use WithPagination;

    public string $search = '';

    public function with()
    {
        $profesores = app(IntranetProfessorService::class);
        $conn = DualDatabase::academicConnection();
        $map = config('roles.usu_cod_rol_map', []);
        $codCoord = array_search('coordinador', $map, true);

        $coordinadores = collect();
        if ($codCoord !== false) {
            try {
                $qb = DB::connection($conn)
                    ->table('usuario as u')
                    ->leftJoin('persona as p', DB::raw('TRIM(p.per_cedula)'), '=', DB::raw('TRIM(u.usu_cedula)'))
                    ->where('u.usu_cod_rol', (int) $codCoord);

                if ($this->search !== '') {
                    $termRaw = trim(mb_strtolower($this->search));
                    $digits = preg_replace('/\D+/', '', $termRaw);
                    if ($digits !== '' && ctype_digit($digits) && strlen($digits) >= 4) {
                        $qb->whereRaw('TRIM(u.usu_cedula) LIKE ?', [$digits.'%']);
                    } else {
                        $parts = preg_split('/\s+/', $termRaw);
                        $qb->where(function ($qparts) use ($parts) {
                            foreach ($parts as $pw) {
                                $pwLike = $pw.'%';
                                $qparts->orWhereRaw('LOWER(TRIM(p.per_nombres)) LIKE ?', [$pwLike])
                                    ->orWhereRaw('LOWER(TRIM(p.per_apellidos)) LIKE ?', [$pwLike])
                                    ->orWhereRaw('LOWER(TRIM(u.usu_nombre)) LIKE ?', [$pwLike]);
                            }
                        });
                    }
                }

                $coordinadores = $qb
                    ->selectRaw('TRIM(u.usu_cedula) as cedula')
                    ->selectRaw('TRIM(COALESCE(p.per_nombres, u.usu_nombre)) as nombre')
                    ->selectRaw('TRIM(p.per_apellidos) as apellido')
                    ->orderBy('apellido')
                    ->orderBy('nombre')
                    ->limit(200)
                    ->get();
            } catch (\Throwable) {
                $coordinadores = collect();
            }
        }

        $lap = $profesores->lapsoVigenteCodigo();
        $docentes = $lap
            ? $profesores->paginateDocentesActivos($this->search, $lap, 8, $this->getPage())
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 8, 1);

        $coordinacionesCatalogo = app(ModuloRepositorioService::class)->coordinacionesActivas();

        return [
            'coordinadoresIntranet' => $coordinadores,
            'docentes' => $docentes,
            'programas' => app(AcademicCatalog::class)->programasForSelect(),
            'lapsoVigente' => $lap,
            'coordinacionesCatalogo' => $coordinacionesCatalogo,
        ];
    }

    public function render()
    {
        return view('livewire.coordinacion-coordinator-manager', $this->with());
    }
}
