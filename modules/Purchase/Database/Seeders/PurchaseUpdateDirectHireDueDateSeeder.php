<?php

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Purchase\Models\PurchaseDirectHire;
use Illuminate\Support\Facades\DB;

/**
 * @class $CLASS$
 * @brief [descripción detallada]
 *
 * Se adapta el formato del campo due_date a json_encode([$data_frame => $due_date])
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseUpdateDirectHireDueDateSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @method run
     *
     * @return void     [descripción de los datos devueltos]
     */
    public function run()
    {
        Model::unguard();

        DB::transaction(function () {

            //Se obtienen los resgistros de ordenes de compra, especificamente el campo plazo de entrega (due_date)
            $directHires = PurchaseDirectHire::all();
            if ($directHires) {
                foreach ($directHires as $directHire) {
                    if (preg_match("/inm/i", $directHire->due_date)) {
                        // Entrega inmediata
                        $directHire->due_date = json_encode(['delivery' => '']);
                    } elseif (preg_match("/día|dia/i", $directHire->due_date)) {
                        // Entrega en x dias
                        $days = explode(' ', $directHire->due_date);
                        $directHire->due_date = json_encode(['day' => $days ? $days[0] : 0]);
                    } elseif (preg_match("/sem/i", $directHire->due_date)) {
                        // Entrega en x semanas
                        $week = explode(' ', $directHire->due_date);
                        $directHire->due_date = json_encode(['week' => $week ? $week[0] : 0]);
                    } elseif (preg_match("/mes/i", $directHire->due_date)) {
                        // Entrega en x meses
                        $month = explode(' ', $directHire->due_date);
                        $directHire->due_date = json_encode(['month' => $month ? $month[0] : 0]);
                    } elseif (preg_match("/^[0-9]+$/", $directHire->due_date)) {
                        // Se asignan x dias si el campo contiene solo números
                        $directHire->due_date = json_encode(['day' => $directHire->due_date]);
                    } else {// La entrega es inmediata si no se consigue ningun resultado
                        $directHire->due_date = json_encode(['delivery' => '']);
                    }
                    $directHire->save();
                }
            }
        });
    }
}
