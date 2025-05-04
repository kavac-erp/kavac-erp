<?php

namespace App\Http\Controllers;

use App\Models\Gender;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class PayrollGenderController
 * @brief Controlador del género
 *
 * Clase que gestiona los géneros
 *
 * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javirrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class GenderController extends Controller
{
    use ValidatesRequests;

    /**
     * Atributos personalizados
     *
     * @var array $customAttributes
     */
    private $customAttributes = [];

    /**
     * Define la configuración de la clase
     *
     * @author  Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javirrupe19@gmail.com>
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:gender.list', ['only' => 'index']);
        $this->middleware('permission:gender.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:gender.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:gender.delete', ['only' => 'destroy']);

        $this->customAttributes = ['name' => 'nombre'];
    }

    /**
     * Muestra todos los registros de cargos
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     * @author  Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javirrupe19@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de cargos
     */
    public function index()
    {
        return response()->json(['records' => Gender::all()], 200);
    }

    /**
     * Valida y registra un nuevo género
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     * @author  Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javirrupe19@gmail.com>
     *
     * @param  \Illuminate\Http\Request $request    Solicitud con los datos a guardar
     *
     * @return \Illuminate\Http\JsonResponse        Json: objeto guardado y mensaje de confirmación de la operación
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:genders,name']
        ], [], $this->customAttributes);
        $gender = Gender::create(['name' => $request->name]);
        return response()->json(['record' => $gender, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información del género
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     * @author  Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javirrupe19@gmail.com>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador del género a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $gender = Gender::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:genders,name,' . $gender->id]
        ], [], $this->customAttributes);
        $gender->name  = $request->name;
        $gender->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el género
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     * @author  Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javirrupe19@gmail.com>
     *
     * @param  integer $id                      Identificador del género a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json: objeto eliminado y mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $gender = Gender::find($id);
        $gender->delete();
        return response()->json(['record' => $gender, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los géneros registrados
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     * @author  Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javirrupe19@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los géneros
     */
    public function getGenders()
    {
        return response()->json(template_choices('App\Models\Gender', 'name', '', true));
    }
}
