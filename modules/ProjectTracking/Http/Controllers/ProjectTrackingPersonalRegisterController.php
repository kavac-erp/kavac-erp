<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingPersonalRegister;

/**
 * @class ProjectTrackingPersonalRegisterController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingPersonalRegisterController extends Controller
{
    use ValidatesRequests;

    /**
     * Obtiene todo el personal registrado
     *
     * @return    \Illuminate\Http\JsonResponse
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
     * @author    Oscar González <xxmaestroyixx@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
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
     * Muestra el formulario para un nuevo registro de personal en seguimiento de proyectos
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Almacena un nuevo registro de personal en seguimiento de proyectos
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
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
     * Muestra información sobre un registro de personal en seguimiento de proyectos
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('projecttracking::show');
    }

    /**
     * Muestra el formulario para editar un registro de personal en seguimiento de proyectos
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    void
     */
    public function edit($id)
    {
        //
    }

    /**
     * Actualiza un registro de personal en seguimiento de proyectos
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
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
     * Elimina un registro de personal en seguimiento de proyectos
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $personal = ProjectTrackingPersonalRegister::find($id);
        $personal->delete();
        return response()->json(['record' => $personal, 'message' => 'Success'], 200);
    }
}
