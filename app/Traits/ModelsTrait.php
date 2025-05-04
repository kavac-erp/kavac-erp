<?php

namespace App\Traits;

use DateTime;
use App\Models\City;
use App\Models\FiscalYear;
use App\Models\Institution;
use App\Scopes\OrganismScope;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\ClosedFiscalYearException;
use App\Exceptions\RestrictedRegistryDeletionException;

/**
 * @trait   Trait para la gestión de modelos
 * @brief Trait para la gestión de modelos
 *
 * Trait para la gestión de modelos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
trait ModelsTrait
{
    /**
     * Método que ejecuta eventos del modelo
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($model) {
            $modelClass = get_class($model);
            $deletedRecords = $modelClass::where('id', $model->id)->onlyTrashed()->orderBy('deleted_at', 'desc')->get();

            if (Cache::has('deleted_records')) {
                $deleted = Cache::get('deleted_records');
                $deletedRecords = $deleted->merge($deletedRecords);
                Cache::put('deleted_records', $deletedRecords);
            } else {
                Cache::rememberForever('deleted_records', function () use ($deletedRecords) {
                    return $deletedRecords;
                });
            }
        });

        static::restored(function ($model) {
            $modelClass = get_class($model);
            $restored = Cache::get('deleted_records');
            $restored = $restored->filter(function ($res) use ($model, $modelClass) {
                return ($modelClass === get_class($res) && $res->id !== $model->id) || $modelClass !== get_class($res);
            });
            Cache::put('deleted_records', $restored);
        });

        static::saving(function ($model) {
            $isApproveUrl = str_contains(request()->url(), 'close-fiscal-year/registers/approve/');
            if (method_exists($model, 'getDate') && !$isApproveUrl && $model->getDate() !== null) {
                $date = new DateTime($model->getDate());
                $formatedDate = $date->format('Y');

                if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
                    $institution = Institution::query()
                        ->where(['id' => auth()->user()->profile->institution_id])
                        ->first();
                } else {
                    $institution = Institution::query()
                        ->where(['active' => true, 'default' => true])
                        ->first();
                }

                $currentFiscalYear = FiscalYear::query()
                    ->where(['active' => true, 'closed' => false, 'institution_id' => $institution->id])
                    ->orderBy('year', 'desc')
                    ->first();

                if (isset($currentFiscalYear->entries)) {
                    return throw new ClosedFiscalYearException(
                        __('No puede registrar, actualizar o eliminar ' .
                        'registros debido a que se está realizando el cierre de año fiscal')
                    );
                }

                if ($formatedDate != $currentFiscalYear->year) {
                    return throw new ClosedFiscalYearException(
                        __('Solo puede registrar, actualizar o eliminar registros del año fiscal en curso')
                    );
                }
            }
        });

        static::deleting(function ($model) {
            // Obtener el nombre de la clase del modelo que se está eliminando.
            $class_name = get_class($model);

            if (Module::has('Budget') && Module::isEnabled('Budget')) {
                // Caso de la eliminación de Proyectos o AC relacionados a AE.
                if ($class_name === 'Modules\Budget\Models\BudgetProject') {
                    $projectType = 'Modules\Budget\Models\BudgetProject';
                    $projectID = $model->id;

                    $relatedActions = \Modules\Budget\Models\BudgetSpecificAction::where(
                        'specificable_type',
                        $projectType
                    )
                        ->where('specificable_id', $projectID)
                        ->get();

                    if ($relatedActions->isNotEmpty()) {
                        throw new RestrictedRegistryDeletionException(
                            __(
                                "No es posible eliminar este registro, ya que " .
                                "está vinculado a otro registro del sistema"
                            )
                        );
                    }
                } elseif ($class_name === 'Modules\Budget\Models\BudgetCentralizedAction') {
                    $projectType = 'Modules\Budget\Models\BudgetCentralizedAction';
                    $projectID = $model->id;
                    $relatedActions = \Modules\Budget\Models\BudgetSpecificAction::where(
                        'specificable_type',
                        $projectType
                    )
                        ->where('specificable_id', $projectID)
                        ->get();

                    if ($relatedActions->isNotEmpty()) {
                        throw new RestrictedRegistryDeletionException(
                            __(
                                "No es posible eliminar este registro, ya que " .
                                "está vinculado a otro registro del sistema"
                            )
                        );
                    }
                }
            }

            // Dividir el nombre de la clase en segmentos utilizando '\'.
            $segments = explode('\\', $class_name);

            // Obtener el último segmento (el nombre de la clase en sí).
            $class_name = end($segments);

            /* Aplicar una transformación personalizada del nombre de la clase
            en el nombre de un campo y se le agrega "_id" al final del nombre
            transformado */
            $dynamicFieldName = strtolower(
                preg_replace('/(?<!^)([A-Z])/', '_$1', $class_name)
            ) . '_id';

            // Caso de tabla intermedia entre profesionales e idiomas.
            if ($dynamicFieldName === 'payroll_language_id') {
                $dynamicFieldName = 'payroll_lang_id';
            }

            // Caso de la eliminación de tipos de becas en Talento Humano.
            if ($dynamicFieldName === 'payroll_scholarship_type_id') {
                $dynamicFieldName = 'payroll_scholarship_types_id';
            }

            // Caso de la eliminación de Tipos financiamiento de Presupuesto.
            if ($dynamicFieldName === 'budget_financement_types_id') {
                $dynamicFieldName = 'budget_financement_type_id';
            }

            // Caso de la eliminación de fuentes de financiamiento de Presupuesto.
            if ($dynamicFieldName === 'budget_financement_sources_id') {
                $dynamicFieldName = 'budget_financement_source_id';
            }

            // Obtener el id del modelo que se está eliminando.
            $modelId = $model->id;

            // Obtener los modelos registrados en la app.
            $x = new City();
            $getModels = $x->getModels();

            // Inicializa una variable para rastrear si se encontró al menos una coincidencia.
            $foundMatchingField = false;

            // Iterar sobre los modelos en $getModels
            foreach ($getModels as $className) {
                // Crear una instancia del modelo actual
                $modelInstance = new $className();

                // Obtener la lista de campos fillable del modelo actual
                $fillableFields = $modelInstance->getFillable();

                // Inicializar una variable para almacenar el nombre del campo
                // fillable correspondiente.
                $matchingField = null;

                // Iterar sobre los campos fillable y buscar un campo que
                // contenga el fragmento dinámico.
                foreach ($fillableFields as $fillableField) {
                    if (strpos($fillableField, $dynamicFieldName) !== false) {
                        $matchingField = $fillableField;
                        $foundMatchingField = true;
                        // No rompemos el bucle aquí para buscar todas las coincidencias
                    }
                }

                // Si se encontró un campo fillable que coincide, realiza la búsqueda y la verificación
                if ($matchingField) {
                    $existingModel = $className::where($matchingField, $modelId)->first();
                    if ($existingModel) {
                        return throw new RestrictedRegistryDeletionException(
                            __(
                                "No es posible eliminar este registro, ya que " .
                                "está vinculado a otro registro del sistema"
                            )
                        );
                    }
                }
            }

            if (method_exists($model, 'getDate')) {
                $date = new DateTime($model->getDate());
                $formatedDate = $date->format('Y');

                if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
                    $institution = Institution::query()
                        ->where(['id' => auth()->user()->profile->institution_id])
                        ->first();
                } else {
                    $institution = Institution::query()
                        ->where(['active' => true, 'default' => true])
                        ->first();
                }

                $currentFiscalYear = FiscalYear::query()
                    ->where(['active' => true, 'closed' => false, 'institution_id' => $institution->id])
                    ->orderBy('year', 'desc')
                    ->first();

                if (isset($currentFiscalYear->entries)) {
                    return throw new ClosedFiscalYearException(
                        __('No puede registrar, actualizar o eliminar ' .
                            'registros debido a que se está realizando el cierre de año fiscal')
                    );
                }

                if ($formatedDate != $currentFiscalYear->year) {
                    return throw new ClosedFiscalYearException(
                        __('Solo puede registrar, actualizar o eliminar registros del año fiscal en curso')
                    );
                }
            }
        });
    }

    /**
     * Método que establece las condiciones globales una vez el modelo este cargado
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new OrganismScope());
    }

    /**
     * Método que escanea todos los modelos presentes en la aplicación
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return array                Retorna un arreglo con los módulos
     */
    public function getModels($dir = "")
    {
        $path = app_path() . "/Models";
        $modules_path = base_path() . '/modules';

        if (!empty($dir)) {
            $path .= '/' . $dir;
        }
        $out = [];
        $results = scandir($path);

        foreach ($results as $result) {
            if ($result === '.' or $result === '..' or $result === 'Session.php') {
                continue;
            }

            $filename = $result;

            if (is_dir($filename)) {
                $out = array_merge($out, $this->getModels($filename));
            } else {
                $out[] = 'App\Models\\' . substr($filename, 0, -4);
            }
        }

        /* Escanea los directorios de módulos para obtener los correspondientes modelos */
        $results_modules = scandir($modules_path);
        foreach ($results_modules as $result_module) {
            if (!Module::has($result_module) || Module::isDisabled($result_module)) {
                /* Si el módulo no esta presente o esta deshabilitado se continua escaneando los demás módulos */
                continue;
            }
            if (
                $result_module === '.' || $result_module === '..' ||
                !file_exists(base_path() . '/modules/' . $result_module . '/Models')
            ) {
                continue;
            }

            $filename_module = $result_module;

            $r = scandir(base_path() . '/modules/' . $filename_module . '/Models');

            foreach ($r as $model) {
                if (in_array($model, ['.', '..', '.gitkeep', 'AssetClasification.php']) || strpos($model, '.php') <= 0) {
                    continue;
                }
                $filename_m = $model;

                if (is_dir($filename_m) || strpos($model, '.php') <= 0) {
                    $out = array_merge(
                        $out,
                        //'Modules\\' . $filename_module . '\\Models\\' . $this->getModels($filename_m)
                        $this->getModels($filename_m)
                    );
                } else {
                    $out[] = 'Modules\\' . $filename_module . '\\Models\\' . substr($filename_m, 0, -4);
                }
            }
        }

        return $out;
    }

    /**
     * Identifica si un modelo esta establecido para una eliminación lógica
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string  $model Nombre del modelo a evaluar
     *
     * @return boolean        Devuelve verdadero si el modelo esta establecido para una eliminación lógica,
     *                        de lo contrario devuelve falso
     */
    public function isModelSoftDelete($model)
    {
        return is_array(class_uses($model)) &&
            count(class_uses($model)) > 0 &&
            in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($model));
    }

    /**
     * Establece datos en cache
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @param  string $key  Clave del cache en el cual gestionar la información
     */
    public static function setCacheEvents($key)
    {
        static::saved(function ($model) use ($key) {
            $record = $model->where('id', $model->id)->orderBy('created_at', 'desc')->get();

            if (Cache::has($key)) {
                $cacheData = Cache::get($key);
                $record = $cacheData->merge($record);
                Cache::put($key, $record);
            } else {
                Cache::rememberForever($key, function () use ($record) {
                    return $record;
                });
            }
        });

        static::updated(function ($model) use ($key) {
            $record = $model->where('id', $model->id)->orderBy('updated_at', 'desc')->get();

            if (Cache::has($key)) {
                $cacheData = Cache::get($key);
                $record = $cacheData->filter(function ($doc) use ($model) {
                    return $doc->id !== $model->id;
                })->merge($record);
                Cache::put($key, $record);
            } else {
                Cache::rememberForever($key, function () use ($record) {
                    return $record;
                });
            }
        });

        static::deleted(function ($model) use ($key) {
            if (Cache::has($key)) {
                $cacheData = Cache::get($key);
                $record = $cacheData->filter(function ($doc) use ($model) {
                    return $doc->id !== $model->id;
                });
                Cache::put($key, $record);
            }
        });

        static::restored(function ($model) use ($key) {
            $record = $model->where('id', $model->id)->orderBy('updated_at', 'desc')->get();

            if (Cache::has($key)) {
                $cacheData = Cache::get($key);
                $record = $cacheData->merge($record);
                Cache::put($key, $record);
            } else {
                Cache::rememberForever($key, function () use ($record) {
                    return $record;
                });
            }
        });
    }
}
