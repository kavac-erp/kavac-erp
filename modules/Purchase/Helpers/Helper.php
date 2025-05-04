<?php

if (!function_exists('generate_registration_code_budget')) {
    /**
     * Genera códigos a implementar en los registros de orden de compra/servicio
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string           $prefix      Prefijo que identifica el código
     * @param  integer          $code_length Longitud máxima permitida para el código a generar
     * @param  integer|string   $year        Sufijo que identifica el año del cual se va a generar el código
     * @param  string           $model       Namespace y nombre del modelo en donde se aplicará el nuevo código
     * @param  string           $field       Nombre del campo del código a generar
     *
     * @return string|array                  Retorna una cadena con el nuevo código
     */
    function generate_registration_code_budget($prefix, $code_length, $year, $model, $field)
    {
        $newCode = 1;

        $targetModel = $model::select($field)
        ->where($field, 'like', "{$prefix}-%-{$year}")
        ->withTrashed()
        ->orderByRaw("CAST(split_part($field, '-', 2) AS INTEGER) DESC")
        ->orderBy('id', 'desc')
        ->first();

        $newCode += ($targetModel) ? (int)explode('-', $targetModel->$field)[1] : 0;

        if (strlen((string)$newCode) > $code_length) {
            return ["error" => "El nuevo código excede la longitud permitida"];
        }

        return "{$prefix}-{$newCode}-{$year}";
    }
}
