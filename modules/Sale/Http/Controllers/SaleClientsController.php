<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleClient;
use Modules\Sale\Models\SaleClientsEmail;
use Modules\Sale\Models\SaleClientsPhone;
use App\Rules\Rif as RifRule;

/**
 * @class SaleClientsController
 * @brief Controlador que gestiona los clientes
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleClientsController extends Controller
{
    use ValidatesRequests;

    /**
     * Lista de elementos a mostrar en select
     *
     * @var array $data
     */
    protected $data = [];

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:sale.setting.client', ['only' => 'index']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'type_person_juridica'       => ['required'],
            'country_id'                 => ['required'],
            'estate_id'                  => ['required'],
            'municipality_id'            => ['required'],
            'parish_id'                  => ['required', 'max:200'],
            'address_tax'                => ['required', 'max:200'],
            'sale_clients_email'         => ['required'],
            'sale_clients_email.*.email' => ['email'],
            'sale_clients_phone'         => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'rif.required'                        => 'El campo rif es obligatorio.',
            'rif.unique'                          => 'El campo rif ya ha sido registrado.',
            'business_name.required'              => 'El campo razón social es obligatorio.',
            'type_person_juridica.required'       => 'El campo tipo de persona es obligatorio.',
            'representative_name.required'        => 'El campo nombres y apellidos del representante legal es obligatorio.',
            'name.required'                       => 'El campo nombre y apellido es obligatorio.',
            'country_id.required'                 => 'El campo país es obligatorio.',
            'estate_id.required'                  => 'El campo estado es obligatorio.',
            'municipality_id.required'            => 'El campo municipio es obligatorio.',
            'parish_id.required'                  => 'El campo parroquia es obligatorio.',
            'address_tax.required'                => 'El campo dirección fiscal es obligatorio.',
            'name_client.required'                => 'El campo persona de contacto es obligatorio.',
            'id_type.required'                    => 'El campo tipo de identificación es obligatorio.',
            'id_number.required'                  => 'El número de identificación es obligatorio.',
            'id_number.unique'                    => 'El número de identificación ya ha sido registrado.',
            'id_number.digits_between'            => 'El número de identificación no posee el formato correcto.',
            'sale_clients_email.required'         => 'El campo correo electrónico es obligatorio.',
            'sale_clients_email.*.email.email'    => 'El formato del correo electrónico es incorrecto.',
            'sale_clients_phone.required'         => 'El campo teléfono es obligatorio.',
        ];
    }

    /**
     * Muestra el listado de clientes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => SaleClient::with(['saleClientsEmail', 'saleClientsPhone'])->get()], 200);
    }

    /**
     * Almacena un nuevo cliente
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        if ($request->type_person_juridica == 'Natural') {
            $this->validate($request, [
                'id_type' => ['required'],
                'id_number' => ['required', 'unique:sale_clients,id_number', 'digits_between:1,10'],
                'name' => ['required'],
            ], $this->messages);
        } else {
            $this->validate($request, [
                'rif' => ['required', 'size:10', 'unique:sale_clients,rif', new RifRule()],
                'business_name' => ['required'],
                'representative_name' => ['required'],
                'name_client' => ['required'],
            ], $this->messages);
        }

        $client = new SaleClient();
        $client->type_person_juridica = $request->type_person_juridica;
        $client->rif = $request->rif;
        $client->business_name = $request->business_name;
        $client->representative_name = $request->representative_name;
        $client->name = $request->name;
        $client->country_id = $request->country_id;
        $client->estate_id = $request->estate_id;
        $client->municipality_id = $request->municipality_id;
        $client->parish_id = $request->parish_id;
        $client->address_tax = $request->address_tax;
        $client->name_client = $request->name_client;
        $client->id_type = $request->id_type;
        $client->id_number = $request->id_number;
        $client->save();

        if ($request->sale_clients_email && !empty($request->sale_clients_email)) {
            foreach ($request->sale_clients_email as $email) {
                $clientEmail = SaleClientsEmail::create([
                    'email'          => $email['email'],
                    'sale_client_id' => $client->id
                ]);
            }
        }

        if ($request->sale_clients_phone && !empty($request->sale_clients_phone)) {
            foreach ($request->sale_clients_phone as $phone) {
                $clientPhone = SaleClientsPhone::create([
                    'phone'          => $phone['phone'],
                    'sale_client_id' => $client->id
                ]);
            }
        }

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['record' => $client, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un cliente
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('sale::show');
    }

    /**
     * Muestra el formulario para editar un cliente
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('sale::edit');
    }

    /**
     * Actualiza la información de un cliente
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        /* Datos del cliente email y phone */
        $client = SaleClient::with('saleClientsEmail', 'saleClientsPhone')->find($id);

        $this->validate($request, $this->validateRules, $this->messages);

        if ($request->type_person_juridica == 'Natural') {
            $this->validate($request, [
                'id_type' => ['required'],
                'id_number' => ['required', 'unique:sale_clients,id_number,' . $client->id, 'digits_between:1,10'],
                'name' => ['required'],
            ], $this->messages);
        } else {
            $this->validate($request, [
                'rif' => ['required', 'size:10', 'unique:sale_clients,rif,' . $client->id, new RifRule()],
                'business_name' => ['required'],
                'representative_name' => ['required'],
                'name_client' => ['required'],
            ], $this->messages);
        }

        $client->rif = $request->rif;
        $client->business_name = $request->business_name;
        $client->type_person_juridica = $request->type_person_juridica;
        $client->representative_name = $request->representative_name;
        $client->name = $request->name;
        $client->country_id = $request->country_id;
        $client->estate_id = $request->estate_id;
        $client->municipality_id = $request->municipality_id;
        $client->parish_id = $request->parish_id;
        $client->address_tax = $request->address_tax;
        $client->name_client = $request->name_client;
        $client->id_type = $request->id_type;
        $client->id_number = $request->id_number;
        $client->save();

        if ($request->sale_clients_email && !empty($request->sale_clients_email)) {
            $client->saleClientsEmail()->delete();
            foreach ($request->sale_clients_email as $email) {
                $client->saleClientsEmail()->updateOrCreate(
                    [
                        'email'          => $email['email'],
                        'sale_client_id' => $client->id
                    ],
                    [
                        'email'          => $email['email'],
                        'sale_client_id' => $client->id
                    ]
                );
            }
        }

        if ($request->sale_clients_phone && !empty($request->sale_clients_phone)) {
            $client->saleClientsPhone()->delete();
            foreach ($request->sale_clients_phone as $phone) {
                $client->saleClientsPhone()->updateOrCreate(
                    [
                        'phone'          => $phone['phone'],
                        'sale_client_id' => $client->id
                    ]
                );
            }
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true], 200);
    }

    /**
     * Elimina un cliente registrado en el sistema
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único del cliente
     *
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function destroy($id)
    {
        /* Objeto con la información asociada al modelo sustrato */
        $client = SaleClient::find($id);
        if ($client) {
            $client->delete();
            return response()->json(['record' => $client, 'message' => 'Success'], 200);
        }
    }

    /**
     * Muestra una lista de los tipos de bienes
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getSaleClientsRif()
    {
        $records = [];
        $saleClients = SaleClient::orderBy('id', 'ASC')->get();

        array_push($records, ['id' => '', 'text' => 'Seleccione...']);

        foreach ($saleClients as $saleClient) {
            if ($saleClient->type_person_juridica == 'Natural') {
                array_push($records, [
                    'id'            => $saleClient->id,
                    'text'          => $saleClient->name . ' - ' . $saleClient->id_type . $saleClient->id_number,
                ]);
            } elseif ($saleClient->type_person_juridica == 'Jurídica') {
                array_push($records, [
                    'id'            => $saleClient->id,
                    'text'          => $saleClient->business_name . ' - ' . $saleClient->rif,
                ]);
            }
        }
        return response()->json(['records' => $records], 200);
    }

    /**
     * Obtiene los clientes registrados
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los productos
     */
    public function getSaleClient($id)
    {
        $saleClient = SaleClient::with(['saleClientsEmail', 'saleClientsPhone'])->find($id);
        return response()->json(['sale_client' => $saleClient], 200);
    }
}
