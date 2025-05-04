<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Purchase\Models\PurchaseSupplierSpecialty;

/**
 * @class PurchaseSupplierSpecialtyController
 * @brief Gestiona los procesos de las especialidades de proveedores
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseSupplierSpecialtyController extends Controller
{
    use ValidatesRequests;

    /**
     * Muestra el listado de especialidades de proveedores
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => PurchaseSupplierSpecialty::all()], 200);
    }

    /**
     * Muestra el formulario para crear una nueva especialidad de proveedor
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('purchase::create');
    }

    /**
     * Almacena una nueva especialidad de proveedor
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'unique:purchase_supplier_specialties,name'],
        ]);

        $supplierSpecialty = PurchaseSupplierSpecialty::create([
            'name' => $request->name,
            'description' => $request->description ?? null
        ]);

        return response()->json(['record' => $supplierSpecialty, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de una especialidad de proveedor
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('purchase::show');
    }

    /**
     * Muestra el formulario para editar una especialidad de proveedor
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('purchase::edit');
    }

    /**
     * Actualiza una especialidad de proveedor
     *
     * @param  Request $request Datosos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        /* Datos de la especialidad de proveedores */
        $supplierSpecialty = PurchaseSupplierSpecialty::find($id);

        $this->validate($request, [
            'name' => ['required', 'unique:purchase_supplier_specialties,name,' . $supplierSpecialty->id],
        ]);

        $supplierSpecialty->name = $request->name;
        $supplierSpecialty->description = $request->description ?? null;
        $supplierSpecialty->save();

        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Elimina una especialidad de proveedor
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /* Datos de la especialidad de proveedores */
        $supplierSpecialty = PurchaseSupplierSpecialty::find($id);
        $supplierSpecialty->delete();
        return response()->json(['record' => $supplierSpecialty, 'message' => 'Success'], 200);
    }
}
