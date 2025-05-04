<?php

namespace App\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class Currency
 * @brief Datos de Monedas
 *
 * Gestiona el modelo de datos para las Monedas
 *
 * @property  string  $id
 * @property  string  $symbol
 * @property  string  $name
 * @property  string  $plural_name
 * @property  integer $country_id
 * @property  boolean $default
 * @property  integer $decimal_places
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Currency extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

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
    protected $fillable = ['symbol', 'name', 'plural_name', 'country_id', 'default', 'decimal_places'];

    /**
     * Oculta los campos de fechas de creación, actualización y eliminación
     *
     * @var    array $hidden
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Campos a agregar en las consultas
     *
     * @var array $appends
     */
    protected $appends = ['description'];

    /**
     * Obtiene una descripción para la moneda
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    string                     Descripción de la moneda
     */
    public function getDescriptionAttribute()
    {
        return "{$this->symbol} - {$this->name}";
    }

    /**
     * Método que obtiene el Pais de una Moneda
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Método que obtiene los tipos de cambio desde la moneda a la cual se va a convertir
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fromExchangeRates()
    {
        return $this->hasMany(ExchangeRate::class, 'from_currency_id');
    }

    /**
     * Metodo que obtiene los tipos de cambio de la moneda a la cual se realizó la converción
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function toExchangeRates()
    {
        return $this->hasMany(ExchangeRate::class, 'to_currency_id');
    }

    /**
     * Metodo que se ejecuta cuando se elimina un registro
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::deleting(function ($model) {
            if (has_data_in_foreign_key($model->id, 'currency_id')) {
                throw new \Exception('No se puede eliminar este registro debido a que tiene otros registros asociados');
            };
        });
    }
}
