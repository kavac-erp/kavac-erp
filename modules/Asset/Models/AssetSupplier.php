<?php

namespace Modules\Asset\Models;

use App\Models\Phone;
use App\Models\Contact;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Asset\Models\AssetSupplierObject;
use Modules\Accounting\Models\AccountingAccount;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class AssetSupplier
 * @brief Datos de los proveedores
 *
 * Gestiona el modelo de datos para los proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSupplier extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Nombre de la tabla
     *
     * @var array $table
     */
    protected $table = 'purchase_suppliers';

    /**
     * Lista de campos de tipo fecha
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de relaciones a cargar con la consulta
     *
     * @var array $with
     */
    protected $with = [
        'assetSupplierSpecialty', 'assetSupplierType', 'assetSupplierBranch', 'assetSupplierObjects',
        'phones', 'city', 'contacts'
    ];

    /**
     * Lista de campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'rif', 'code', 'name', 'direction', 'person_type', 'company_type', 'website',
        'active', 'purchase_supplier_specialty_id', 'purchase_supplier_type_id', 'purchase_supplier_object_id',
        'purchase_supplier_branch_id', 'accounting_account_id','country_id', 'estate_id', 'city_id', 'rnc_status',
        'rnc_certificate_number', 'social_purpose'
    ];

    /**
     * Lista de atributos personalizados a cargar con la consulta
     *
     * @var array $appends
     */
    protected $appends = ['referential_name'];

    /**
     * Obtiene el atributo del nombre referencial
     *
     * @return string
     */
    public function getReferentialNameAttribute()
    {
        return $this->rif . " - " . $this->name;
    }

    /**
     * Obtiene todas las relaciones con los números telefónicos
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function phones()
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    /**
     * Obtiene todas las relaciones con los contactos
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable');
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
     * AssetSupplier belongs to AssetSupplierSpecialty.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assetSupplierSpecialty()
    {
        return $this->belongsToMany(
            AssetSupplierSpecialty::class,
            'purchase_specialty_supplier',
            'purchase_supplier_id',
            'purchase_supplier_specialty_id'
        );
    }

    /**
     * Obtiene la relación con el tipo de proveedor de bienes
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assetSupplierType()
    {
        return $this->belongsTo(AssetSupplierType::class);
    }

    /**
     * Obtiene la relacion con el pais del proveedor de bienes
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Obtiene la relacion con el estado del proveedor de bienes
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    /**
     * Obtiene la relacion con la ciudad del proveedor de bienes
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Obtiene la relacion con la rama del proveedor de bienes
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assetSupplierBranch()
    {
        return $this->belongsToMany(
            AssetSupplierBranch::class,
            'purchase_branch_supplier',
            'purchase_supplier_id',
            'purchase_supplier_branch_id'
        );
    }

    /**
     * Obtiene la relacion con los objetos del proveedor de bienes
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assetSupplierObjects()
    {
        return $this->belongsToMany(
            AssetSupplierObject::class,
            'purchase_object_supplier',
            'purchase_supplier_id',
            'purchase_supplier_object_id'
        );
    }

    /**
     * Obtiene la relacion con la cuenta contable del proveedor de bienes
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingAccount()
    {
        return (
            Module::has('Accounting') && Module::isEnabled('Accounting')
        ) ? $this->belongsTo(AccountingAccount::class) : [];
    }
}
