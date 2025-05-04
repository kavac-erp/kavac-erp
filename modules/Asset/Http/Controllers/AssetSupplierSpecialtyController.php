<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Renderable;
use Modules\Asset\Models\AssetSupplierSpecialty;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class      AssetSupplierSpecialtyController
 * @brief      Controlador de especialidades de Proveedores de bienes
 *
 * Clase que gestiona los especialidades de Proveedores de bienes
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSupplierSpecialtyController extends Controller
{
    use ValidatesRequests;

    /**
     * Listado de especialidades de Proveedores de bienes
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => AssetSupplierSpecialty::all()], 200);
    }

    /**
     * Muestra el formulario para crear una nueva especialidad de Proveedor de bienes
     *
     * @return Renderable
     */
    public function create()
    {
        return view('asset::create');
    }

    /**
     * Almacena una nueva especialidad de Proveedor de bienes
     *
     * @param  Request $request Datos de la petición
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'unique:purchase_supplier_specialties,name'],
        ]);

        $supplierSpecialty = AssetSupplierSpecialty::create([
            'name' => $request->name,
            'description' => $request->description ?? null
        ]);

        return response()->json(['record' => $supplierSpecialty, 'message' => 'Success'], 200);
    }

    /**
     * Muestro los detalles de la especialidad de Proveedor de bienes
     *
     * @return Renderable
     */
    public function show()
    {
        return view('asset::show');
    }

    /**
     * Muestra el formulario para editar una especialidad de Proveedor de bienes
     *
     * @return Renderable
     */
    public function edit()
    {
        return view('asset::edit');
    }

    /**
     * Actualiza la especialidad de Proveedor de bienes
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id      Identificador de la especialidad de Proveedor de bienes
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        /* Datos de la especialidad de proveedores */
        $supplierSpecialty = AssetSupplierSpecialty::find($id);

        $this->validate($request, [
            'name' => ['required', 'unique:purchase_supplier_specialties,name,' . $supplierSpecialty->id],
        ]);

        $supplierSpecialty->name = $request->name;
        $supplierSpecialty->description = $request->description ?? null;
        $supplierSpecialty->save();

        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Elimina una especialidad de Proveedor de bienes
     *
     * @param  integer $id Identificador de la especialidad de Proveedor de bienes
     *
     * @return JsonResponse
     */
    public function destroy($id)
    {
        /* Datos de la especialidad de proveedores */
        $supplierSpecialty = AssetSupplierSpecialty::find($id);
        $supplierSpecialty->delete();
        return response()->json(['record' => $supplierSpecialty, 'message' => 'Success'], 200);
    }
}
