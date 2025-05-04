<?php

namespace Modules\DigitalSignature\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Modules\DigitalSignature\Models\Signprofile;
use Modules\DigitalSignature\Models\User;
use Modules\DigitalSignature\Helpers\Helper;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class DigitalSignatureController
 * @brief Controlador para la gestión de firma electrónica
 *
 * Clase que gestiona la firma electrónica
 *
 * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DigitalSignatureController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración inicial de la clase.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:digitalsignature.index', ['only' => 'index']);
        $this->middleware('permission:digitalsignature.store', ['only' => 'store']);
        $this->middleware('permission:digitalsignature.update', ['only' => 'update']);
        $this->middleware('permission:digitalsignature.destroy', ['only' => 'destroy']);
        $this->middleware('permission:digitalsignature.sign', ['only' => 'signFileApi']);
        $this->middleware('permission:digitalsignature.verify', ['only' => 'verifySignApi']);
    }

    /**
     * Muestra la ventana principal del módulo Digital signature
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @return  \Illuminate\View\View
     */
    public function index()
    {
        if (Auth::user()->hasPermission('digitalsignature.index')) {
            $permissionIndex = true;
        } else {
            $permissionIndex = false;
        }

        if (Auth::user()->hasPermission('digitalsignature.store')) {
            $permissionStore = true;
        } else {
            $permissionStore = false;
        }

        if (Auth::user()->hasPermission('digitalsignature.update')) {
            $permissionUpdate = true;
        } else {
            $permissionUpdate = false;
        }

        if (Auth::user()->hasPermission('digitalsignature.sign')) {
            $permissionSign = true;
        } else {
            $permissionSign = false;
        }

        if (Auth::user()->hasPermission('digitalsignature.verify')) {
            $permissionVerify = true;
        } else {
            $permissionVerify = false;
        }

        if (Auth::user()->hasPermission('digitalsignature.destroy')) {
            $permissionDestroy = true;
        } else {
            $permissionDestroy = false;
        }

        if (User::find(auth()->user()->id)->signprofiles) {
            /* datos del usuario con certificado firmante */
            $userprofile = User::find(auth()->user()->id)->signprofiles;
            /* certificado firmante del usuario */
            $certuser = Crypt::decryptString($userprofile['cert']);
            /* certficado firmante del usuario en una matriz para acceder a sus campos */
            $cert = openssl_x509_parse($certuser);
            /* fecha de expiración del certificado firmante del usuario */
            $fecha = date('d-m-y H:i:s', $cert['validTo_time_t']);

            return view('digitalsignature::create', [
                'Identidad' => $cert['subject']['CN'],
                'Verificado' => $cert['issuer']['CN'],
                'Caduca' => $fecha,
                'cert' => 'true',
                'certdetail' => 'false',
                'permissionStore' => $permissionStore,
                'permissionIndex' => $permissionIndex,
                'permissionUpdate' => $permissionUpdate,
                'permissionSign' => $permissionSign,
                'permissionVerify' => $permissionVerify,
                'permissionDestroy' => $permissionDestroy
            ]);
        }
        return view('digitalsignature::create', [
            'informacion' => 'No posee un certificado firmante',
            'cert' => 'false',
            'certdetail' => 'false',
            'permissionStore' => $permissionStore,
            'permissionIndex' => $permissionIndex,
            'permissionUpdate' => $permissionUpdate,
            'permissionSign' => $permissionSign,
            'permissionVerify' => $permissionVerify,
            'permissionDestroy' => $permissionDestroy
        ]);
    }

    /**
     * Muestra el formulario para crear una firma electrónica
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('digitalsignature::create');
    }

    /**
     * Genera un nuevo certificado P12
     *
     * @param string $filename Nombre del archivo
     * @param string $temporaryPath Ruta temporal
     * @param string $passphrase Clave del certificado
     * @param boolean $pkcs12 Certificado PKCS12
     *
     * @return array|bool
     */
    public function regenerateCert(&$filename, $temporaryPath, $passphrase, &$pkcs12)
    {
        /* En caso de que no se pueda leer el certificado debido a la version de openssl */
        $newFileName = Str::random(10) . '.p12';
        $newFilePath = $temporaryPath . '/' . $newFileName;

        /* Debido a que la version de openssl es incompatible con el comando de creación de certificado
           se debe desencriptar el certificado y convertirlo en un archivo .p12 con la nueva version de openssl */
        $command = "openssl pkcs12 -legacy -in "
            . $temporaryPath
            . '/'
            . $filename
            . " -nodes -out "
            . $newFilePath
            . " -passin pass:"
            . $passphrase
            . " && openssl pkcs12 -in "
            .  $newFilePath
            . " -export -out "
            . $newFilePath
            . " -passin pass:"
            . $passphrase
            . " -passout pass:"
            . $passphrase
            . " && rm "
            . $temporaryPath
            . '/'
            . $filename;

        exec($command, $output, $result_code);

        $pkcs12 = openssl_pkcs12_read(file_get_contents($newFilePath), $certInfo, $passphrase);

        $filename = $newFileName;

        return ($result_code == 0 && $pkcs12) ? $certInfo : false;
    }

    /**
     * Almacena el certificado del firmante
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /* dirección de almacenamiento del archivo -p12 */
        $temporaryPath = storage_path('temporary');
        /* nombre del archivo .p12 */
        $filename = Str::random(10) . '.p12';
        $request->file('pkcs12')->storeAs('', $filename, 'temporary');
        /* archivo .p12 */
        $certStore = file_get_contents($temporaryPath . '/' . $filename);
        /* frase de paso del archivo .p12 */
        $passphrase = $request->get('password');
        $certInfo = [];

        if (!$certStore) {
            $request->session()->flash(
                'message',
                [
                    'type' => 'other',
                    'title' => 'Alerta',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger',
                    'text' => 'No se puede leer el fichero del certificado'

                ]
            );
            return redirect()->route('digitalsignature');
        }

        /* objeto del certificado firmante */
        $pkcs12 = openssl_pkcs12_read($certStore, $certInfo, $passphrase);

        /* En caso de que no se pueda leer el certificado debido a la version de openssl */
        if (!$pkcs12) {
            $certInfo = $this->regenerateCert($filename, $temporaryPath, $passphrase, $pkcs12);
            if (!$certInfo) {
                $request->session()->flash(
                    'message',
                    [
                        'type' => 'other',
                        'title' => 'Alerta',
                        'icon' => 'screen-error',
                        'class' => 'growl-danger',
                        'text' => 'Contraseña incorrecta'

                    ]
                );
                return redirect()->route('digitalsignature');
            }
        }

        /* Certificado del firmante */
        $cert = Crypt::encryptString($certInfo['cert']);
        /* clave privada */
        $pkey = Crypt::encryptString($certInfo['pkey']);
        /* frase de paso cifrada */
        $passphraseCrypt = Crypt::encryptString($passphrase);

        /* objeto tipo Signprofile */
        $profile = new Signprofile();
        $profile->cert = $cert;
        $profile->pkey = $pkey;
        $profile->passphrase = $passphraseCrypt;
        $profile->user_id = Auth::user()->id;
        $profile->save();
        Storage::disk('temporary')->delete($filename);

        $request->session()->flash(
            'message',
            [
                'type'     => 'store',
            ]
        );

        return redirect()->route('digitalsignature');
    }

    /**
     * Muestra información del certificado del firmante
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('digitalsignature::show');
    }

    /**
     * Muestra el formulario para editar el certificado del firmante
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('digitalsignature::edit');
    }

    /**
     * Actualiza el certificado del firmante
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        //Se toma el certificado que se va actualizar (Borrar)
        if (User::find(auth()->user()->id)->signprofiles) {
            $userprofile = User::find(auth()->user()->id)->signprofiles;
        }

        /* dirección de almacenamiento del archivo -p12 */
        $temporaryPath = storage_path('temporary');
        /* nombre del archivo .p12 */
        $filename = Str::random(10) . '.p12';
        $request->file('pkcs12')->storeAs('', $filename, 'temporary');
        /* archivo .p12 */
        $certStore = file_get_contents(storage_path('temporary') . '/' . $filename);
        /* frase de paso del archivo .p12 */
        $passphrase = $request->get('password');
        $certInfo = [];

        if (!$certStore) {
            echo "Error: No se puede leer el fichero del certificado\n";
            exit;
        }

        /* objeto del certificado firmante */
        $pkcs12 = openssl_pkcs12_read($certStore, $certInfo, $passphrase);
        /** En caso de que no se pueda leer el certificado debido a la version de openssl */
        if (!$pkcs12) {
            $certInfo = $this->regenerateCert($filename, $temporaryPath, $passphrase, $pkcs12);
            if (!$certInfo) {
                $request->session()->flash(
                    'message',
                    [
                        'type' => 'other',
                        'title' => 'Alerta',
                        'icon' => 'screen-error',
                        'class' => 'growl-danger',
                        'text' => 'Contraseña incorrecta'

                    ]
                );
                return redirect()->route('digitalsignature');
            }
        }

        /* Certificado del firmante */
        $cert = Crypt::encryptString($certInfo['cert']);
        /* clave privada */
        $pkey = Crypt::encryptString($certInfo['pkey']);
        /* frase de paso cifrada */
        $passphraseCrypt = Crypt::encryptString($passphrase);

        /* objeto tipo Signprofile */
        $profile = new Signprofile();
        $profile->cert = $cert;
        $profile->pkey = $pkey;
        $profile->passphrase = $passphraseCrypt;
        $profile->user_id = Auth::user()->id;
        $profile->save();

        //Se borra el certificado viejo
        $userprofile->delete();

        //Se borra el archivo .p12
        Storage::disk('temporary')->delete($filename);
        $request->session()->flash(
            'message',
            [
                'type'     => 'store',
            ]
        );

        return redirect()->route('digitalsignature');
    }

    /**
     * Elimina el certificado del firmante
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy()
    {
        if (User::find(auth()->user()->id)->signprofiles) {
            /* usuario con certificado firmante */
            $userprofile = User::find(auth()->user()->id)->signprofiles;
            $userprofile->delete();
            session()->flash(
                'msg',
                [
                    'autohide' => 'true',
                    'type'     => 'success',
                    'title'    => 'Éxito',
                    'text'     => 'Registro eliminado con éxito.'
                ]
            );
            return redirect()->route('digitalsignature');
        }

        session()->flash(
            'msg',
            [
                'autohide' => 'true',
                'type'     => 'error',
                'title'    => 'Alerta',
                'text'     => 'El registro fue eliminado previamente.'
            ]
        );
        return redirect()->route('digitalsignature');
    }

    /**
     * Obtiene la información detallada del certificado del firmante
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @return array|void con la información del certificado firmante
     */
    public function getCertificate()
    {
        if (User::find(auth()->user()->id)->signprofiles) {
            /* usuario con certificado firmante */
            $userprofile = User::find(auth()->user()->id)->signprofiles;
            /* certificado del usuario */
            $certuser = Crypt::decryptString($userprofile['cert']);
            /* certficado firmante del usuario en una matriz para acceder a sus campos */
            $cert = openssl_x509_parse($certuser);

            $certificateDetails = (object) [
                'subjCountry' => $cert['subject']['C'],
                'subjState' => $cert['subject']['ST'],
                'subjLocality' => $cert['subject']['L'],
                'subjOrganization' => $cert['subject']['O'],
                'subjUnitOrganization' => $cert['subject']['OU'],
                'subjName' => $cert['subject']['CN'],
                'subjMail' => $cert['subject']['emailAddress'],
                'issCountry' => $cert['issuer']['C'],
                'issState' => $cert['issuer']['ST'],
                'issLocality' => $cert['issuer']['L'],
                'issOrganization' => $cert['issuer']['O'],
                'issUnitOrganization' => $cert['issuer']['OU'],
                'issName' => $cert['issuer']['CN'],
                'issMail' => $cert['issuer']['emailAddress'],
                'version' => $cert['version'],
                'serialNumber' => $cert['serialNumber'],
                'validFrom' => date('d-m-y H:i:s', $cert['validFrom_time_t']),
                'validTo' => date('d-m-y H:i:s', $cert['validTo_time_t']),
                'signatureTypeSN' => $cert['signatureTypeSN'],
                'signatureTypeLN' => $cert['signatureTypeLN'],
                'signatureTypeNID' => $cert['signatureTypeNID'],
            ];
            /* fecha de expiración del certificado firmante del usuario */
            $fecha = date('d-m-y H:i:s', $cert['validFrom_time_t']);

            return response()->json([
                'records' =>
                [
                    'certificateDetail' => $certificateDetails,
                    'cert' => 'true',
                    'certdetail' => 'true',
                    'Identidad' => $cert['subject']['CN'],
                    'Verificado' => $cert['issuer']['CN'],
                    'Caduca' => $fecha
                ]
            ], 200);
        }
    }

    /**
     * Realiza la firma electrónica de un documento
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View documento pdf firmado
     */
    public function signFile(Request $request)
    {
        $this->validate($request, [
            'pdf' => ['required', 'mimes:pdf']
        ]);

        if (Auth::user()) {
            if (User::find(auth()->user()->id)->signprofiles) {
                /* nombre aleatoria para asignar al documentos pdf */
                $filename = Str::random(10);
                /* nombre del documento pdf a firmar */
                $filenamepdf = $filename . '.pdf';
                /* certificado firmante del usuario en una matriz para acceder a sus campos */
                $path = $request->file('pdf')->storeAs('', $filenamepdf, 'temporary');
                /* nombre del documento pdf firmado */
                $filenamepdfsign = $filename . '-sign.pdf';
                /* objeto de tipo Helper */
                $getpath = new Helper();
                /* ruta del documento pdf firmado obtenido de una función del Helper */
                $storePdfSign = $getpath->getPathSign($filenamepdfsign);
                /* ruta del documento pdf a firmar obtenido de una función del Helper */
                $storePdf = $getpath->getPathSign($filenamepdf);


                /* certificado del firmante */
                $cert = Crypt::decryptString(User::find(auth()->user()->id)->signprofiles['cert']);
                /* clave privada para firmar */
                $pkey = Crypt::decryptString(User::find(auth()->user()->id)->signprofiles['pkey']);
                /* frase de paso del archivo .p12 */
                $passphrase = Crypt::decryptString(User::find(auth()->user()->id)->signprofiles['passphrase']);

                /* nombre del archivo .p12 */
                $filenamep12 = Str::random(10) . '.p12';
                /* ruta del certificado firmante */
                $storeCertificated = $getpath->getPathSign($filenamep12);
                /* archivo .p12 creado para la firma */
                $createpkcs12 = openssl_pkcs12_export_to_file($cert, $storeCertificated, $pkey, $passphrase);
                /* ruta del ejecutable PortableSigner para realizar la firma */
                $pathPortableSigner = $getpath->getPathSign('PortableSigner');

                /* comando para realizar el proceso de firma */
                $comand = 'java -jar ' . $pathPortableSigner . ' -n -t ' . $storePdf . ' -o ' . $storePdfSign . ' -s ' . $storeCertificated . ' -p ' . $passphrase;
                /* respuesta del proceso de firma electrónica */
                $run = exec($comand, $output);

                //enlace para descargar el documento PDF
                $pathDownload = asset('storage/temporary/' . $filenamepdfsign);
                $headers = array(
                    'Content-Type: application/pdf',
                );

                //elimina el certficado .p12
                Storage::disk('temporary')->delete($filenamep12);

                //elimina el documento pdf
                Storage::disk('temporary')->delete($filenamepdf);

                $previousUrl = app('url')->previous(); //obtiene el nombre de la ruta

                $routeAction = $request->route()->getName();

                return view('digitalsignature::viewSignfile', [
                    'msg' => "El documento fue firmado exitosamente",
                    'namefile' => $filenamepdfsign,
                    'signfile' => 'true'
                ]);
            }
            return redirect()->route('fileprofile');
        }
        return redirect()->route('login');
    }

    /**
     * Verifica la firma electrónica de un documento
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\View\View documento pdf firmado
     */
    public function verifySign(Request $request)
    {
        $this->validate($request, [
            'pdf' => ['required', 'mimes:pdf']
        ]);
        /* nombre aleatoria para asignar al documentos pdf */
        $filename = Str::random(10);
        /* nombre del documento pdf a verificar firma */
        $namepdfsign = $filename . '.pdf';
        /* ruta del documento a verificar firma */
        $path = $request->file('pdf')->storeAs('', $namepdfsign, 'temporary');
        /* objeto de tipo Helper */
        $getpath = new Helper();
        /* ruta del documento a verificar obtenido de una función del Helper */
        $storePdfSign = $getpath->getPathSign($namepdfsign);

        /* comando para realizar el proceso de firma */
        $comand = 'pdfsig ' . $storePdfSign;
        /* respuesta del proceso de firma electrónica */
        $run = exec($comand, $output);

        //elimina el documento pdf a verificar la firma electrónica
        Storage::disk('temporary')->delete($namepdfsign);


        if (count($output) == 1) {
            $infoVerify = array();
            array_push($infoVerify, "El documento seleccionado no contiene firma electrónica");
            $json_test = json_encode($infoVerify);
            return view('digitalsignature::viewVerifySignfile', ['verifyFile' => "true", 'json_test' => $json_test]);
        } else {
            $respVerify = new Helper();
            $json_test = json_encode($respVerify->getRespVerify($output));

            return view('digitalsignature::viewVerifySignfile', ['verifyFile' => "true", 'json_test' => $json_test]);
        }
    }

    /**
     * Lista los usuarios que asociado certificado firmante
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @return void
     */
    public function listCertificate()
    {

        $users = User::all();
        $userlist = [];

        foreach ($users as $user) {
            $profile = User::find($user->id);
            if ($profile->signprofiles) {
                print_r('############');
                print_r($user->name);
                print_r('############');
                print_r($user->email);
                print_r('############');
                print_r(Crypt::decryptString($profile->signprofiles['cert']));
            }
        }
    }

    /**
     * Función que descargar documentos firmado
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @param string $filename nombre del documento firmado
     *
     * @return BinaryFileResponse enlace para descargar la documento PDF firmado
     */
    public function getFile($filename)
    {
        return response()->download(storage_path("temporary/{$filename}"));
    }

    /**
     * Realiza la firma electrónica de un documento para los componentes
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @param Request $request datos de la petición
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function signFileApi(Request $request)
    {
        if (Auth::user()) { //usuario autenticado
            // Si tiene un certificado firmante almacenado
            if (User::find(auth()->user()->id)->signprofiles) {
                if ($request->file('pdf')) {
                    //Documento pdf
                    $filename = Str::random(10);
                    $filenamepdf = $filename . '.pdf';
                    $path = $request->file('pdf')->storeAs('', $filenamepdf, 'temporary');
                    $filenamepdfsign = $filename . '-sign.pdf';
                    $getpath = new Helper();
                    $storePdfSign = $getpath->getPathSign($filenamepdfsign);
                    $storePdf = $getpath->getPathSign($filenamepdf);


                    //Crear archivo pkcs#12
                    $cert = Crypt::decryptString(User::find(auth()->user()->id)->signprofiles['cert']);
                    $pkey = Crypt::decryptString(User::find(auth()->user()->id)->signprofiles['pkey']);
                    //$passphrase = Str::random(10);
                    $passphrase = Crypt::decryptString(User::find(auth()->user()->id)->signprofiles['passphrase']);

                    //Datos para la firma
                    $filenamep12 = Str::random(10) . '.p12';
                    $storeCertificated = $getpath->getPathSign($filenamep12);
                    $createpkcs12 = openssl_pkcs12_export_to_file($cert, $storeCertificated, $pkey, $passphrase);
                    $pathPortableSigner = $getpath->getPathSign('PortableSigner');

                    //ejecución del comando para firmar
                    $comand = 'java -jar ' . $pathPortableSigner . ' -n -t ' . $storePdf . ' -o ' . $storePdfSign . ' -s ' . $storeCertificated . ' -p ' . $passphrase;
                    $run = exec($comand, $output);

                    //enlace para descargar el documento PDF
                    $pathDownload = asset('storage/temporary/' . $filenamepdfsign);
                    $headers = array(
                        'Content-Type: application/pdf',
                    );

                    //elimina el certficado .p12
                    Storage::disk('temporary')->delete($filenamep12);

                    //elimina el documento pdf
                    Storage::disk('temporary')->delete($filenamepdf);

                    $previousUrl = app('url')->previous(); //obtiene el nombre de la ruta

                    $routeAction = $request->route()->getName();

                    return response()->json([
                        'msg' => "El documento fue firmado exitosamente",
                        'namefile' => $filenamepdfsign,
                        'signfile' => 'true'
                    ]);
                }
                return response()->json(['msg' => "Seleccione un documento PDF"]);
            }
            return redirect()->route('fileprofile');
        }

        return redirect()->route('login');
    }

    /**
     * Verifica la firma electrónica de un documento pdf para los componentes
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @param Request $request datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifySignApi(Request $request)
    {
        $this->validate($request, [
            'pdf' => ['required', 'mimes:pdf']
        ]);

        //Documento pdf
        $filename = Str::random(10);
        $namepdfsign = $filename . '.pdf';
        $path = $request->file('pdf')->storeAs('', $namepdfsign, 'temporary');

        $getpath = new Helper();
        $storePdfSign = $getpath->getPathSign($namepdfsign);

        //ejecución del comando para firmar
        $comand = 'pdfsig ' . $storePdfSign;
        $run = exec($comand, $output);

        //elimina el documento pdf a verificar la firma electrónica
        Storage::disk('temporary')->delete($namepdfsign);


        if (count($output) == 1) {
            $infoVerify = array();
            array_push($infoVerify, 'El documento seleccionado no contiene firma electrónica');
            $records = json_encode($infoVerify, JSON_UNESCAPED_UNICODE);

            return response()->json(['verifyFile' => "false", 'records' => $records]);
        }

        $respVerify = new Helper();
        $records = json_encode($respVerify->getRespVerify($output), JSON_UNESCAPED_UNICODE);

        return response()->json(['verifyFile' => "true", 'records' => $records]);
    }

    /**
     * Metodo para validar la autenticación del usuario y autorizar la ejecución
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @param string $passphrase contraseña de la firma
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function validateAuth($passphrase)
    {
        if (Auth::user()) {
            if (User::find(auth()->user()->id)->signprofiles) {
                $passphraseOrigin = Crypt::decryptString(User::find(auth()->user()->id)->signprofiles['passphrase']);

                if ($passphrase == $passphraseOrigin) {
                    return response()->json([
                        'authorization' => 'true',
                        'msg' => "Autenticación validada"
                    ]);
                } else {
                    return response()->json([
                        'validate' => 'false',
                        'msg' => "Autenticación invalidada"
                    ]);
                }
            }
            return redirect()->route('fileprofile');
        }
        return redirect()->route('login');
    }

    /**
     * Metodo para validar la autenticación del usuario
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @param Request $request datos de la petición
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function validateAuthApi(Request $request)
    {
        if (Auth::user()) {
            if (User::find(auth()->user()->id)->signprofiles) {
                $passphrase = $request->get('passphrase');
                $passphraseOrigin = Crypt::decryptString(User::find(auth()->user()->id)->signprofiles['passphrase']);

                if ($passphrase == $passphraseOrigin) {
                    return response()->json([
                        'auth' => true,
                        'msg' => "Autenticación validada"
                    ]);
                }
                return response()->json([
                    'auth' => false,
                    'msg' => "La contraseña del certificado no concuerda con la guardada"
                ]);
            }
            return redirect()->route('fileprofile');
        }
        return redirect()->route('login');
    }
}
