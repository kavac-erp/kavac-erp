<?php

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Purchase\Models\PurchaseSupplierBranch;
use Modules\Purchase\Models\PurchaseSupplierSpecialty;
use Modules\Purchase\Models\PurchaseSupplier;

/**
 * @class PurchaseSupplierDataMigratePivoteTableSeeder
 * @brief Ejecuta la modificación de datos en la tabla pivote de proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseSupplierDataMigratePivoteTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $purchaseSuppliers = PurchaseSupplier::orderBy('id')->get();
        foreach ($purchaseSuppliers as $purchaseSupplier) {
            if ($purchaseSupplier->purchase_supplier_branch_id != null) {
                $purchaseSupplier->purchaseSupplierBranch()->sync($purchaseSupplier->purchase_supplier_branch_id);
                $supplierBranch = PurchaseSupplier::find($purchaseSupplier->id);
                $supplierBranch->purchase_supplier_branch_id = null;
                $supplierBranch->save();
            }
            if ($purchaseSupplier->purchase_supplier_specialty_id != null) {
                $purchaseSupplier->purchaseSupplierSpecialty()->sync($purchaseSupplier->purchase_supplier_specialty_id);
                $supplierSpecialty = PurchaseSupplier::find($purchaseSupplier->id);
                $supplierSpecialty->purchase_supplier_specialty_id = null;
                $supplierSpecialty->save();
            }
        }
    }
}
