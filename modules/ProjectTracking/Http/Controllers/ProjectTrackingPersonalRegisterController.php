<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingPersonalRegister;

/**
 * @class ProjectTrackingPersonalRegisterController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingPersonalRegisterController extends Controller
{
    use ValidatesRequests;

    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        $PersonalRegisterList = ProjectTrackingPersonalRegister::with('Position')->get()->all();
        foreach ($PersonalRegisterList as $personal) {
            $personal['position_name'] = $personal->Position->name;
        }

        return response()->json(['records' => $PersonalRegisterList], 200);
    }

    /**
     * Retorna un json con todo el personal para ser usado en un componente <select2>
     *
     * @method    getPersonal
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com>
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function getPersonal()
    {
        $personalList = ProjectTrackingPersonalRegister::all();
        $personal = [];
        array_push($personal, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($personalList->all() as $person) {
            array_push($personal, [
                'id' => $person->id,
                'text' => $person->name . ' ' . $person->last_name
            ]);
        }
        return response()->json($personal);
    }

    /**
     * [descripción del método]
     *
     * @method    create
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * [descripción del método]
     *
     * @method    store
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => ['required', 'max:50'],
                'last_name' => ['required', 'max:50'],
                'id_number' => ['required', 'max:50'],
                'position_id' => ['required']
            ],
            [],
            [
                'id_number' => 'Cédula',
                'position_id' => 'Cargo'
            ]
        );
        $personal = ProjectTrackingPersonalRegister::create([
            'name' => $request->input('name'),
            'last_name' => $request->input('last_name'),
            'id_number' => $request->input('id_number'),
            'position_id' => $request->input('position_id')
        ]);
        return response()->json(['record' => $personal, 'message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    show
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function show($id)
    {
        return view('projecttracking::show');
    }

    /**
     * [descripción del método]
     *
     * @method    edit
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function edit($id)
    {
        //
    }

    /**
     * [descripción del método]
     *
     * @method    update
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => ['required', 'max:50'],
                'last_name' => ['required', 'max:50'],
                'id_number' => ['required', 'max:50'],
                'position_id' => ['required']
            ],
            [],
            [
                'id_number' => 'Cédula',
                'position_id' => 'Cargo'
            ]
        );
        $personal = ProjectTrackingPersonalRegister::find($request->input('id'));
        $personal->name = $request->input('name');
        $personal->last_name = $request->input('last_name');
        $personal->id_number = $request->input('id_number');
        $personal->position_id = $request->input('position_id');
        $personal->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    destroy
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        $personal = ProjectTrackingPersonalRegister::find($id);
        $personal->delete();
        return response()->json(['record' => $personal, 'message' => 'Success'], 200);
    }
}
