<?php

namespace Modules\DigitalSignature\Helpers;

use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Storage;

/**
 * @class Helper
 * @brief Gestiona los helpers del módulo de firma
 *
 * @author Ing. Pedro Buitrago <pbuitrago@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Helper
{
    /**
     * Retorna la dirección completa del ejecutable del firma PortableSigner o del archivo a firmar o verificar
     *
     * @param string $nameFile Nombre del archivo
     *
     * @return string
     */
    public function getPathSign($nameFile)
    {
        $module = Module::find('DigitalSignature');

        //obtiene la dirección del PortableSigner
        if ($nameFile == 'PortableSigner') {
            return($module->getPath() . '/PortableSigner/PortableSigner.jar');
        } else { //obtiene la dirección almacen del archivos pdf
            $path = Storage::disk('temporary')->path($nameFile);
            return($path);
        }
    }

    /**
     * Gestión de la cadena de caracteres de la verificación de la firma electrónica
     *
     * @param array $respverify Cadena de caracteres de la verificación de la firma
     *
     * @return array
     */
    public function getRespVerify($respverify)
    {
        $count = 0;
        $item = 0;
        $records = [
            "count" => 0,
            "signs" => [],
        ];

        // Función para buscar datos en la cadena de verificación de firma.
        function findData($signsInfo, $item, $data)
        {
            foreach ($signsInfo as $line) {
                if (strpos($line, $data) !== false) {
                    return substr($line, strpos($line, ':') + 2);
                }
            }
            return '';
        }

        for ($i = 0; $i < count($respverify); $i++) {
            if (substr_count($respverify[$i], 'Signature #') !== 0) {
                $count++;
            }
        }

        // Número de firmas
        $records["count"] = $count;

        for ($j = 1; $j <= $count; $j++) {
            // Número de la firma
            $records["signs"][$j]["Firma"] = $j;

            // Nombre del firmante
            $records["signs"][$j]["Nombre del firmante"] = findData($respverify, $item, 'Signer Certificate Common Name');

            // Sujeto firmante
            $records["signs"][$j]["Sujeto firmante"] = findData($respverify, $item, 'Signer full Distinguished Name');

            // Fecha de la firma
            $records["signs"][$j]["Fecha de la firma"] = findData($respverify, $item, 'Signing Time');

            // Algoritmo hash (reseña)
            $records["signs"][$j]["Algoritmo hash (reseña)"] = findData($respverify, $item, 'Signing Hash Algorithm');

            // Tipo de firma
            $records["signs"][$j]["Tipo de firma"] = findData($respverify, $item, 'Signature Type');

            // Validación de la firma
            $records["signs"][$j]["Validación de la firma"] = findData($respverify, $item, 'Signature Validation');

            // Validación del certificado
            $records["signs"][$j]["Validación del certificado"] = findData($respverify, $item, 'Certificate Validation');

            $item += 10; // Avanza al siguiente bloque de información de firma
        }

        return $records;
    }
}
