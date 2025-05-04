<?php

namespace Modules\Finance\Http\Controllers;

use DateTime;
use stdClass;
use Exception;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\FinanceBankingMovement;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Finance\Models\FinanceSettingBankReconciliationFiles;

/**
 * @class FinanceConciliationController
 *
 * @brief Gestión de Finanzas > Banco > Conciliación.
 *
 * Clase que gestiona lo referente a Conciliaciones bancarias.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class FinanceConciliationController extends Controller
{
    use ValidatesRequests;

    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:finance.settingbankreconciliationfiles.index', ['only' => 'index', 'vueList']);
    }
    /**
     * Muestra la plantilla del módulo Finanzas > Banco > Conciliación.
     *
     * @method index
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('finance::conciliation.index');
    }

    public function store(Request $request)
    {
    }

    public function show()
    {
    }

    public function update(Request $request, $id)
    {
    }

    public function destroy($id)
    {
    }

    /**
     * Método que retorna el delimitador usado un archiv .csv
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param string $filePath
     *
     * @param array $delimiters
     *
     * @return string
     */
    public function guessDelimiter(string $filePath, array $delimiters = [';', ',', "\t"]): string
    {
        $file = fopen($filePath, 'r');

        $firstLine = fgets($file);

        fclose($file);

        $ns = [];
        foreach ($delimiters as $delimiter) {
            $ns[$delimiter] = count(explode($delimiter, $firstLine));
        }

        return collect($ns)->sort()->reverse()->keys()->first();
    }


    /**
     * Método que obtiene el separador usado en el formato de monto
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param array $csvData
     *
     * @param array $separators
     *
     * @return void
     */
    public function guessDecimalSeparator(array $csvData, array $separators = ['.', ','])
    {
        $ns = [];
        foreach ($separators as $separator) {
            for ($i = 1; $i < count($csvData); $i++) {
                $ns[$separator] = count(explode($separator, $csvData[$i][5]));
            }
        }
        $separator = collect($ns)->sort()->reverse()->keys()->first();
        return $separator;
    }

    /**
     * Método que obtiene el array de validación para el archivo de configuración
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param $bank_id Id del banco asociado a la cuenta bancaria
     *
     * @return array
     */
    public function getFileValidationArray($bank_id)
    {
        $fileStructure = FinanceSettingBankReconciliationFiles::where('bank_id', $bank_id)->first()?->toArray();

        return $fileStructure;
    }

    /**
     * Método que consigue el delimitador usado en el archivo csv/.txt
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param $delimiter Delimitador usado por el archivo csv/.txt
     *
     * @return string
     */
    public function getFileConfigurationDelimiter($delimiter, $fileDelimiters = [';' => 'Punto y Coma', "\t" => 'Tabulador', ',' => 'Coma'])
    {
        return $fileDelimiters[$delimiter];
    }

    /**
     * Método que consigue el separador de decimales usado en el archivo csv/.txt
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param $separator Separador usado por el archivo csv/.txt
     *
     * @return string
     */
    public function getFileConfigurationDecimalSeparator($separator, $fileDelimiters = [',' => 'Coma', "." => 'Punto'])
    {
        return $fileDelimiters[$separator];
    }

    /**
     * Método que valida la estructura del archivo usado para realizar la conciliación bancaria
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param [stream] $file
     *
     * @return array|object
     */
    public function validateFileStructure($file, $bank_id)
    {
        // Encontrar el archivo de configuración para el banco asociado a la cuenta bancaria
        $fileStructure = $this->getFileValidationArray($bank_id);

        // Si no existe, lanzar error de validación
        throw_if(
            is_null($fileStructure),
            'El banco asociado a la cuenta bancaria no tiene una configuración para el archivo ' .
            'de conciliación bancaria. Por favor registre una configuración.'
        );

        // Leer archivo csv/.txt
        $filePath = $file->getRealPath();

        // Delimitador del archivo csv/.txt
        $delimiter = $this->guessDelimiter($filePath);
        // Obtener el string correspodiente a los delimitadores ';' | ',' | '\t'
        $translatedDelimiter = $this->getFileConfigurationDelimiter($delimiter);

        // Si el delimitador del archivo csv/.txt no coincide con el del archivo de configuración, lanzar error de validación
        throw_if(
            $translatedDelimiter != $fileStructure['separated_by'],
            'El archivo no se encuentra separado por el separador indicado en la configuración'
        );


        $csvData = [];


        if (($open = fopen($filePath, "r")) !== false) {
            if ($fileStructure["read_start_line"] == true) {
                fgetcsv($open, 700, $delimiter);
            }

            while (($data = fgetcsv($open, 700, $delimiter)) !== false) {
                $csvData[] = is_array($data) ? trim($data[0]) : trim($data);
            }

            if ($fileStructure["read_end_line"] == false) {
                array_pop($csvData);
            }

            fclose($open);
        }

        $decimalSeparator = $this->guessDecimalSeparator($csvData);
        $translatedDecimalSeparator = $this->getFileConfigurationDecimalSeparator($decimalSeparator);

        throw_if(
            $translatedDecimalSeparator != $fileStructure['decimal_separator'],
            'El archivo no esta usando el separador de decimales indicado en la configuración'
        );
        return $csvData;
    }

    /**
     * Método que devuelve el id del banco al que pertenece una cuenta bancaria
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param [type] $account_id
     *
     * @return int $id id del banco al que pertenece una cuenta bancaria
     */
    public function getBankId($account_id)
    {
        $id = DB::table('finance_banks')
            ->join('finance_bank_accounts', 'finance_banks.id', '=', 'finance_bank_accounts.finance_bank_id')
            ->where('finance_bank_accounts.id', '=', $account_id)
            ->select('finance_banks.id')
            ->first()->id;

        return $id;
    }

    /**
     * Método que encuentra y retorna los movimientos de una cuenta bancaria que cumplen con parámetros de busqueda
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getBankAccountConciliationInfo(Request $request)
    {
        $this->validate($request, [
            'account_id' => ['required'],
            'month' => ['required'],
            'year' => ['required'],
            'file' => ['required', 'mimes:csv,txt'],
        ], [], ['account_id' => 'cuenta bancaria', 'file' => 'archivo']);
        $bank_id = $this->getBankId($request->account_id);
        $bankMovements = [];

        if (!empty($request->files) && $request->hasFile('file')) {
            $structure = FinanceSettingBankReconciliationFiles::where('bank_id', $bank_id)->first()?->toArray();

            try {
                $csvData = $this->validateFileStructure($request->file('file'), $bank_id);
                foreach ($csvData as $key => $row) {
                    $row = preg_replace('/\s+/', ' ', $row);
                    $bankMovements[] = explode(' ', $row);
                    if ($key === 2) {
                        dd(count($row));
                    }
                }
            } catch (\Throwable $th) {
                return response()->json(['errors' => ['file' => [
                    'Se encontraron errores en el formato del archivo suministrado con respecto a la configuración ' .
                    'indicada para el archivo.'
                ]]], 422);
            }
        }

        $accountMovements = FinanceBankingMovement::where('finance_bank_account_id', $request->account_id)
            ->whereYear('payment_date', $request->year)
            ->whereMonth('payment_date', $request->month)->get();
        return response()->json([
            'result' => true,
            'movements' => $accountMovements,
            'bankMovements' => $bankMovements
        ], 200);
    }

    /**
     * Obtiene los datos de la organización asociada al usuario autenticado o en
     * su defecto, la organización activa y por defecto.
     *
     * @method  getInstitution
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Institution $institution Objeto con información asociada a un organismo
     *
     * @return JsonResponse JSON con información del organismo
     */
    public function getInstitution()
    {
        if (isset(auth()->user()->profile)) {
            if (isset(auth()->user()->profile->institution_id)) {
                $institution = Institution::where(['id' => auth()->user()->profile->institution_id])->first();
            } else {
                $institution = Institution::where(['active' => true, 'default' => true])->first();
            }
        } else {
            $institution = Institution::where(['active' => true, 'default' => true])->first();
        }
        $inst = Institution::where('id', $institution->id)->with(['municipality' => function ($q) {
            return $q->with(['estate' => function ($qq) {
                return $qq->with('country');
            }]);
        }, 'banner', 'logo'])->first();

        return response()->json(['result' => true, 'institution' => $inst], 200);
    }
}
