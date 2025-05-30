<?php

/**
 * @file Helper.php
 * @brief Definición de funciones de ayuda
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 */

use Carbon\Carbon;
use App\Models\Currency;
use App\Models\Institution;
use App\Models\DocumentStatus;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

if (!function_exists('set_active_menu')) {
    /**
     * Define la opción activa del menú según la URL actual
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param array|string $compareUrls Nombre o lista de nombres de las URL a comparar
     *
     * @return string Si la URL a comparar es igual a la actual retorna active de lo contrario retorna vacio
     */
    function set_active_menu($compareUrls)
    {
        $currentUrl = Route::current()->getName();
        if (is_array($compareUrls)) {
            $active = '';
            foreach ($compareUrls as $url) {
                if ($currentUrl == $url) {
                    return 'active';
                }
            }
            return $active;
        }

        return ($currentUrl == $compareUrls) ? 'active' : '';
    }
}

if (!function_exists('display_submenu')) {
    /**
     * Define si se expande o contrae las opciones de un submenu
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string|array $submenu Nombre del submenu a mostrar u ocultar
     *
     * @return string  Retorna una cadena vacia para contraer las opciones del submenú,
     *                 de lo contrario retorna el css para mostrar el bloque de opciones
     */
    function display_submenu($submenu)
    {
        if (is_array($submenu)) {
            foreach ($submenu as $sb) {
                if ($sb !== '' && strpos(Route::current()->getName(), $sb) !== false) {
                    return 'display:block';
                }
            }
        }
        return (
            !is_array($submenu) && $submenu !== '' && strpos(Route::current()->getName(), $submenu) !== false
        ) ? 'display:block;' : '';
    }
}

if (!function_exists('generate_registration_code')) {
    /**
     * Genera códigos a implementar en los diferentes registros del sistema
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
    function generate_registration_code($prefix, $code_length, $year, $model, $field)
    {
        $newCode = 1;

        $targetModel = $model::select($field)->where($field, 'like', "{$prefix}-%-{$year}")
                            ->withTrashed()->orderBy('id', 'desc')->first();

        $newCode += ($targetModel) ? (int)explode('-', $targetModel->$field)[1] : 0;

        if (strlen((string)$newCode) > $code_length) {
            return ["error" => "El nuevo código excede la longitud permitida"];
        }

        return "{$prefix}-{$newCode}-{$year}";
    }
}

if (!function_exists('template_choices')) {
    /**
     * Construye un arreglo de elementos para usar en plantillas blade
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string|object    $model       Nombre de la clase del modelo al cual generar el listado de opciones
     * @param  string|array     $fields      Campo(s) a utilizar para mostrar en el listado de opciones
     * @param  string|array     $filters     Arreglo con los filtros a ser aplicados en la consulta
     *                                       Ej. sin relación con otro modelo: ['active' => 'true']
     *                                       Ej. con relación a otro modelo:
     *                                       ['relationship' => 'metodoRelacion', 'where' => ['active' => true]]
     * @param  boolean          $vuejs       Indica si las opciones a mostrar son para una plantilla normal o para VueJS
     * @param  integer          $except_id   Identificador del registro a excluir. Opcional
     * @param string            $placeholder Texto del campo inicial de una lista
     *
     * @return array    Arreglo con las opciones a mostrar
     */
    function template_choices($model, $fields = 'name', $filters = [], $vuejs = false, $except_id = null)
    {
        $records = (is_object($model)) ? $model : $model::all();
        if ($filters) {
            if (!isset($filters['relationship'])) {
                if (isset($filters['whereIn'])) {
                    $arraySearch = $filters['whereIn']['list'];
                    $keySearch = $filters['whereIn']['key'];
                    $records = $model::whereIn($keySearch, $arraySearch)->get();
                } else {
                    $records = $model::where($filters)->get();
                }
            } else {
                // Filtra la información a obtener mediante relaciones
                $relationship = $filters['relationship'];
                $records = $model::whereHas($relationship, function ($q) use ($filters) {
                    $q->where($filters['where']);
                })->get();
            }
        }

        // Inicia la opción vacia por defecto
        $options = ($vuejs) ? [['id' => '', 'text' => 'Seleccione...']] : ['' => 'Seleccione...'];

        foreach ($records as $rec) {
            if (is_array($fields)) {
                $text = '';
                foreach ($fields as $field) {
                    $text .= ($field !== "-" && $field !== " ")
                        ? $rec->$field
                        : (($field === " ") ? $field : " {$field} ");
                }
            } else {
                $text = $rec->$fields;
            }

            if (is_null($except_id) || $except_id !== $rec->id) {
                /*
                 * Carga el listado según el tipo de plantilla en el cual se va a implementar
                 * (normal o con VueJS)
                 */
                ($vuejs)
                ? array_push($options, ['id' => $rec->id, 'text' => $text])
                : $options[$rec->id] = $text;
            }
        }
        return $options;
    }
}

