<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('multiexplode')) {
    /**
     * Divide una cadena de acuerdo a sus delimitadores y construye un arreglo con las subcadenas resultantes
     *
     * @method    multiexplode
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     {array}     $delimiters    Arreglo con los delimitadores de la cadena
     * @param     {string}    $string        Cadena de texto a ser procesada
     *
     * @return    {array}                    Arreglo con las subcadenas generadas
     */
    function multiexplode($delimiters, $string)
    {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return $launch;
    }
}

if (!function_exists('max_length')) {
    /**
     * Devuelve el valor de la cadena mas larga contenida en un arreglo
     *
     * @method    max_length
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     {array}     $records    Arreglo con los elementos a comparar
     *
     * @return    {array}                 Arreglo con las subcadenas generadas
     */
    function max_length($records)
    {
        $current = '';
        foreach ($records as $record) {
            if ($current != '') {
                if (strlen($record) > strlen($current)) {
                    $current = $record;
                }
            } else {
                $current = $record;
            }
        }
        return $current;
    }
}

if (!function_exists('str_eval')) {
    /**
     * Evalua una expresión contenida en una cadena
     *
     * @method    str_eval
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     {array}     $string    Cadena que contiene la expresión a evaluar
     *
     * @return    {array}
     */
    function str_eval($string)
    {
        $string = str_replace(',', '', $string);
        if (!str_contains($string, 'select(')) {
            $string = 'select(' . $string . ')';
        }

        try {
            $calc = DB::select(DB::raw($string));
        } catch (\Exception $error) {
            return false;
        }
        $col = '?column?';
        $value = $calc[0]->$col ?? $calc[0]->case;
        return $value;
    }
}

