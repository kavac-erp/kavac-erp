<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @class Source
 * @brief Datos de fuentes de donde se almacena el receptor de un proceso
 *
 * Gestiona el modelo de datos para las fuentes de donde se almacenan
 * los receptores de procesos dentro del sistema.
 *
 * @property  string $receiver_id
 * @property  string $sourceable_type
 * @property  string $sourceable_id
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Source extends Model
{
    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'receiver_id', 'sourceable_type', 'sourceable_id'
    ];

    /**
     * Relación belongsTo que asocia la fuente del registro
     * con su receptor o beneficiario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function receiver()
    {
        return $this->belongsTo(Receiver::class);
    }

    /**
     * Los modelos registrados en el campo sourceable_type hacen
     * referencia a la fuente de un receptor de un proceso
     * o beneficiario, por ejemplo, un beneficiario registrado
     * desde un compromiso en el módulo de presupuesto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function sourceable()
    {
        return $this->morphTo();
    }
}
