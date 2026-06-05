<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Organizacion extends RepositorioModel
{
    protected $table = 'organizacion';

    protected $schemaKey = 'organizacion';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'rif',
        'correo',
        'direccion',
        'cargo',
        'dep_codigo',
        'nombre_contacto',
        'apellido_contacto',
        'numero_contacto',
    ];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'org_dep_codigo', 'dep_codigo');
    }
}
