<?php

namespace Modules\Purchase\Imports;

use Modules\Purchase\Models\PurchaseProduct;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PurchaseProductImport extends \App\Imports\DataImport implements
    ToModel,
    WithBatchInserts,
    WithChunkReading
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        /** @var array Datos de los productos a importar */
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

    public function chunkSize(): int
    {
        return 5000;
    }

    public function batchSize(): int
    {
        return 5000;
    }
}
