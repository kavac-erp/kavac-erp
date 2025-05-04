<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleListSubservicesAttribute;
use Modules\Sale\Models\SaleListSubservices;

/**
 * @class SaleListSubservicesController
 * @brief Gestiona los datos de los subservicios
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleListSubservicesController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */

    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:sale.setting.subservices', ['only' => 'index']);
    }

    /**
     * Muestra todos la Lista de subservicios
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        /* Contiene los registros del personal, tipos de proyecto y los tipos de producto */
        $saleListSubservices = SaleListSubservices::with(['SaleListSubservicesAttribute', 'saleTypeGood'])->get()->all();
        foreach ($saleListSubservices as $subservice) {
            $subservice['sale_type_good_name'] = $subservice->saleTypeGood ? $subservice->saleTypeGood->name : '';
        }
        return response()->json(['records' => $saleListSubservices], 200);
    }

    /**
     * Valida y registra un nuevo metodo de lista de subsevicios
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request    Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'sale_type_good' => ['required'],
                'name'           => ['required', 'max:100'],
                'description'    => ['required', 'max:200']
            ],
            $messages =
                [
                    'sale_type_good_id.required' => 'El campo Tipo de Servicio es obligatorio.'
                ]
        );
        $salelistsubservicesMethod = SaleListSubservices::create([
            'sale_type_good' => $request->sale_type_good,
            'name'              => $request->name,
            'description'       => $request->description,
            'define_attributes' => !empty($request->define_attributes)
                ? $request->define_attributes
                : false
        ]);

        if ($salelistsubservicesMethod->define_attributes) {
            foreach ($request->sale_list_subservices_attribute as $att) {
                $attribute = SaleListSubservicesAttribute::create([
                    'value'                 => $att['value'],
                    'sale_list_subservices_id' => $salelistsubservicesMethod->id
                ]);
            }
        };

        return response()->json(['record' => $salelistsubservicesMethod, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un subservicio
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('sale::show');
    }

    /**
     * Muestra el formulario para editar un subservicio
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('sale::edit');
    }

    /**
     * Actualiza la información de subsevicios
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Datos de la petición
     * @param  integer $id                          Identificador del datos a actualizar
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(Request $request, $id)
    {
        $salelistsubservicesMethod = SaleListSubservices::find($id);

        $this->validate($request, [
            'sale_type_good'      => ['required'],
            'name'                => ['required', 'max:100'],
            'description'         => ['required']
        ]);
        $salelistsubservicesMethod->name                = $request->input('sale_type_good');
        $salelistsubservicesMethod->name                = $request->input('name');
        $salelistsubservicesMethod->description         = $request->input('description');
        $salelistsubservicesMethod->define_attributes   =  !empty($request->define_attributes)
            ? $request->input('define_attributes')
            : false;
        $salelistsubservicesMethod->save();
        $salelist_subservicesattribute = SaleListSubservicesAttribute::where('sale_list_subservices_id', $salelistsubservicesMethod->id)->get();

        /* Se búsca si en la solicitud se eliminaron atributos registrados anteriormente */
        if ($salelist_subservicesattribute) {
            foreach ($salelist_subservicesattribute as $att) {
                $equal = false;
                foreach ($request->sale_list_subservices_attribute as $attr) {
                    if ($attr['value'] == $att->value) {
                        $equal = true;
                        break;
                    }
                }
                if ($equal == false) {
                    $value = $att->SaleListSubservices();
                    if ($value) {
                        $att->delete();
                    }
                }
            }
        }

        /* Registro de los nuevos atributos del subservicio */
        if ($salelistsubservicesMethod->define_attributes == true) {
            foreach ($request->sale_list_subservices_attribute as $att) {
                $attribute = SaleListSubservicesAttribute::where('value', $att['value'])
                    ->where('sale_list_subservices_id', $salelistsubservicesMethod->id)->first();
                if (is_null($attribute)) {
                    //return $att;
                    $attribute = SaleListSubservicesAttribute::create([
                        'value' => $att['value'],
                        'sale_list_subservices_id' => $salelistsubservicesMethod->id
                    ]);
                }
            }
        };

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el metodo de lista de subsevicios
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del metodo de lista de subsevicios a eliminar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $salelistsubservicesMethod = SaleListSubservices::find($id);
        $salelistsubservicesMethod->delete();
        return response()->json(['record' => $salelistsubservicesMethod, 'message' => 'Success'], 200);
    }
    /**
     * Obtiene los Subservicios registrados
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleListSubServicesMethod()
    {
        return response()->json(template_choices('Modules\Sale\Models\SaleListSubservices', 'name', '', true));
    }
}
