<?php

namespace Modules\Purchase\Imports;

use Modules\Purchase\Models\PurchaseProduct;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

/**
 * @class PurchaseProductImport
 * @brief Gestiona las importaciones de los productos del módulo de compras
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseProductImport extends \App\Imports\DataImport implements
    ToModel,
    WithBatchInserts,
    WithChunkReading
{
    /**
     * Carga los datos del archivo
     *
     * @param array $row Datos del archivo
     *
     * @return \Illuminate\Database\Eloquent\Model|array|null
     */
    public function model(array $row)
    {
        /* Datos de los productos a importar */
        $data = [
            'name' => $row['nombre'],
            'code' => $row['codigo'],
        ];

        if (!empty($row['codigo']) && !is_null($row['codigo'])) {
            $product = PurchaseProduct::where('code', $row['codigo'])->first();
            if (!is_null($product)) {
                $product->name = $row['nombre'];
                $product->save();

                return [];
            } else {
                return new PurchaseProduct($data);
            }
        }
    }

    /**
     * Establece el tamaño de los trozos a leer
     *
     * @return integer
     */
    public function chunkSize(): int
    {
        return 5000;
    }

    /**
     * Establece el número de registros a insertar por trozo
     * @return integer
     */
    public function batchSize(): int
    {
        return 5000;
    }
}