if (!function_exists('validate_rif')) {
    /**
     * Verifica que el número de RIF sea correcto comparando el dígito verificador del mismo
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string $rif Cadena con el número de RIF completo
     *
     * @return boolean     Devuelve verdadero si el número de RIF es correcto, de lo contrario devuelve falso
     */
    function validate_rif($rif)
    {
        $rifCheck = preg_match('/^([VEJPG]{1})([0-9]{8})([0-9]{1})$/', strtoupper($rif), $output_array);

        // Si el número de RIF no es correcto retorna falso
        if (!$rifCheck || !$output_array) {
            return false;
        }

        // Caracter que identifica el tipo de RIF (V, E, J, P, G)
        $type = $output_array[1];
        // Número de RIF sin el tipo y el dígito verificador
        $number = $output_array[2];
        // Caracter que representa el digito verificador del RIF
        $digit = $output_array[3];

        // Arreglo con cada número que compone los datos del RIF, sin el tipo y el dígito verificador
        $validateNumber = str_split($number);
        $validateNumber[7] *= 2;
        $validateNumber[6] *= 3;
        $validateNumber[5] *= 4;
        $validateNumber[4] *= 5;
        $validateNumber[3] *= 6;
        $validateNumber[2] *= 7;
        $validateNumber[1] *= 2;
        $validateNumber[0] *= 3;

        // Determinar dígito especial según la inicial del RIF (Regla introducida por el SENIAT)
        switch ($type) {
            case 'V':
                $specialDigit = 1;
                break;
            case 'E':
                $specialDigit = 2;
                break;
            case 'J':
                $specialDigit = 3;
                break;
            case 'P':
                $specialDigit = 4;
                break;
            case 'G':
                $specialDigit = 5;
                break;
            default:
                $specialDigit = 0;
        }

        // Sumatoria de los números del RIF y el dígito especial de validación
        $sum = array_sum($validateNumber) + ($specialDigit * 4);
        // Residuo obtenido entre la sumatoria de los números del rif y el dígito especial de validación
        $residue = $sum % 11;
        // Resta obtenida del residuo
        $subtraction = 11 - $residue;
        // Dato que permite identificar si el dígito verificador es correcto. 0 = digito correcto, de lo contrario es incorrecto
        $verifyDigit = ($subtraction >= 10) ? 0 : $subtraction;

        if ($verifyDigit != $digit) {
            // Devuelve falso si el dígito verificador no es correcto
            return false;
        }

        // Retorna verdadero si el dígito verificador es correcto
        return true;
    }
}

if (!function_exists('rif_exists')) {
    /**
     * Comprueba que un número de RIF exista
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string $rif Cadena con el número de RIF completo
     *
     * @return boolean     Devuelve verdadero si el RIF existe, de lo contrario retorna falso
     */
    function rif_exists($rif)
    {
        // Comprueba si existe conexión externa
        $connectionExists = check_connection();
        $rifExists = false;
        // TODO: Agregar instrucciones que permita validar el número de rif ante la autoridad que los emite
        return ($connectionExists && $rifExists);
    }
}

if (!function_exists('validate_ci')) {
    /**
     * Verifica si un número de Cédula de Identidad es correcto
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string  $ci       Número de Cédula de Identidad
     * @param  boolean $with_nac Indica si la verificación del número de cédula es con la nacionalidad
     * @param  integer $length   Establece la longitud máxima del número de Cédula de Identidad
     *
     * @return boolean           Devuelve verdadero si el número de cédula es correcto, de lo contrario devuelve falso
     */
    function validate_ci($ci, $with_nac = false, $length = 8)
    {
        $ciCheck = preg_match(
            ($with_nac) ? "/^([VE]{1})([0-9]{$length})$/" : "/^([0-9]{$length})$/",
            strtoupper($ci),
            $output_array
        );

        if (!$ciCheck || !$output_array) {
            return false;
        }

        return true;
    }
}

if (!function_exists('ci_exists')) {
    /**
     * Comprueba la existencia de un número de cédula de identidad
     *
     * @param  string $ci  Número de cédula de identidad
     * @param  string $nac Indica la nacionalidad de la cédula a validar
     *
     * @return array       Devuelve un arreglo con los datos de comprobación de la cédula, si existe devuelve la
     *                     información de la persona
     */
    function ci_exists($ci, $nac = 'V')
    {
        // Comprueba si existe conexión externa
        $connectionExists = check_connection();
        $exists = false;
        $personData = [];

        if ($connectionExists) {
            $client = new GuzzleHttp\Client();
            // TODO: Modificar URL para obtener datos de la autoridad que emite la cedula
            $res = $client->request(
                'GET',
                'www.cne.gob.ve/web/registro_civil/buscar_rep.php?nac=' . $nac . '&ced=' . $ci
            );

            if ($res->getStatusCode() === 200) {
                preg_match('/<b[^>]*>[^<]*<\/b>/', $res->getBody(), $content);
                if (count($content) > 0) {
                    // La cédula existe en el organismo emisor
                    $oneName = strpos($content[0], '  ');
                    $oneLastName = strpos($content[0], ' </b>');
                    $filterContent = str_replace("  ", " ", trim($content[0]));
                    $filterContent = str_replace("<b>", "", trim($filterContent));
                    $filterContent = str_replace("</b>", "", trim($filterContent));
                    $data = explode(" ", $filterContent);
                    $personData = array_merge($personData, [
                        'firstName' => $data[0],
                        'secondName' => (!$oneName) ? $data[1] : '',
                        'firstLastName' => (!$oneName) ? $data[2] : $data[1],
                        'secondLastName' => (!$oneName)
                            ? ((!$oneLastName) ? $data[3] : $data[2])
                            : ((!$oneLastName) ? $data[2] : $data[1]),
                    ]);
                    $exists = true;
                }
            }
        }

        return compact('exists', 'connectionExists', 'personData');
    }
}

if (!function_exists('generate_code')) {
    /**
     * Genera una cadena aleatoria
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  object|string    $model  Clase del modelo a verificar
     * @param  string           $field  Nombre del campo a validar
     * @param  integer          $length Longitud máxima de la cadena a generar
     *
     * @return string           Devuelve una cadena aleatoria
     */
    function generate_code($model, $field, $length = 20)
    {
        $pool = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $code = substr(str_shuffle(str_repeat($pool, $length)), 0, $length);

        $generatedCode = ($model::where($field, $code)->first())
            ? $model::where($field, $code)->first()->$field : '';

        while ($generatedCode == $code) {
            $code = substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        }

        return $code;
    }
}

if (!function_exists('get_json_resource')) {
    /**
     * Obtiene el contenido de recursos json
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      string   $file   Nombre del archivo .json del cual se va a obtener su contenido
     *
     * @return     object|array   Objeto con elementos json
     */
    function get_json_resource($file, $module = null)
    {
        if (!is_null($module)) {
            return json_decode(
                file_get_contents(Module::getModulePath($module) . "/Resources/" . $file, true)
            );
        }

        return json_decode(
            file_get_contents(app()->resourcePath($file), true)
        );
    }
}

