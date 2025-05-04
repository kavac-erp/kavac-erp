<?php

if (! function_exists('format_bank_account')) {
    /**
     * Establece una cadena formateada con el número de cuenta bancaria
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string   $account Número de cuenta bancaria
     * @param  boolean  $formatted Establece si se desea una cadena formateada o no
     *
     * @return string Devuelve el número de cuenta formateado o no segun el filtro indicado
     */
    function format_bank_account($account, $formatted = null)
    {
        $account_formatted = '';
        for ($i = 0; $i < strlen($account); $i++) {
            if ($formatted && in_array($i, [4, 8, 10]) && $account[$i] !== "-") {
                    $account_formatted .= '-';
            }
                $account_formatted .= $account[$i];
        }

        return $account_formatted;
    }
}
