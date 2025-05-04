<?php

namespace Modules\Purchase\Models;

use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Modules\Purchase\Models\PurchaseSupplierObject;

/**
 * @class PurchaseSupplier
 * @brief Datos de los proveedores
 *
 * Gestiona el modelo de datos para los proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseSupplier extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos de tipo fecha
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de relaciones a cargar por defecto con el modelo
     *
     * @var array $with
     */
    protected $with = [
        'purchaseSupplierSpecialty', 'purchaseSupplierType', 'purchaseSupplierBranch', 'purchaseSupplierObjects',
        'phones', 'city', 'contacts'
    ];

    /**
     * Lista de atributos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'rif', 'code', 'name', 'direction', 'person_type', 'company_type', 'website',
        'active', 'purchase_supplier_specialty_id', 'purchase_supplier_type_id', 'purchase_supplier_object_id',
        'purchase_supplier_branch_id', 'accounting_account_id','country_id', 'estate_id', 'city_id', 'rnc_status',
        'rnc_certificate_number', 'social_purpose', 'file_number'
    ];

    /**
     * Lista de atributos personalizados a cargar con el modelo
     *
     * @var array $appends
     */
    protected $appends = ['referential_name'];

    /**
     * Obtiene el nombre completo del proveedor
     *
     * @return string
     */
    public function getReferentialNameAttribute()
    {
        return $this->rif . " - " . $this->name;
    }

    /**
     * Establece la relación con los teléfonos de los proveedores
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function phones()
    {
        return $this->morphMany(\App\Models\Phone::class, 'phoneable');
    }

    /**
     * Establece la relación con los contactos de los proveedores
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function contacts()
    {
        return $this->morphMany(\App\Models\Contact::class, 'contactable');
    }

    /**
     * Obtiene todos los documentos asociados al proveedor
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Establece la relación con las especialidades de los proveedor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function purchaseSupplierSpecialty()
    {
        return $this->belongsToMany(PurchaseSupplierSpecialty::class, 'purchase_specialty_supplier');
    }

    /**
     * Establece la relación con el tipo de proveedor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseSupplierType()
    {
        return $this->belongsTo(PurchaseSupplierType::class);
    }

    /**
     * Establece la relación con el Pais de ubicación del proveedor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Establece la relación con el Estado de ubicación del proveedor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    /**
     * PurchaseSupplier belongs to City.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Establece la relación con las ramas de los proveedores
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function purchaseSupplierBranch()
    {
        return $this->belongsToMany(PurchaseSupplierBranch::class, 'purchase_branch_supplier');
    }

    /**
     * Establece la relación con l;os objetos de los proveedores
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function purchaseSupplierObjects()
    {
        return $this->belongsToMany(PurchaseSupplierObject::class, 'purchase_object_supplier');
    }

    /**
     * Establece la relación con las ordenes de compra
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseOrder()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Establece la relación con las cuentas contables del módulo de contabilidad si esta presente
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingAccount()
    {
        return (
            Module::has('Accounting') && Module::isEnabled('Accounting')
        ) ? $this->belongsTo(\Modules\Accounting\Models\AccountingAccount::class) : null;
    }
}
