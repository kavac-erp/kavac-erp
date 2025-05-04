<?php

namespace Modules\Asset\Http\Controllers;

use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Asset\Models\AssetStorage;
use Illuminate\Contracts\Support\Renderable;
use Modules\Asset\Models\AssetInstitutionStorage;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class AssetStorageController
 * @brief Gestiona los depósitos de bienes
 *
 * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetStorageController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:asset.setting.storage.create', ['only' => ['index', 'create', 'store']]);
        $this->middleware('permission:asset.setting.storage.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:asset.setting.storage.delete', ['only' => ['destroy']]);
    }

    /**
     * Muestra el listado de los depósitos registrados
     *
     * @author    Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @param     integer|null $institution ID de la institución
     *
     * @return \Illuminate\Http\Response JSON con los registros a mostrar
     */
    public function index($institution = null)
    {
        if (!is_null($institution)) {
            return response()->json(['records' => AssetInstitutionStorage::where('institution_id', $institution)
                ->with(
                    ['storage' =>
                    function ($query) {
                        $query->with(['parish' => function ($query) {
                            $query->with(['municipality' => function ($query) {
                                $query->with(['estate' => function ($query) {
                                    $query->with('country');
                                }]);
                            }]);
                        }]);
                    },'institution']
                )->get()], 200);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
            $institution = $institution->id;
            return response()->json(['records' => AssetInstitutionStorage::where('institution_id', $institution)
                ->with(
                    ['storage' =>
                    function ($query) {
                        $query->with(['parish' => function ($query) {
                            $query->with(['municipality' => function ($query) {
                                $query->with(['estate' => function ($query) {
                                    $query->with('country');
                                }]);
                            }]);
                        }]);
                    },'institution']
                )->get()], 200);
        }
    }

    /**
     * Muestra el formulario para la creación de un nuevo depósito de bienes
     *
     * @author    Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @return    Renderable
     */
    public function create()
    {
        return view('asset::create');
    }

    /**
     * Valida y registra un nuevo depósito
     *
     * @author    Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return \Illuminate\Http\Response|void
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name'            => ['required', 'max:100', 'unique:asset_storages,name'],
                'address'         => ['required'],
                'country_id'      => ['required'],
                'estate_id'       => ['required'],
                'municipality_id' => ['required'],
                'parish_id'       => ['required'],
            ],
            [
                'name.required'            => 'El campo Nombre del depósito es obligatorio.',
                'name.max'                 => 'El campo Nombre del depósito no debe ser mayor a 100 caracteres.',
                'name.unique'              => 'Ya ha sido registrado un depósito con ese nombre.',
                'country_id.required'      => 'El campo País es obligatorio.',
                'estate_id.required'       => 'El campo Estado es obligatorio.',
                'municipality_id.required' => 'El campo Municipio es obligatorio.',
                'address.required'         => 'El campo Dirección es obligatorio.',
                'parish_id.required'       => 'El campo Parroquia es obligatorio.'
            ],
        );

        DB::transaction(function () use ($request) {

            $storage = AssetStorage::create([
                'name'      => $request->name,
                'address'   => $request->address,
                'parish_id' => $request->parish_id,
                'active'    => !empty($request->active) ? $request->active : false,
            ]);

            if (empty($request->institution_id)) {
                $institution = Institution::where('active', true)->where('default', true)->first();
            }
            $institution_id = empty($request->institution_id) ? $institution->id : $request->institution_id;

            if ($request->main) {
                AssetInstitutionStorage::where('main', true)
                      ->update(['main' => false]);
            }
            $storage_institution = AssetInstitutionStorage::create([
                'institution_id' => $institution_id,
                'storage_id'   => $storage->id,
                'main'           => !empty($request->main) ? $request->input('main') : false,
            ]);

            return response()->json(['record' => $storage, 'message' => 'Success'], 200);
        });
    }

    /**
     * Muestra información del depósito de bienes
     *
     * @author    Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable
     */
    public function show($id)
    {
        return view('asset::show');
    }

    /**
     * Muestra el formulario para la actualización de datos de un depósito de bienes
     *
     * @author    Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable
     */
    public function edit($id)
    {
        return view('asset::edit');
    }

    /**
     * Valida y actualiza un depósito
     *
     * @author    Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     * @param  \Modules\Asset\Models\AssetStorage  $storage Datos del almacén
     *
     * @return \Illuminate\Http\Response|void
     */
    public function update(Request $request, AssetStorage $storage)
    {
        $this->validate(
            $request,
            [
                'name'            => ['required', 'max:100', 'unique:asset_storages,name,' . $storage->id],
                'address'         => ['required'],
                'country_id'      => ['required'],
                'estate_id'       => ['required'],
                'municipality_id' => ['required'],
                'parish_id'       => ['required'],
            ],
            [
                'name.required'            => 'El campo Nombre del depósito es obligatorio.',
                'name.max'                 => 'El campo Nombre del depósito no debe ser mayor a 100 caracteres.',
                'name.unique'              => 'Ya ha sido registrado un depósito con ese nombre.',
                'country_id.required'      => 'El campo País es obligatorio.',
                'estate_id.required'       => 'El campo Estado es obligatorio.',
                'municipality_id.required' => 'El campo Municipio es obligatorio.',
                'address.required'         => 'El campo Dirección es obligatorio.',
                'parish_id.required'       => 'El campo Parroquia es obligatorio.'
            ],
        );

        DB::transaction(function () use ($storage, $request) {

            $storage->name      = $request->name;
            $storage->address   = $request->address;
            $storage->parish_id = $request->parish_id;
            $storage->active    = !empty($request->active) ? $request->active : false;
            $storage->save();

            if (empty($request->institution_id)) {
                $institution = Institution::where('active', true)->where('default', true)->first();
            }

            $institution_id =  empty($request->institution_id) ? $institution->id : $request->institution_id;

            if ($request->main) {
                AssetInstitutionStorage::where('main', true)
                      ->update(['main' => false]);
            }

            $storage_institution = AssetInstitutionStorage::where('institution_id', $institution_id)
                                    ->where('storage_id', $storage->id)->first();

            $storage_institution->main = !empty($request->input('main')) ? $request->input('main') : false;
            $storage_institution->save();

            return response()->json(['message' => 'Success'], 200);
        });
    }

    /**
     * [descripción del método]
     *
     * @author    Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @param  \Modules\Asset\Models\AssetStorage  $storage Datos del depósito
     * @param  integer $id Identificador único del registro
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $inst_stor = AssetInstitutionStorage::find($id);
            $storage = AssetStorage::find($id);
            $inst_stor->delete();
            $storage->delete();
            return response()->json(['record' => $storage, 'message' => 'Success'], 200);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => true, 'message' => __($e->getMessage())], 200);
        }
    }

    /**
     * Gestiona los depositos de bienes
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function manage($id)
    {
        $storage_inst = AssetInstitutionStorage::where('storage_id', $id)->first();
        $storage_inst->manage = !$storage_inst->manage;
        $storage_inst->save();

        return response()->json(
            [
                'records' => AssetInstitutionStorage::where('institution_id', $storage_inst->institution_id)
                ->with(
                    ['storage' =>
                    function ($query) {
                        $query->with(['parish' => function ($query) {
                            $query->with(['municipality' => function ($query) {
                                $query->with(['estate' => function ($query) {
                                    $query->with('country');
                                }]);
                            }]);
                        }]);
                    },'institution']
                )->get(),
                'manage' => $storage_inst->manage],
            200
        );
    }

    /**
     * Obtiene los depósitos de bienes
     *
     * @param integer|null $institution_id Identificador de la institución
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStorages($institution_id = null)
    {
        $storages = AssetInstitutionStorage::select('id', 'institution_id', 'storage_id', 'main', 'manage')
            ->with(['storage' => function ($query) {
                $query->where('active', true);
            }]);

        if (!empty($institution_id)) {
            $storages = $storages->where('institution_id', $institution_id);
        }
        return response()->json($storages->get(), 200);
    }
}