if (!function_exists('verify_assignment')) {
    /**
     * Evalua si un trabajador cumple con los parámetros establecidos
     *
     * @method    verify_assignment
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     {array}     $filters    Arreglo que contiene las expresión a evaluar
     * @param     {integer}   $id         Identificador único del trabajador
     *
     * @return    {boolean}
     */
    function verify_assignment($filters = [], $assignTo = [], $assignOptions = [], $id = null, $period_start = null, $period_end = null, $exceptions = [])
    {
        $now = ($period_end) ? new DateTime($period_end): new DateTime();
        $find = false;

        foreach ($filters as $filter) {
            $rule = null;
            foreach ($assignTo as $field) {
                if ($filter->id == 'staff') {
                    return in_array($id, $exceptions ?? []);
                }
                if ($field['id'] == $filter->id) {
                    $rule = $field;
                    break;
                }
            }
            if (isset($rule)) {
                if ($rule['id'] === 'staff_with_sons_has_scholarships') {
                    $options = [];
                    foreach ($assignOptions->where('key', $rule['id']) as $assign_option) {
                        if ($rule['type'] == 'range') {
                            $options = json_decode($assign_option['value']);
                        } elseif ($rule['type'] == 'list') {
                            array_push($options, $assign_option['assignable_id']);
                        }
                    };
                    $records = \Modules\Payroll\Models\PayrollStaff::select('payroll_staffs.id')
                        ->where('payroll_staffs.id', $id)
                        ->join('payroll_socioeconomics', 'payroll_staffs.id', '=', 'payroll_socioeconomics.payroll_staff_id')
                        ->join('payroll_family_burdens as family', 'payroll_socioeconomics.id', '=', 'family.payroll_socioeconomic_id')
                        ->where('family.payroll_relationships_id', 3)
                        ->where('family.has_scholarships', true)
                        ->whereIn('family.payroll_scholarship_types_id', $options)
                        ->get();
                } elseif ($rule['id'] === 'all_staff_with_sons') {
                    $options = [];
                    foreach ($assignOptions->where('key', $rule['id']) as $assign_option) {
                        if ($rule['type'] == 'range') {
                            $options = json_decode($assign_option['value']);
                        } elseif ($rule['type'] == 'list') {
                            array_push($options, $assign_option['assignable_id']);
                        }
                    };
                    $relationshipSon = \Modules\Payroll\Models\PayrollRelationship::query()
                        ->where('name', 'Hijo(a)')
                        ->first();
                    if (!isset($relationshipSon)) {
                        $relationshipSon["id"] = 3;
                    }
                    $relationship = $rule['whereHas'];
                    //relacion con socioeconomic
                    $records = $rule['model']::query()
                        ->where('id', $id)
                        ->with($relationship['field'])
                        ->whereHas($relationship['field'], function ($q) use ($relationship, $relationshipSon, $options, $period_start, $period_end, $now) {
                        if (isset($relationship['whereHas'])) {
                            /** Filtra la información a obtener mediante relaciones */
                            $relationshipR = $relationship['whereHas'];
                            //relacion con familyburden
                            $q->whereHas($relationshipR['field'], function ($qq) use ($relationshipR, $relationshipSon, $options, $now, $period_end) {
                                $maxNow = ($period_end)
                                ? new DateTime($period_end)
                                : new DateTime();
                                $min = date('Y-m-d', mktime(0, 0, 0, 1, 1, $now->modify('-' . ($options->maximum ?? 0) . 'year')->format('Y')));
                                $max = $maxNow->modify('-' . ($options->minimum ?? 0) . 'year');
                                $max_Date = $max->format('Y-m-d');
                                $qq->whereBetween("birthdate", [$min, $max_Date])->where('payroll_relationships_id', $relationshipSon["id"]);
                            });
                        }
                    })->get();
                } elseif ($rule['id'] === 'all_staff_with_sons_studying') {
                    $options = [];
                    foreach ($assignOptions->where('key', $rule['id']) as $assign_option) {
                        if ($rule['type'] == 'range') {
                            $options = json_decode($assign_option['value']);
                        } elseif ($rule['type'] == 'list') {
                            array_push($options, $assign_option['assignable_id']);
                        }
                    };
                    $relationshipSon = \Modules\Payroll\Models\PayrollRelationship::query()
                        ->where('name', 'Hijo(a)')
                        ->first();
                    if (!isset($relationshipSon)) {
                        $relationshipSon["id"] = 3;
                    }
                    $relationship = $rule['whereHas'];
                    //relacion con socioeconomic
                    $records = $rule['model']::query()
                        ->where('id', $id)
                        ->with($relationship['field'])
                        ->whereHas($relationship['field'], function ($q) use ($relationship, $relationshipSon, $options, $period_start, $period_end, $now) {
                        if (isset($relationship['whereHas'])) {
                            /** Filtra la información a obtener mediante relaciones */
                            $relationshipR = $relationship['whereHas'];
                            //relacion con familyburden
                            $q->whereHas($relationshipR['field'], function ($qq) use ($relationshipR, $relationshipSon, $options, $now, $period_end) {
                                if (isset($relationshipR['where'])) {
                                    $qq->where('is_student', true)->where('payroll_relationships_id', $relationshipSon["id"]);
                                }
                            });
                        }
                    })->get();
                } elseif ($rule['id'] === 'staff_according_position') {
                    $options = [];

                    foreach ($assignOptions->where('key', $rule['id']) as $assign_option) {
                        array_push($options, $assign_option['assignable_id']);
                    }

                    $records = \Modules\Payroll\Models\PayrollStaff::query()
                        ->where('id', $id)
                        ->whereHas('payrollEmployment.payrollPositions', function ($query) use ($options) {
                            $query->whereIn('payroll_positions.id', $options);
                        })
                        ->get();
                } elseif (str_contains($rule['id'], 'all')) {
                    $options = [];
                    foreach ($assignOptions->where('key', $rule['id']) as $assign_option) {
                        if ($rule['type'] == 'range') {
                            $options = json_decode($assign_option['value']);
                        } elseif ($rule['type'] == 'list') {
                            array_push($options, $assign_option['assignable_id']);
                        }
                    };
                    if ($filter) {
                        if (isset($rule['whereHas'])) {
                            /** Filtra la información a obtener mediante relaciones */
                            $relationship = $rule['whereHas'];
                            $records = $rule['model']::query()
                                ->where('id', $id)
                                ->with($relationship['field'])
                                ->whereHas($relationship['field'], function ($q) use ($relationship, $options, $period_start, $period_end, $now) {
                                    if (isset($relationship['whereHas'])) {
                                        /** Filtra la información a obtener mediante relaciones */
                                        $relationshipR = $relationship['whereHas'];
                                        $q->whereHas($relationshipR['field'], function ($qq) use ($relationshipR, $options, $now, $period_end) {
                                            if (isset($relationshipR['where'])) {
                                                $qq->where($relationshipR['where'][0], $relationshipR['where'][1]);
                                            } elseif (isset($relationshipR['whereRaw'])) {
                                                $raw = $relationshipR['whereRaw'];
                                                $qq->select("id", "birthdate")->whereRaw("(DATE_PART('year',  '" . $now . "'::date) - DATE_PART('year', " . $raw['field'] . "::date))  > " . $options->minimum)
                                                    ->whereRaw("(DATE_PART('year',  '" . $now . "'::date) - DATE_PART('year', " . $raw['field'] . "::date))  < " . $options->maximum);
                                            } elseif (isset($relationshipR['whereYear'])) {
                                                $maxNow = ($period_end)
                                                ? new DateTime($period_end)
                                                : new DateTime();
                                                $min = date('Y-m-d', mktime(0, 0, 0, 1, 1, $now->modify('-' . ($options->maximum ?? 0) . 'year')->format('Y')));
                                                $max = $maxNow->modify('-' . ($options->minimum ?? 0) . 'year');
                                                $qq->whereBetween($relationshipR['whereYear'], [$min, $max]);
                                            }
                                        });
                                    } elseif (isset($relationship['where'])) {
                                        $q->where($relationship['where'][0], $relationship['where'][1]);
                                    } elseif (isset($relationship['whereDate'])) {
                                        $date = $now->modify('-' . ($options->maximum) . 'year');
                                        $q->whereDate($relationship['whereDate'], '<=', $date);
                                        if (($period_start && $period_end)) {
                                            $start = explode('-', $period_start);
                                            $end = explode('-', $period_end);

                                            if ($start[1] > $end[1]) {
                                                $q->whereBetween(
                                                    DB::raw("to_char(" . $relationship['whereDate'] . ", 'MM-DD')"),
                                                    [$start[1] . '-' . $start[2], '12-31']
                                                )
                                                    ->OrWhereBetween(
                                                        DB::raw("to_char(" . $relationship['whereDate'] . ", 'MM-DD')"),
                                                        ['01-01', $end[1] . '-' . $end[2]]
                                                    );
                                            } elseif ($start[1] . '-' . $start[2] == $end[1] . '-' . $end[2]) {
                                                if ($start[0] != $end[0]) {
                                                    $q->WhereBetween(
                                                        DB::raw("to_char(" . $relationship['whereDate'] . ", 'MM-DD')"),
                                                        ['01-01', '12-31']
                                                    );
                                                } else {
                                                    $q->Where(
                                                        DB::raw("to_char(" . $relationship['whereDate'] . ", 'MM-DD')"),
                                                        $start[1] . '-' . $start[2]
                                                    );
                                                }
                                            } else {
                                                $q->whereBetween(
                                                    DB::raw("to_char(" . $relationship['whereDate'] . ", 'MM-DD')"),
                                                    [$start[1] . '-' . $start[2], $end[1] . '-' . $end[2]]
                                                );
                                            }
                                        }
                                    }
                                })->get();
                        } elseif (isset($rule['where'])) {
                            $records = $rule['model']::query()
                                ->where('id', $id)
                                ->where($rule['where'][0], $rule['where'][1])
                                ->get();
                        }
                    }
                } else {
                    $options = [];
                    foreach ($assignOptions->where('key', $rule['id']) as $assign_option) {
                        if ($rule['type'] == 'range') {
                            $options = json_decode($assign_option['value']);
                        } elseif ($rule['type'] == 'list') {
                            array_push($options, $assign_option['assignable_id']);
                        }
                    }
                    if ((($rule['type'] == null) || ($rule['type'] == '')) && (!empty($rule['whereHas']) && !empty($rule['whereHas']['withCount']))) {
                        $relationship = $rule['whereHas'];
                        $records = \Modules\Payroll\Models\PayrollStaff::whereId($id)->with(
                            [
                                $relationship['field'] => function ($q) use ($relationship) {
                                    $q->withCount($relationship['withCount']);
                                },
                            ]
                        )->first();
                        if (isset($records)) {
                            $fieldCount = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $relationship['withCount']));
                            if ($records->{$relationship['field']}->{$fieldCount . "_count"} > 1) {
                                $find = true;
                                break;
                            }
                        }
                    } else {
                        if (isset($rule['whereHas'])) {
                            $relationship = $rule['whereHas'];
                            $records = \Modules\Payroll\Models\PayrollStaff::query()
                                ->where('id', $id)
                                ->whereHas(
                                    $relationship['field'],
                                    function ($query) use ($options, $relationship) {
                                        if (isset($relationship['whereHas'])) {
                                            $relationshipR = $relationship['field'];
                                            $query->whereHas($relationshipR, function ($q) use ($options, $relationshipR) {
                                                $q->whereIn('id', $options)->get();
                                            })->get();
                                        } elseif (isset($relationship['has'])) {
                                            $query->has($relationship['has']['field']);
                                        } elseif (isset($relationship['whereNotIn'])) {
                                            $query->whereNotIn('id', $options)->get();
                                        } elseif (isset($relationship['whereIn'])) {
                                            $query->whereIn($relationship['whereIn'][0], $options);
                                        }
                                    }
                                )->get();
                        } elseif (isset($rule['whereIn'])) {
                            $records = \Modules\Payroll\Models\PayrollStaff::query()
                                ->where('id', $id)
                                ->whereIn($rule['whereIn'][0], $options)
                                ->get();
                        } elseif (isset($rule['whereNotIn'])) {
                            $records = \Modules\Payroll\Models\PayrollStaff::query()
                                ->where('id', $id)
                                ->whereNotIn($rule['whereNotIn'][0], $options)
                                ->get();
                        }
                    }
                }
            }
            if (isset($records) && empty($rule['whereHas']['withCount'])) {
                if ($records->find($id) != null) {
                    $find = true;
                    break;
                }
            }
        }

        return $find;
    }
}

if (!function_exists('expression_format')) {
    /**
     * Convierte los parámetros de una expresión matemática en su representacion flotante
     *
     * @method    expression_format
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     {string}   $expression         Expresion a revisar y transformar en formato flotante
     *
     * @return    {string}
     */
    function expression_format($expression)
    {
        return preg_replace_callback('/\d+(\.\d+)?/', function ($match) {
            return currency_format($match[0], 4, true);
        }, $expression);
    }
}