if (!function_exists('check_connection')) {
    /**
     * Determina si existe o no una conexión externa a Internet
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      string       $host    Dirección IP o URL del servidor al cual realizar la petición para identificar
     *                                   si existe la conexión
     * @param      integer      $port    Puerto de conexión al servidor
     *
     * @return     boolean               Devuelve verdadero si existe la conexión, de lo contrario retorna falso
     */
    function check_connection($host = 'www.google.com', $port = 80)
    {
        return (bool)@fsockopen($host, $port, $errno, $errstr, 4);
    }
}

if (!function_exists('get_institution')) {
    /**
     * Obtiene la información de una organización
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  int|null $id Identificador único de la organización
     *
     * @return \App\Models\Institution     Devuelve un objeto con información de la organización,
     *                                     si no se indica un ID devuelve el registro de la organización por defecto,
     *                                     de lo contrario devuelve los datos de la organización solicitada
     */
    function get_institution($id = null)
    {
        if ($id) {
            return App\Models\Institution::find($id);
        }
        return App\Models\Institution::where('default', true)->first()
               ?? App\Models\Institution::first();
    }
}

if (!function_exists('get_user_institution')) {
    /**
     * Obtiene la informacion de la organización del usuario autenticado
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \App\Models\Institution     Devuelve un objeto con información de la organización,
     *                                     si no se indica un ID devuelve el registro de la organización por defecto,
     *                                     de lo contrario devuelve los datos de la organización asociada al usuario
     */
    function get_user_institution($authUser)
    {
        $institution = Institution::query()->where([
            'active' => true, 'default' => true
        ])->first();
        if (isset($authUser->profile) && isset($authUser->profile->institution_id)) {
            $institution = Institution::query()->where([
                'id' => $authUser->profile->institution_id
            ])->first();
        }
        return $institution;
    }
}

if (!function_exists('generate_hash')) {
    /**
     * Genera una cadena aleatoria
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      integer          $length          Longitud de la cadena a generar
     * @param      boolean          $specialChars    Condición que determina si se incluyen o no carácteres especiales
     * @param      boolean          $separators      Condición que determina si se incluyen carácteres "-" y "_" como
     *                                               separadores de la cadena generada
     *
     * @return     string           Devuelve una cadena aleatoria
     */
    function generate_hash($length = 8, $specialChars = false, $separators = false)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

        $chars = '';

        if ($specialChars) {
            $chars = '%$[](-_)@/#{}';
            $alphabet .= $chars;
        }
        if ($separators) {
            $chars = '-_';
            $alphabet .= $chars;
        }
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        $i = 0;
        while ($i < $length) {
            $n = rand(0, $alphaLength);
            if (in_array($alphabet[$n], $pass) && !empty($chars) && strpos($chars, $alphabet[$n])) {
                continue;
            }
            $pass[] = $alphabet[$n];
            $i++;
        }

        $hash = implode($pass);
        return $hash;
    }
}

if (!function_exists('execution_year')) {
    /**
     * Obtiene el año de ejecución del ejercicio económico
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      string   $year   Cadena con el año del ejercicio económico
     *
     * @return     string           Devuelve el año del ejercicio económico
     */
    function execution_year($year)
    {
        return (strlen($year) === 4) ? $year : Crypt::decrypt($year);
    }
}

if (!function_exists('set_current_timestamp')) {
    /**
     * Establece la fecha actual con marca de tiempo
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    string        Devuelve una cadena de texto con la fecha y marca de tiempo actual
     */
    function set_current_timestamp()
    {
        return Carbon::now();
    }
}


if (!function_exists('list_table_foreign_keys')) {
    /**
     * Obtiene un listado de claves foráneas de una tabla
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     string                     $table    Nombre de la tabla de la cual obtener las claves foráneas
     *
     * @return    array                      Listado de claves foráneas encontradas en la tabla
     */
    function list_table_foreign_keys($table)
    {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();

        return array_map(function ($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));
    }
}

if (!function_exists('has_foreign_key')) {
    /**
     * Verifica si una tabla de la base de datos contiene una clave foránea específica
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     string     $table         Nombre de la tabla en base de datos
     * @param     string     $foreignKey    Nombre de la clave foránea a verificar
     *
     * @return    boolean    Devuelve verdadero si la clave foránea existe en la tabla, de lo contrario retorna falso
     */
    function has_foreign_key($table, $foreignKey)
    {
        // Objeto con información detallada de las propiedades de la tabla
        $detailTable = Schema::getConnection()->getDoctrineSchemaManager()->listTableDetails($table);
        return $detailTable->hasForeignKey($foreignKey);
    }
}

