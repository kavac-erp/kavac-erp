<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

/**
 * @class DepartmentController
 * @brief Gestiona información de Departamentos
 *
 * Controlador para gestionar Departamentos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DepartmentController extends Controller
{
    /**
     * Lista de elementos a mostrar
     *
     * @var array $data
     */
    protected $data = [];

    /**
     * Método constructor de la clase
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        $this->data[0] = [
            'id' => '',
            'text' => 'Seleccione...',
        ];
    }

    /**
     * Listado con todos los departamentos registrados
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse     JSON con información de respuesta a la petición
     */
    public function index()
    {
        return response()->json(['records' => Department::with(['parent', 'childrens'])->get()], 200);
    }

    /**
     * Registra un nuevo departamento
     *
     * @author     Ing. Francisco Escala <fjescala@cenditel.gob.ve> | <fjescala@gmail.com>
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request    Objeto con información de la petición
     *
     * @return    JsonResponse     JSON con información de respuesta a la petición
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => ['required'],
            'institution_id' => ['required'],
            'acronym' => ['max:4'],
        ];
        $mesg = [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.unique' => 'El campo nombre ya ha sido registrado.',
            'acronym.max' => 'El campo acrónimo no debe ser mayor a 4 caracteres.',
            'acronym.unique' => 'El campo acrónimo ya ha sido registrado.',
            'institution_id.required' => 'El campo institución es obligatorio.',

        ];

        $acronym = Department::where(['institution_id' => $request->institution_id, 'acronym' => $request->acronym])->first();

        if ($acronym != null) {
            $rules = array_merge($rules, [
                'acronym' => ['max:4', 'unique:departments,acronym'],
            ]);
            $mesg = array_merge($mesg, [
                'acronym.unique' => 'El campo acrónimo ya ha sido registrado en la institucion.',
            ]);
        }

        $name = Department::where(['institution_id' => $request->institution_id, 'name' => $request->name])->first();
        if ($name != null) {
            $rules = array_merge($rules, [

                'name' => ['unique:departments,name'],

            ]);
            $mesg = array_merge($mesg, [
                'name.unique' => 'El campo nombre ya ha sido registrado en la institucion.',
            ]);
        }
        $this->validate($request, $rules, $mesg);

        // Establece la jerarquía del departamento
        $hierarchy = 0;

        if (!is_null($request->parent_id) || !empty($request->parent_id)) {
            // Departamento asociado
            $dto = Department::where('parent_id', $request->parent_id)->first();
            if ($dto) {
                $hierarchy = (int) $dto->hierarchy + 1;
            }
        }

        // Objeto con información del departamento registrado
        $department = Department::create([
            'name' => $request->name,
            'acronym' => ($request->acronym) ? $request->acronym : null,
            'hierarchy' => $hierarchy,
            'issue_requests' => $request->issue_requests ?? false,
            'active' => $request->active ?? false,
            'administrative' => $request->administrative ?? false,
            'parent_id' => ($request->parent_id) ? $request->parent_id : null,
            'institution_id' => $request->institution_id,
        ]);

        return response()->json(['record' => $department, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza los datos de un departamento
     *
     * @author     Ing. Francisco Escala <fjescala@cenditel.gob.ve> | <fjescala@gmail.com>
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request       $request       Objeto con datos de la petición
     * @param     Department    $department    Objeto con información del departamento a modificar
     *
     * @return    JsonResponse     JSON con información de respuesta a la petición
     */
    public function update(Request $request, Department $department)
    {
        $rules = [
            'name' => ['required'],
            'institution_id' => ['required'],
            'acronym' => ['max:4'],
        ];
        $mesg = [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.unique' => 'El campo nombre ya ha sido registrado.',
            'acronym.max' => 'El campo acrónimo no debe ser mayor a 4 caracteres.',
            'acronym.unique' => 'El campo acrónimo ya ha sido registrado.',
            'institution_id.required' => 'El campo institución es obligatorio.',

        ];

        $acronym = Department::where(['institution_id' => $request->institution_id, 'acronym' => $request->acronym])->first();

        if ($acronym != null) {
            if ($acronym->id != $department->id) {
                $rules = array_merge($rules, [
                    'acronym' => [Rule::unique('departments', 'acronym')->ignore($department->id)],
                ]);
                $mesg = array_merge($mesg, [
                    'acronym.unique' => 'El campo acrónimo ya ha sido registrado en la institucion.',
                ]);
            }
        }

        $name = Department::where(['institution_id' => $request->institution_id, 'name' => $request->name])->first();
        if ($name != null) {
            if ($name->id != $department->id) {
                $rules = array_merge($rules, [

                    'name' => [Rule::unique('departments', 'name')->ignore($department->id)],

                ]);
                $mesg = array_merge($mesg, [
                    'name.unique' => 'El campo nombre ya ha sido registrado en la institucion.',
                ]);
            }
        }
        $this->validate($request, $rules, $mesg);

        $hierarchy = 0;

        if (!is_null($request->parent_id) || !empty($request->parent_id)) {
            $dto = Department::where('parent_id', $request->parent_id)->first();
            if ($dto) {
                $hierarchy = (int) $dto->hierarchy + 1;
            }
        }

        $department->name = $request->name;
        $department->acronym = ($request->acronym) ? $request->acronym : null;
        $department->hierarchy = (string) $hierarchy;
        $department->issue_requests = $request->issue_requests ?? false;
        $department->active = $request->active ?? false;
        $department->administrative = $request->administrative ?? false;
        $department->parent_id = ($request->parent_id) ? $request->parent_id : null;
        $department->institution_id = $request->institution_id;
        $department->save();

        return response()->json(['message' => __('Registro actualizado correctamente')], 200);
    }

    /**
     * Elimina un departamento
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Department    $department    Objeto con información del departamento a eliminar
     *
     * @return    JsonResponse     JSON con información de respuesta a la petición
     */
    public function destroy(Department $department)
    {
        $department->delete();
        return response()->json(['record' => $department, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene un listado de departamentos
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request  $request        Objeto con los datos de la petición
     * @param  integer  $institution_id Identificador de la organización
     *
     * @return JsonResponse     JSON con información de respuesta a la petición
     */
    public function getDepartments(Request $request, $institution_id)
    {
        return response()->json(
            template_choices(Department::class, 'name', ['institution_id' => $institution_id], true)
        );
    }
}