if (!function_exists('has_data_in_foreign_key')) {
    /**
     * Verifica si una tabla de la base de datos contiene información en una clave foránea específica
     *
     * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     string     $foreignKey    Nombre de la clave foránea a verificar
     * @param     string     $id            Id de la clave foránea a verificar
     *
     * @return    boolean    Devuelve verdadero si la columna tiene información, de lo contrario retorna falso
     */
    function has_data_in_foreign_key($id, $foreignKey)
    {
        // Objeto con información detallada de las propiedades de la tabla
        $table_names = Schema::getConnection()
                        ->getDoctrineSchemaManager()->listTableNames();

        foreach ($table_names as $table_name) {
            $table = Schema::getConnection()
                        ->getDoctrineSchemaManager()->listTableDetails($table_name);
            $hasForeignKey = $table->hasColumn($foreignKey);
            if ($hasForeignKey) {
                $count = DB::table($table_name)
                    ->whereNotNull($foreignKey)
                    ->where($foreignKey, $id)
                    ->count();

                if ($count > 0) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (!function_exists('has_index_key')) {
    /**
     * Verifica si una tabla de la base de datos contiene un índice específico
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     string     $table       Nombre de la tabla en base de datos
     * @param     string     $indexKey    Nombre del índice a verificar
     *
     * @return    boolean    Devuelve verdadero si el índice existe en la tabla, de lo contrario retorna falso
     */
    function has_index_key($table, $indexKey)
    {
        // Objeto con información detallada de las propiedades de la tabla
        $detailTable = Schema::getConnection()->getDoctrineSchemaManager()->listTableDetails($table);
        return $detailTable->hasIndex($indexKey);
    }
}

if (!function_exists('get_database_info')) {
    /**
     * Obtiene información de la base de datos
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    object               Devuelve un arreglo con información de la base de datos
     */
    function get_database_info()
    {
        if (env('DB_CONNECTION') === 'sqlite') {
            // Conexión a base de datos SQLite
            $conn = new PDO('sqlite:' . env('DB_DATABASE'));
        } else {
            // Conexión a base de datos PostgreSQL o MySQL
            $conn = new PDO(
                env('DB_CONNECTION') . ':host=' . env('DB_HOST') .
                ';port=' . env('DB_PORT') . ';dbname=' . env('DB_DATABASE'),
                env('DB_USERNAME'),
                env('DB_PASSWORD')
            );
        }
        // Versión de la base de datos
        $version = $conn->getAttribute(PDO::ATTR_SERVER_VERSION);
        if (env('DB_CONNECTION') === 'pgsql') {
            $version = substr($version, 0, strpos($version, ' '));
        } elseif (env('DB_CONNECTION') === 'mysql') {
            $version = substr($version, 0, strpos($version, '-'));
        }

        switch (env('DB_CONNECTION')) {
            case 'pgsql':
                $databaseType = 'PostgreSQL';
                break;
            case 'mysql':
                $databaseType = 'MySQL';
                break;
            case 'sqlite':
                $databaseType = 'SQLite';
                break;
            default:
                $databaseType = env('DB_CONNECTION');
                break;
        }


        return (object)[
            'database_type' => $databaseType,
            'version' => $version
        ];
    }
}

if (!function_exists('strpos_array')) {
    /**
     * Verifica si los datos en un arraglo se encuentran en una cadena de texto
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     string          $haystack    Texto de la cadena en donde buscar
     * @param     string|array    $needle      Texto o arreglo de palabras a buscar
     *
     * @return    boolean         Devuelve verdadero si el texto a buscar es encontrado, de lo contrario devuelve falso
     */
    function strpos_array($haystack, $needle)
    {
        if (!is_array($needle)) {
            $needle = [$needle];
        }
        foreach ($needle as $what) {
            if (strpos(strtoupper($haystack), strtoupper($what)) !== false) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('secure_record')) {
    /**
     * Cifra y descifra registros
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     string|integer   $record     Cadena de texto a ser cifrada / descifrada
     * @param     boolean          $decrypt    Indica si el registro va a ser descifrado
     *
     * @return    string|integer   Devuelve el registro cifrado / descifrado
     */
    function secure_record($record, $decrypt = false)
    {
        return ($decrypt) ? Crypt::decrypt($record) : Crypt::encrypt($record);
    }
}

if (!function_exists('age')) {
    /**
     * Calcula la edad de una persona en años
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     string    $birthdate    Fecha de nacimiento
     *
     * @return    integer   Devuelve la edad representada en años
     */
    function age($birthdate, $current = null, $withDecimal = false)
    {
        $date = new DateTime($birthdate);

        $now = ($current)
            ? new DateTime($current)
            : new DateTime();

        $difference = $now->diff($date);

        $age = $difference->y;

        if ($withDecimal) {
            // Calcula la fracción de año representada por los meses y días
            $age += $difference->m / 12;
            $age += $difference->d / 365;

            // Redondea la edad a dos decimales
            $age = round($age, 2);
        }

        return  $age;
    }
}

if (!function_exists('convert_filesize')) {
    /**
     * Convierte un tamaño expresado en bytes a Bytes, KiloBytes, MegaBytes, etc...
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     float              $bytes       Tamaño expresado en bytes
     * @param     boolean            $showSize    Define si se muestra o no la unidad que expresa el tamaño
     * @param     integer            $decimals    Define la cantidad de decimales a retornar
     *
     * @return    float|string       Devuelve el tamaño expresado en Bytes, KiloBytes, MegaBytes, etc...
     */
    function convert_filesize($bytes, $showSize = true, $decimals = 2)
    {
        $size = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        $humanSize = sprintf("%.{$decimals}f", $bytes / pow(1024, $factor));
        if (!$showSize) {
            return (float)$humanSize;
        }

        return $humanSize . $size[$factor];
    }
}

if (!function_exists('convert_to_bytes')) {
    /**
     * Convierte un tamaño de archivo expresado en Bytes, KiloBytes, MegaBytes, etc.., a bytes
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     string              $from    Tamaño a ser convertido en bytes
     *
     * @return    integer|null        Devuelve el número expresado en bytes, de lo contrario retorna null
     */
    function convert_to_bytes(string $from): ?int
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $number = substr($from, 0, -2);
        $suffix = strtoupper(substr($from, -2));

        if (is_numeric(substr($suffix, 0, 1))) {
            return preg_replace('/[^\d]/', '', $from);
        }

        $exponent = array_flip($units)[$suffix] ?? null;
        if ($exponent === null) {
            return null;
        }

        return $number * (1024 ** $exponent);
    }
}

if (!function_exists('check_max_upload_size')) {
    /**
     * Verifica si el archivo a subir esta dentro de los parámetros establecidos en la configuración de PHP
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     string    $file    Ruta del archivo a ser evaluado
     *
     * @return    boolean   Devuelve verdadero si el tamaño del archivo a subir está dentro del parámetro
     *                      de configuración
     */
    function check_max_upload_size($file)
    {
        // Tamaño en bytes del archivo a verificar
        $fileSize = filesize($file);
        // Tamaño del archivo con el factor en Bytes, kilo Bytes, Mega Bytes, etc...
        $humanFileSize = convert_filesize($fileSize);
        preg_match_all('/\d+/', ini_get('upload_max_filesize'), $uploadMaxFilesize);
        preg_match_all('/[^0-9]/', ini_get('upload_max_filesize'), $factor);
        preg_match_all('/\d+/', ini_get('post_max_size'), $postMaxSize);
        preg_match_all('/[^0-9]/', ini_get('post_max_size'), $factorPost);
        // Tamaño máximo permitido para subir
        $maxSize = (float)$uploadMaxFilesize[0][0];
        // Tamaño máximo permitido por el método POST
        $maxSizePost = (float)$postMaxSize[0][0];

        if ($maxSize === 0 && $maxSizePost === 0) {
            // No existe límite máximo para subir archivos
            return true;
        }
        // Unidad que establece el tamaño máximo
        $unit = $factor[0][0] . 'B';
        // Unidad que establece el tamaño máximo a través del método POST
        $unitPost = $factorPost[0][0] . 'B';

        // Tamaño máximo permitido para subir archivos
        $size = (string)$maxSize . $unit;
        // Tamaño máximo permitido a través del método POST
        $sizePost = (string)$maxSizePost . $unitPost;

        // Conversión a bytes del tamaño máximo permitido para subir archivos
        $bytes = convert_to_bytes($size);
        // Conversión a bytes del tamaño máximo a través del método POST
        $bytesPost = convert_to_bytes($sizePost);

        return $fileSize < $bytes && $fileSize < $bytesPost;
    }
}

if (!function_exists('restore_record')) {
    /**
     * Restaura registros eliminados del sistema
     *
     * @method    restore_record
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     string    $model     Nombre del modelo que contiene el registro a restaurar
     * @param     array     $filter    Arreglo con el filtro a aplicar para obtener el(los) registro(s) a restaurar
     *
     * @return    boolean   Devuelver verdadero si existe el registro y fue restaurado, de lo contrario devuelve falso
     */
    function restore_record($model, $filter)
    {
        if ($record = $model::onlyTrashed()->where($filter)->first()) {
            $record->restore();
            return true;
        }

        return false;
    }
}

if (!function_exists('info_modules')) {
    /**
     * Obtiene información de los módulos de la aplicación
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @param  boolean $min Mínima versión a buscar. Opcional
     * @param  string  $mod Nombre del módulo del cual se va a mostrar información. Opcional
     *
     * @return array|null
     */
    function info_modules($min = false, $mod = null)
    {
        // Objeto con información de todos los módulos de la aplicación
        $modules = Module::all();

        // Arreglo con información detallada de los módulos de la aplicación
        $listModules = [];
        foreach ($modules as $module) {
            if ($mod !== null && $module->getLowerName() !== trim(strtolower($mod))) {
                continue;
            }
            // Arreglo con los requerimientos del módulo
            $requirements = [];
            if (count($module->getRequires()) > 0) {
                foreach ($module->getRequires() as $modName => $version) {
                    array_push($requirements, ['module' => $modName, 'versión' => $version]);
                }
            }

            // Arreglo con información de los autores del módulo
            $authors = [];
            if (!is_null($module->get('authors')) && count($module->get('authors')) > 0) {
                foreach ($module->get('authors') as $author) {
                    array_push($authors, ['name' => $author['name'], 'emails' => $author['email']]);
                }
            }

            $moduleDetails = [
                'originalName' => $module->getName(),
                'alias' => $module->get('alias'),
                'name' => $module->get('name_es') ?? $module->getName(),
                'installed' => $module->isEnabled(),
                'disabled' => $module->isDisabled(),
                'withSetting' => $module->get('setting') ?? false
            ];

            if (!$min) {
                array_push($moduleDetails, [
                    'icon' => $module->icon["name"] ?? "fa fa-cubes",
                    'logo' => ($module->get('logo'))
                        ? "assets/" . $module->get('name') . "/images/" . $module->get('logo')
                        : "images/default-avatar.png",
                    'description' => $module->getDescription(),
                    'requirements' => $requirements,
                    'authors' => $authors
                ]);
            }

            array_push($listModules, $moduleDetails);
        }

        return $listModules;
    }
}


if (!function_exists('listMonths')) {
    /**
     * Listado de meses
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     boolean    $reverse    Determina si el listado a retornar es por número de mes o por nombre
     *
     * @return    array     Arreglo con un listado de meses
     */
    function listMonths($reverse = false)
    {
        if ($reverse) {
            return [
                1 => 'jan', 2 => 'feb', 3 => 'mar',  4 => 'apr',  5 => 'may',  6 => 'jun',
                7 => 'jul', 8 => 'aug', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dec'
            ];
        }

        return [
            'jan' => 1, 'feb' => 2, 'mar' => 3,  'apr' => 4,  'may' => 5,  'jun' => 6,
            'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12
        ];
    }
}

if (! function_exists('months_dictionary')) {
    /**
     * Diccionario de nombres de los meses del año
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg at gmail.com>
     *
     * @param boolean $short Determina si se retornan los nombres abreviados de los meses,
     *                       si se establece en true devuelve los nombres abreviados,
     *                       de lo contrario devuelve los nombres completos
     *
     * @return array Arreglo con el listado de los nombres de los meses
     */
    function months_dictionary($short = false)
    {
        $months = [
            'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo', 'April' => 'Abril', 'May' => 'Mayo',
            'June' => 'Junio', 'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
            'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre',
        ];

        if ($short) {
            $months = [
                'jan' => 'Ene', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Abr', 'may' => 'May', 'jun' => 'Jun',
                'jul' => 'Jul', 'aug' => 'Ago', 'sep' => 'Sep', 'oct' => 'Oct', 'nov' => 'Nov', 'dec' => 'Dic'
            ];
        }
        return $months;
    }
}

if (! function_exists('isModuleEnabled')) {
    /**
     * Estado de un módulo
     *
     * @author     Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @param     string    $moduleName    Nombre del modulo
     *
     * @return    boolean   Devuelve verdadero si el módulo esta activo, de lo contrario devuelve falso
     */
    function isModuleEnabled($moduleName)
    {
        return Module::collections()->has($moduleName);
    }
}

if (! function_exists('restoreSoftDeletedRelatedModels')) {
    /**
     * Restaura todos los registros eliminados que poseen SoftDeletes para el modelo dado y
     * todas sus relaciones internas.
     *
     * @author    Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
     *
     * @param Illuminate\Database\Eloquent\Model|string $modelName Nombre del modelo a restaurar
     * @param int $id Identificador del registro a restaurar
     *
     * @return void
     */
    function restoreSoftDeletedRelatedModels($modelName, $id)
    {
        // Obtener el modelo dado su nombre
        $model = (new $modelName())->withTrashed()->findOrFail($id);

        if (!$model) {
            return; //retorna vacío sí el modelo no es encontrado
        }

        // Restaurar el modelo dado
        $model->restore();

        // Buscar todas las relaciones del modelo
        $relationships = $model->getRelations();

        // Sí el modelo no posee relaciones retorna vacío
        if (!$relationships) {
            return;
        }
        // Cargar todas las relaciones del modelo eliminadas temporalmente
        foreach ($relationships as $relationName => $relation) {
            $model->load([$relationName => function ($query) {
                $query->withTrashed();
            }]);
        }
        // Buscar de nuevo todas las relaciones del modelo
        $relationships = $model->getRelations();
        // Sí el modelo no posee relaciones retorna vacío
        if (!$relationships) {
            return;
        }
        // Se recorren todas las relaciones de los registros a restaurar
        foreach ($relationships as $relationshipName => $relationship) {
            // Verificar si la relación no es nula
            if ($relationship) {
                // Sí la refelación es payrollFinancial, se guarda la primera posicion del array
                $relation = ($relationshipName == 'payrollFinancial') ? $relationship[0] : $relationship;
                // Verificar si el modelo relacionado utiliza SoftDeletes
                if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($relation))) {
                    // Restaurar los registros eliminados tempóralmete para el modelo relacionado
                    // Si hay más relaciones en el modelo relacionado, busca y restaura todos
                    // los registros eliminados temporalmente en ellas de manera recursiva
                    restoreSoftDeletedRelatedModels(get_class($relation), $relation->id);
                }
            }
        }
    }
}

// =================== Números a letras ===================
if (! function_exists('unidad')) {
    /**
     * Obtiene el texto de la unidad del número
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $unitNumber Unidad del número
     *
     * @return string Devuelve el texto correspondiente a la unidad de un número
     */
    function unidad($unitNumber)
    {
        $unitText = "";
        switch (floor($unitNumber)) {
            case 9:
                $unitText = __("NUEVE");
                break;
            case 8:
                $unitText = __("OCHO");
                break;
            case 7:
                $unitText = __("SIETE");
                break;
            case 6:
                $unitText = __("SEIS");
                break;
            case 5:
                $unitText = __("CINCO");
                break;
            case 4:
                $unitText = __("CUATRO");
                break;
            case 3:
                $unitText = __("TRES");
                break;
            case 2:
                $unitText = __("DOS");
                break;
            case 1:
                $unitText = __("UN");
                break;
            case 0:
                $unitText = "";
                break;
        }
        return $unitText;
    }
}

if (! function_exists('decena')) {
    /**
     * Obtiene el texto de la decena del número
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $numdero Decena del número
     *
     * @return string Devuelve el texto correspondiente a la decena de un número
     */
    function decena($numdero)
    {
        $numd = "";
        $numdero = floor($numdero);
        if ($numdero >= 90 && $numdero <= 99) {
            $numd = __("NOVENTA ");
            if ($numdero > 90) {
                $numd = $numd . __("Y ") . (unidad($numdero - 90));
            }
        } elseif ($numdero >= 80 && $numdero <= 89) {
            $numd = __("OCHENTA ");
            if ($numdero > 80) {
                $numd = $numd . __("Y ") . (unidad($numdero - 80));
            }
        } elseif ($numdero >= 70 && $numdero <= 79) {
            $numd = __("SETENTA ");
            if ($numdero > 70) {
                $numd = $numd . __("Y ") . (unidad($numdero - 70));
            }
        } elseif ($numdero >= 60 && $numdero <= 69) {
            $numd = __("SESENTA ");
            if ($numdero > 60) {
                $numd = $numd . __("Y ") . (unidad($numdero - 60));
            }
        } elseif ($numdero >= 50 && $numdero <= 59) {
            $numd = __("CINCUENTA ");
            if ($numdero > 50) {
                $numd = $numd . __("Y ") . (unidad($numdero - 50));
            }
        } elseif ($numdero >= 40 && $numdero <= 49) {
            $numd = __("CUARENTA ");
            if ($numdero > 40) {
                $numd = $numd . __("Y ") . (unidad($numdero - 40));
            }
        } elseif ($numdero >= 30 && $numdero <= 39) {
            $numd = __("TREINTA ");
            if ($numdero > 30) {
                $numd = $numd . __("Y ") . (unidad($numdero - 30));
            }
        } elseif ($numdero >= 20 && $numdero <= 29) {
            if ($numdero == 20) {
                $numd = __("VEINTE ");
            } else {
                $numd = __("VEINTI") . (unidad($numdero - 20));
            }
        } elseif ($numdero >= 10 && $numdero <= 19) {
            switch ($numdero) {
                case 10:
                    $numd = __("DIEZ ");
                    break;
                case 11:
                    $numd = __("ONCE ");
                    break;
                case 12:
                    $numd = __("DOCE ");
                    break;
                case 13:
                    $numd = __("TRECE ");
                    break;
                case 14:
                    $numd = _("CATORCE ");
                    break;
                case 15:
                    $numd = __("QUINCE ");
                    break;
                case 16:
                    $numd = __("DIECISEIS ");
                    break;
                case 17:
                    $numd = __("DIECISIETE ");
                    break;
                case 18:
                    $numd = __("DIECIOCHO ");
                    break;
                case 19:
                    $numd = __("DIECINUEVE ");
                    break;
            }
        } else {
            $numd = unidad($numdero);
        }

        return $numd;
    }
}

if (! function_exists('centena')) {
    /**
     * Obtiene el texto de la centena del número
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $numc Centena del número
     *
     * @return string Devuelve el texto correspondiente a la centena de un número
     */
    function centena($numc)
    {
        $numce = "";
        $numc = floor($numc);
        if ($numc >= 100) {
            if ($numc >= 900 && $numc <= 999) {
                $numce = __("NOVECIENTOS ");
                if ($numc > 900) {
                    $numce = $numce . (decena($numc - 900));
                }
            } elseif ($numc >= 800 && $numc <= 899) {
                $numce = __("OCHOCIENTOS ");
                if ($numc > 800) {
                    $numce = $numce . (decena($numc - 800));
                }
            } elseif ($numc >= 700 && $numc <= 799) {
                $numce = __("SETECIENTOS ");
                if ($numc > 700) {
                    $numce = $numce . (decena($numc - 700));
                }
            } elseif ($numc >= 600 && $numc <= 699) {
                $numce = __("SEISCIENTOS ");
                if ($numc > 600) {
                    $numce = $numce . (decena($numc - 600));
                }
            } elseif ($numc >= 500 && $numc <= 599) {
                $numce = __("QUINIENTOS ");
                if ($numc > 500) {
                    $numce = $numce . (decena($numc - 500));
                }
            } elseif ($numc >= 400 && $numc <= 499) {
                $numce = __("CUATROCIENTOS ");
                if ($numc > 400) {
                    $numce = $numce . (decena($numc - 400));
                }
            } elseif ($numc >= 300 && $numc <= 399) {
                $numce = __("TRESCIENTOS ");
                if ($numc > 300) {
                    $numce = $numce . (decena($numc - 300));
                }
            } elseif ($numc >= 200 && $numc <= 299) {
                $numce = __("DOSCIENTOS ");
                if ($numc > 200) {
                    $numce = $numce . (decena($numc - 200));
                }
            } elseif ($numc >= 100 && $numc <= 199) {
                if ($numc == 100) {
                    $numce = __("CIEN ");
                } else {
                    $numce = __("CIENTO ") . (decena($numc - 100));
                }
            }
        } else {
            $numce = decena($numc);
        }

        return $numce;
    }
}

if (! function_exists('miles')) {
    /**
     * Obtiene el texto del número en miles
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $nummero Unidad de mil del número
     *
     * @return string Devuelve el texto correspondiente al número en miles
     */
    function miles($nummero)
    {
        $numm = "";
        $nummero = floor($nummero);
        if ($nummero >= 1000 && $nummero < 2000) {
            $numm = __("MIL ") . (centena($nummero % 1000));
        }
        if ($nummero >= 2000 && $nummero < 10000) {
            $numm = unidad(Floor($nummero / 1000)) . __(" MIL ") . (centena($nummero % 1000));
        }
        if ($nummero < 1000) {
            $numm = centena($nummero);
        }

        return $numm;
    }
}

if (! function_exists('decmiles')) {
    /**
     * Obtiene el texto del número en decena de miles
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $numdmero Decena de mil del número
     *
     * @return string Devuelve el texto correspondiente al número en decena de miles
     */
    function decmiles($numdmero)
    {
        $numde = "";
        if ($numdmero == 10000) {
            $numde = __("DIEZ MIL");
        }
        if ($numdmero > 10000 && $numdmero < 20000) {
            $numde = decena(Floor($numdmero / 1000)) . __("MIL ") . (centena($numdmero % 1000));
        }
        if ($numdmero >= 20000 && $numdmero < 100000) {
            $numde = decena(Floor($numdmero / 1000)) . __(" MIL ") . (miles($numdmero % 1000));
        }
        if ($numdmero < 10000) {
            $numde = miles($numdmero);
        }

        return $numde;
    }
}

if (! function_exists('cienmiles')) {
    /**
     * Obtiene el texto del número en cien de miles
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $numcmero Cien de mil del número
     *
     * @return string Devuelve el texto correspondiente al número en cien de miles
     */
    function cienmiles($numcmero)
    {
        $num_letracm = "";
        if ($numcmero == 100000) {
            $num_letracm = __("CIEN MIL");
        }
        if ($numcmero >= 100000 && $numcmero < 1000000) {
            $num_letracm = centena(Floor($numcmero / 1000)) . __(" MIL ") . (centena((int)$numcmero % 1000));
        }
        if ($numcmero < 100000) {
            $num_letracm = decmiles($numcmero);
        }

        return $num_letracm;
    }
}

if (! function_exists('millon')) {
    /**
     * Obtiene el texto del número en millon
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $nummiero Millon del número
     *
     * @return string Devuelve el texto correspondiente al número en millon
     */
    function millon($nummiero)
    {
        $num_letramm = "";
        if (((int)($nummiero) % 1000000) == 0) {
            $deletras = __('DE ');
        } else {
            $deletras = '';
        }
        if ($nummiero >= 1000000 && $nummiero < 2000000) {
            $num_letramm = __("UN MILLON ") . $deletras . (cienmiles($nummiero % 1000000));
        }
        if ($nummiero >= 2000000 && $nummiero < 10000000) {
            $num_letramm = unidad(Floor($nummiero / 1000000)) . __(" MILLONES ") . $deletras . (cienmiles($nummiero % 1000000));
        }
        if ($nummiero < 1000000) {
            $num_letramm = cienmiles($nummiero);
        }

        return $num_letramm;
    }
}

if (! function_exists(('decmillon'))) {
    /**
     * Obtiene el texto del número en decena de millon
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $numerodm Decena de millon del número
     *
     * @return string Devuelve el texto correspondiente al número en decena de millon
     */
    function decmillon($numerodm)
    {
        $numerodm = (int) $numerodm;
        $num_letradmm = "";
        if (abs(Floor($numerodm / 1000000)) == 0) {
            $deletras = __('DE ');
        } else {
            $deletras = '';
        }
        if ($numerodm == 10000000) {
            $num_letradmm = __("DIEZ MILLONES") . $deletras;
        }
        if ($numerodm > 10000000 && $numerodm < 20000000) {
            $num_letradmm = decena(Floor($numerodm / 1000000)) . __("MILLONES ") . $deletras . (cienmiles($numerodm % 1000000));
        }
        if ($numerodm >= 20000000 && $numerodm < 100000000) {
            $num_letradmm = decena(Floor($numerodm / 1000000)) . __(" MILLONES ") . $deletras . (millon($numerodm % 1000000));
        }
        if ($numerodm < 10000000) {
            $num_letradmm = millon($numerodm);
        }

        return $num_letradmm;
    }
}

if (! function_exists('cienmillon')) {
    /**
     * Obtiene el texto del número en cien de millon
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $numcmeros Cien de millon del número
     *
     * @return string Devuelve el texto correspondiente al número en cien de millon
     */
    function cienmillon($numcmeros)
    {
        $num_letracms = "";
        if (abs(Floor($numcmeros / 1000000)) == 0) {
            $deletras = __('DE ');
        } else {
            $deletras = '';
        }
        if ($numcmeros == 100000000) {
            $num_letracms = __("CIEN MILLONES") . $deletras;
        }
        if ($numcmeros >= 100000000 && $numcmeros < 1000000000) {
            $num_letracms = centena(Floor($numcmeros / 1000000)) . __(" MILLONES ") . $deletras . (millon($numcmeros % 1000000));
        }
        if ($numcmeros < 100000000) {
            $num_letracms = decmillon($numcmeros);
        }

        return $num_letracms;
    }
}

if (! function_exists('milmillon')) {
    /**
     * Obtiene el texto del número en mil de millon
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $nummierod Mil de millon del número
     *
     * @return string Devuelve el texto correspondiente al número en mil de millon
     */
    function milmillon($nummierod)
    {
        $nummierod = (int) $nummierod;
        $num_letrammd = "";
        if ($nummierod >= 1000000000 && $nummierod < 2000000000) {
            $num_letrammd = __("MIL ") . (cienmillon($nummierod % 1000000000));
        }
        if ($nummierod >= 2000000000 && $nummierod < 10000000000) {
            $num_letrammd = unidad(Floor($nummierod / 1000000000)) . __(" MIL ") . (cienmillon($nummierod % 1000000000));
        }
        if ($nummierod < 1000000000) {
            $num_letrammd = cienmillon($nummierod);
        }

        return $num_letrammd;
    }
}

if (! function_exists('convertirNumeros')) {
    /**
     * Convierte un número a su representación en texto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  float $numero Número a convertir
     * @param  string $moneda Moneda a representar
     *
     * @return string Devuelve el número en texto
     */
    function convertirNumeros($numero, $moneda = "BOLIVARES")
    {
        $moneda = __($moneda);
        $with = __("CON");
        $numf = milmillon($numero);
        $explodeNumber = explode(".", $numero);
        $decimals = $explodeNumber[1] ?? "00";
        if (strlen($decimals) == 1) {
            $decimals .= "0";
        }
        if (empty($numf)) {
            return __("Solo se puede convertir números menores a 10 mil millones");
        }
        $numberToText = $numf . " $moneda $with $decimals/100";
        return preg_replace('/\s+/', ' ', $numberToText);
    }
}

if (! function_exists('currency_format')) {
    /**
     * Convierte un número a formato de moneda
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string  $number          Número a mostrar en formato de moneda
     * @param  integer $decimalPlaces   Cantidad de decimales
     * @param  boolean $withDecimal     Muestra o no los decimales
     *
     * @return string Devuelve el número en formato de moneda
     */
    function currency_format(string $number, $decimalPlaces = 2, $withDecimal = false)
    {

        if (!strpos($number, ".")) {
            return $withDecimal ? "{$number}.00" : $number;
        }
        list($num, $dec) = explode('.', $number);
        $newDec = substr($dec, 0, $decimalPlaces);

        return "{$num}.{$newDec}";
    }
}

if (! function_exists('get_default_currency')) {
    /**
     * Obtiene la moneda por defecto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Currency Devuelve información de la moneda por defecto
     */
    function get_default_currency()
    {
        $currency = Currency::query()->where('default', true)->first();
        return $currency;
    }
}

if (! function_exists('default_document_status')) {
    /**
     * Obtiene o Crea un nuevo estado de documento por defecto para el estado PR = En Proceso
     *
     * @author Ing. Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
     *
     * @return DocumentStatus Devuelve información del estatus del documento
     */
    function default_document_status()
    {
        $data = [
            'name' => 'En Proceso',
            'description' => 'Contiene algunas firmas pero no todas las requeridas para su ' .
                             'aprobación',
            'color' => '#2CA8FF',
            'action' => 'PR'
        ];
        $documentStatus = DocumentStatus::firstOrCreate(['action' => 'PR'], $data);

        return $documentStatus;
    }
}

if (! function_exists('default_document_status_el')) {
    /**
     * Obtiene o Crea un nuevo estado de documento por defecto para el estado EL = Elaborado(a)
     *
     * @author Ing. Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
     *
     * @return DocumentStatus Devuelve información del estatus del documento
     */
    function default_document_status_el()
    {
        $data = [
            'name' => 'Elaborado(a)',
            'description' => 'Faltan todas las firmas',
            'color' => '#888',
            'action' => 'EL'
        ];
        $documentStatus = DocumentStatus::firstOrCreate(['action' => 'EL'], $data);

        return $documentStatus;
    }
}
