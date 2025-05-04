<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use Illuminate\Routing\Controller;
use Modules\Accounting\Models\AccountingReportHistory;
use Modules\Accounting\Models\AccountingAccount;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\ExchangeRate;
use Modules\Accounting\Models\Currency;
use Modules\Accounting\Models\Institution;
use Modules\Accounting\Models\Profile;
use Modules\Accounting\Models\Setting;
use App\Repositories\ReportRepository;
use Modules\DigitalSignature\Repositories\ReportRepositorySign;
use DateTime;
use Auth;

/**
 * @class AccountingReportPdfAuxiliaryBookController
 * @brief Controlador para la generación del reporte de libro auxiliar
 *
 * Clase que gestiona el reporte de libro auxiliar
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
 * @copyright <a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *                LICENCIA DE SOFTWARE CENDITEL</a>
 */
class AccountingAuxiliaryBookController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:accounting.report.auxiliarybook', [
            'only' => [
                'index',
                'pdf',
                'pdfVue',
                'pdfSign',
                'pdfVueSign'
            ]
        ]);
    }

    /**
     * [pdfVue verifica las conversiones monetarias de un reporte de libro auxiliar]
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     * @param string $account_id [variable con el id de la cuenta]
     * @param string $date       [fecha para la generación de reporte, formato 'YYYY-mm']
     * @param Currency $currency moneda en que se expresara el reporte
     */
    public function pdfVue($date, Currency $currency, $account_id = null)
    {

        /**
         * [$initDate fecha inicial de busqueda]
         * @var string
         */
        $initDate = $date . '-01';

        /**
         * [$day ultimo dia correspondiente al mes]
         * @var date
         */
        $day = date('d', (mktime(0, 0, 0, explode('-', $date)[1] + 1, 1, explode('-', $date)[0]) - 1));

        /**
         * [$endDate fecha final de busqueda]
         * @var string
         */
        $endDate     = $date . '-' . $day;

        $institution_id = null;

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        $convertions = [];

        if (!$account_id) {
            /**
             * [$query cuenta patrimonial con su relacion en asientos contables]
             * @var [Modules\Accounting\Models\AccountingEntry]
             */
            $query = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                }])
            ->where('group', '>', 0)
            ->where('subgroup', '>', 0)
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')->get();

            foreach ($query as $account) {
                /*
                 * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                 */
                foreach ($account['entryAccount'] as $entryAccount) {
                    $inRange = false;
                    if ($entryAccount['entries']) {
                        if (
                            !array_key_exists(
                                $entryAccount['entries']['currency']['id'],
                                $convertions
                            )
                        ) {
                            $convertions = $this->calculateExchangeRates(
                                $convertions,
                                $entryAccount['entries'],
                                $currency['id']
                            );
                        }
                        foreach ($convertions as $convertion) {
                            foreach ($convertion as $convert) {
                                if (
                                    $entryAccount['entries']['from_date'] >= $convert['start_at'] &&
                                    $entryAccount['entries']['from_date'] <= $convert['end_at']
                                ) {
                                    $inRange = true;
                                }
                            }
                        }

                        if (
                            !array_key_exists($entryAccount['entries']['currency']['id'], $convertions)
                                    && $entryAccount['entries']['currency']['id'] != $currency['id']
                        ) {
                            return response()->json([
                                    'result'  => false,
                                    'message' => 'Imposible expresar saldos de ' .
                                                 $entryAccount['entries']['currency']['symbol']
                                                 . ' (' . $entryAccount['entries']['currency']['name'] . ')'
                                                 . ' a ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                                                 ', verificar tipos de cambio configurados.' .
                                                 ' Para la fecha de ' .
                                                 $entryAccount['entries']['from_date'],
                              ], 200);
                        } elseif (!$inRange) {
                            if ($entryAccount['entries']['currency']['id'] != $currency['id']) {
                                return response()->json([
                                    'result'  => false,
                                    'message' => 'Imposible expresar saldos de ' .
                                                 $entryAccount['entries']['currency']['symbol']
                                                 . ' (' . $entryAccount['entries']['currency']['name'] . ')'
                                                 . ' a ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                                                 ', verificar tipos de cambio configurados.' .
                                                 ' Para la fecha de ' .
                                                 $entryAccount['entries']['from_date'],
                                ], 200);
                            }
                        }
                    }
                }
            }
        } elseif ($account_id) {
            $account = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                }])->find($account_id);

            $accountData = $account->toArray();

            if ($accountData['entry_account'] == [] && explode('.', $accountData['code'])[6] == '000') {
                // Cuenta auxiliar que es padre
                $account = AccountingAccount::with([
                    'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                        if ($institution_id) {
                            if (
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id)
                            ) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                            }
                        } else {
                            if ($is_admin) {
                                if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                    $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                                }
                            }
                        }
                    },
                    'children.entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                        if ($institution_id) {
                            if (
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id)
                            ) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                            }
                        } else {
                            if ($is_admin) {
                                if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                    $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                                }
                            }
                        }
                    },
                    'children.children.entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                        if ($institution_id) {
                            if (
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id)
                            ) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                            }
                        } else {
                            if ($is_admin) {
                                if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                    $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                                }
                            }
                        }
                    },
                    'children.children.children.entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                        if ($institution_id) {
                            if (
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id)
                            ) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                            }
                        } else {
                            if ($is_admin) {
                                if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                    $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                                }
                            }
                        }
                    },
                    'children.children.children.children.entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                        if ($institution_id) {
                            if (
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id)
                            ) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                            }
                        } else {
                            if ($is_admin) {
                                if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                    $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                                }
                            }
                        }
                    },
                    'children.children.children.children.children.entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                        if ($institution_id) {
                            if (
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id)
                            ) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                            }
                        } else {
                            if ($is_admin) {
                                if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                    $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                                }
                            }
                        }
                    },])->find($account_id);
            } else if ($accountData['entry_account'] == [] && explode('.', $accountData['code'])[6] != '000') {
                    return response()->json(
                        [
                        'result' => false,
                        'message' => 'No se ha encontrado ningún registro de Débito o Crédito en la fecha establecida.',
                        ],
                        200
                    );
            }

            $accountArr = $account->toArray();

            if (!array_key_exists('children', $accountArr)) {
                /* Cuenta que solamente es HIJA (1.1.1.01.01.01.000) */
                $acc[0] = [
                    'id'             => $account['id'],
                ];
                if ($acc == []) {
                    return response()->json(
                        [
                        'result' => false,
                        'message' => 'No se ha encontrado ningún registro de Débito o Crédito en la fecha establecida.',
                        ],
                        200
                    );
                }
            } else {
                if (explode('.', $accountArr['code'])[6] == '000' && explode('.', $accountArr['code'])[5] != '00') {
                    /* Cuenta que es HIJA, PADRE de HIJAS (1.1.1.01.01.01.000) */
                    $cont = 0;
                    $accData = [];
                    for ($childrenLenght=0; ; $childrenLenght++) {
                        if ($childrenLenght == count($accountArr['children'])) {
                            break;
                        }
                        /*
                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                         */
                        foreach ($account['children'][$childrenLenght]['entryAccount'] as $entryAccount) {
                            if ($entryAccount['entries']) {
                                array_push($accData, [
                                    'id'           => $account['children'][$childrenLenght]['id'],
                                ]);
                            }
                        }
                    }
                    if ($accData == []) {
                        return response()->json(
                            [
                            'result' => false,
                            'message' => 'No se ha encontrado ningún registro de Débito o Crédito en la fecha establecida.',
                            ],
                            200
                        );
                    }
                } else if (explode('.', $accountArr['code'])[5] == '00' && explode('.', $accountArr['code'])[4] != '00') {
                    /* Cuenta que es HIJA, PADRE de PADRES con HIJAS (1.1.1.01.01.00.000) */
                    $cont = 0;
                    $accData = [];
                    foreach ($accountArr['children'] as $accArrChildren) {
                        for ($childrenCLenght=0; ; $childrenCLenght++) {
                            if ($childrenCLenght == count($accountArr['children'])) {
                                break;
                            }
                            /*
                             * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                             */
                            foreach ($account['children'][$childrenCLenght]['entryAccount'] as $entryAccount) {
                                if ($entryAccount['entries']) {
                                    array_push($accData, [
                                        'id'           => $account['children'][$childrenCLenght]['id'],
                                    ]);
                                }
                            }
                            for ($childrenLenght=0; ; $childrenLenght++) {
                                if ($childrenLenght == count($accArrChildren['children'])) {
                                    break;
                                }
                                if (
                                    array_key_exists($childrenCLenght, $accountArr['children']) &&
                                    array_key_exists($childrenLenght, $accountArr['children'][$childrenCLenght]['children'])
                                ) {
                                    /*
                                     * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                     */
                                    foreach ($account['children'][$childrenCLenght]['children'][$childrenLenght]['entryAccount'] as $entryAccount) {
                                        if ($entryAccount['entries']) {
                                            array_push($accData, [
                                                'id'           => $account['children'][$childrenCLenght]['children'][$childrenLenght]['id'],
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($accData == []) {
                        return response()->json(
                            [
                            'result' => false,
                            'message' => 'No se ha encontrado ningún registro de Débito o Crédito en la fecha establecida.',
                            ],
                            200
                        );
                    }
                } else if (explode('.', $accountArr['code'])[4] == '00' && explode('.', $accountArr['code'])[3] != '00') {
                    /* Cuenta que es HIJA, PADRE de PADRES que son PADRES de HIJAS (1.1.1.01.00.00.000) */
                    $cont = 0;
                    $accData = [];
                    for ($childrenCCLenght=0; ; $childrenCCLenght++) {
                        if ($childrenCCLenght == count($accountArr['children'])) {
                            break;
                        }
                        /*
                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                         */
                        foreach ($account['children'][$childrenCCLenght]['entryAccount'] as $entryAccount) {
                            if ($entryAccount['entries']) {
                                array_push($accData, [
                                    'id'           => $account['children'][$childrenCCLenght]['id'],
                                ]);
                            }
                        }
                        for ($childrenCLenght=0; ; $childrenCLenght++) {
                            if ($childrenCLenght == count($accountArr['children'][$childrenCCLenght]['children'])) {
                                break;
                            }
                            if (
                                array_key_exists($childrenCCLenght, $accountArr['children']) &&
                                array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCLenght]['children'])
                            ) {
                                /*
                                 * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                 */
                                foreach ($account['children'][$childrenCCLenght]['children'][$childrenCLenght]['entryAccount'] as $entryAccount) {
                                    if ($entryAccount['entries']) {
                                        array_push($accData, [
                                            'id'           => $account['children'][$childrenCCLenght]['children'][$childrenCLenght]['id'],
                                        ]);
                                    }
                                }
                            }
                            for ($childrenLenght=0; ; $childrenLenght++) {
                                if ($childrenLenght == count($accountArr['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])) {
                                    break;
                                }
                                if (
                                    array_key_exists($childrenCCLenght, $accountArr['children']) &&
                                    array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCLenght]['children']) &&
                                    array_key_exists($childrenLenght, $accountArr['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])
                                ) {
                                    /*
                                     * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                     */
                                    foreach ($account['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['entryAccount'] as $entryAccount) {
                                        if ($entryAccount['entries']) {
                                            array_push($accData, [
                                                'id'           => $account['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['id'],
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($accData == []) {
                        return response()->json(
                            [
                            'result' => false,
                            'message' => 'No se ha encontrado ningún registro de Débito o Crédito en la fecha establecida.',
                            ],
                            200
                        );
                    }
                } else if (explode('.', $accountArr['code'])[3] == '00' && explode('.', $accountArr['code'])[2] != '0') {
                    /* Cuenta que es HIJA, PADRE de PADRES que son PADRES con HIJAS que tienen HIJAS (1.1.1.00.00.00.000) */
                    $cont = 0;
                    $accData = [];
                    for ($childrenCCCLenght=0; ; $childrenCCCLenght++) { 
                        if ($childrenCCCLenght == count($accountArr['children'])) {
                            break;
                        }
                        /*
                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                         */
                        foreach ($account['children'][$childrenCCCLenght]['entryAccount'] as $entryAccount) {
                            if ($entryAccount['entries']) {
                                array_push($accData, [
                                    'id'           => $account['children'][$childrenCCCLenght]['id'],
                                ]);
                            }
                        }
                        for ($childrenCCLenght=0; ; $childrenCCLenght++) {
                            if ($childrenCCLenght == count($accountArr['children'][$childrenCCCLenght]['children'])) {
                                break;
                            }
                            if (
                                array_key_exists($childrenCCCLenght, $accountArr['children']) &&
                                array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCLenght]['children'])
                            ) {
                                /*
                                 * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                 */
                                foreach ($account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['entryAccount'] as $entryAccount) {
                                    if ($entryAccount['entries']) {
                                        array_push($accData, [
                                            'id'           => $account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['id'],
                                        ]);
                                    }
                                }
                            }
                            for ($childrenCLenght=0; ; $childrenCLenght++) {
                                if ($childrenCLenght == count($accountArr['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'])) {
                                    break;
                                }
                                if (
                                    array_key_exists($childrenCCCLenght, $accountArr['children']) &&
                                    array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCLenght]['children']) &&
                                    array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'])
                                ) {
                                    /*
                                     * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                     */
                                    foreach ($account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['entryAccount'] as $entryAccount) {
                                        if ($entryAccount['entries']) {
                                            array_push($accData, [
                                                'id'           => $account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['id'],
                                            ]);
                                        }
                                    }
                                }
                                for ($childrenLenght=0; ; $childrenLenght++) {
                                    if ($childrenLenght == count($accountArr['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])) {
                                        break;
                                    }
                                    if (
                                        array_key_exists($childrenCCCLenght, $accountArr['children']) &&
                                        array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCLenght]['children']) &&
                                        array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children']) &&
                                        array_key_exists($childrenLenght, $accountArr['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])
                                    ) {
                                        /*
                                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                         */
                                        foreach ($account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['entryAccount'] as $entryAccount) {
                                            if ($entryAccount['entries']) {
                                                array_push($accData, [
                                                    'id'           => $account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['id'],
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($accData == []) {
                        return response()->json(
                            [
                            'result' => false,
                            'message' => 'No se ha encontrado ningún registro de Débito o Crédito en la fecha establecida.',
                            ],
                            200
                        );
                    }
                } else if (explode('.', $accountArr['code'])[2] == '0' && explode('.', $accountArr['code'])[1] != '0') {
                    /* Cuenta que es HIJA, PADRE de PADRES que son PADRES de otros PADRES con HIJAS que tienen HIJAS (1.1.0.00.00.00.000) */
                    $cont = 0;
                    $accData = [];
                    for ($childrenCCCCLenght=0; ; $childrenCCCCLenght++) { 
                        if ($childrenCCCCLenght == count($accountArr['children'])) {
                            break;
                        }
                        /*
                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                         */
                        foreach ($account['children'][$childrenCCCCLenght]['entryAccount'] as $entryAccount) {
                            if ($entryAccount['entries']) {
                                array_push($accData, [
                                    'id'           => $account['children'][$childrenCCCCLenght]['id'],
                                ]);
                            }
                        }
                        for ($childrenCCCLenght=0; ; $childrenCCCLenght++) { 
                            if ($childrenCCCLenght == count($accountArr['children'][$childrenCCCCLenght]['children'])) {
                                break;
                            }
                            if (
                                array_key_exists($childrenCCCCLenght, $accountArr['children']) &&
                                array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCLenght]['children'])
                            ) {
                                /*
                                 * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                 */
                                foreach ($account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['entryAccount'] as $entryAccount) {
                                    if ($entryAccount['entries']) {
                                        array_push($accData, [
                                            'id'           => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['id'],
                                        ]);
                                    }
                                }
                            }
                            for ($childrenCCLenght=0; ; $childrenCCLenght++) {
                                if ($childrenCCLenght == count($accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'])) {
                                    break;
                                }
                                if (
                                    array_key_exists($childrenCCCCLenght, $accountArr['children']) &&
                                    array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCLenght]['children']) &&
                                    array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'])
                                ) {
                                    /*
                                     * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                     */
                                    foreach ($account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['entryAccount'] as $entryAccount) {
                                        if ($entryAccount['entries']) {
                                            array_push($accData, [
                                                'id'           => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['id'],
                                            ]);
                                        }
                                    }
                                }
                                for ($childrenCLenght=0; ; $childrenCLenght++) {
                                    if ($childrenCLenght == count($accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'])) {
                                        break;
                                    }
                                    if (
                                        array_key_exists($childrenCCCCLenght, $accountArr['children']) &&
                                        array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCLenght]['children']) &&
                                        array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children']) &&
                                        array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'])
                                    ) {
                                        /*
                                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                         */
                                        foreach ($account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['entryAccount'] as $entryAccount) {
                                            if ($entryAccount['entries']) {
                                                array_push($accData, [
                                                    'id'           => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['id'],
                                                ]);
                                            }
                                        }
                                    }
                                    for ($childrenLenght=0; ; $childrenLenght++) {
                                        if ($childrenLenght == count($accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])) {
                                            break;
                                        }
                                        if (
                                            array_key_exists($childrenCCCCLenght, $accountArr['children']) &&
                                            array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCLenght]['children']) &&
                                            array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children']) &&
                                            array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children']) &&
                                            array_key_exists($childrenLenght, $accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])
                                        ) {
                                            /*
                                             * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                             */
                                            foreach ($account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['entryAccount'] as $entryAccount) {
                                                if ($entryAccount['entries']) {
                                                    array_push($accData, [
                                                        'id'           => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['id'],
                                                    ]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($accData == []) {
                        return response()->json(
                            [
                            'result' => false,
                            'message' => 'No se ha encontrado ningún registro de Débito o Crédito en la fecha establecida.',
                            ],
                            200
                        );
                    }
                } else if (explode('.', $accountArr['code'])[1] == '0') {
                    /* Cuenta que es PADRE de PADRES (1.0.0.00.00.00.000) */
                    $accData = [];
                    for ($childrenCCCCCLenght=0; ; $childrenCCCCCLenght++) { 
                        if ($childrenCCCCCLenght == count($accountArr['children'])) {
                            break;
                        }
                        /*
                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                         */
                        foreach ($account['children'][$childrenCCCCCLenght]['entryAccount'] as $entryAccount) {
                            if ($entryAccount['entries']) {
                                array_push($accData, [
                                    'id'           => $account['children'][$childrenCCCCCLenght]['id'],
                                ]);
                            }
                        }
                        for ($childrenCCCCLenght=0; ; $childrenCCCCLenght++) {
                            if ($childrenCCCCLenght == count($accountArr['children'][$childrenCCCCCLenght]['children'])) {
                                break;
                            }
                            if (
                                array_key_exists($childrenCCCCCLenght, $accountArr['children']) &&
                                array_key_exists($childrenCCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'])
                            ) {
                                /*
                                 * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                 */
                                foreach ($account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['entryAccount'] as $entryAccount) {
                                    if ($entryAccount['entries']) {
                                        array_push($accData, [
                                            'id'           => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['id'],
                                        ]);
                                    }
                                }
                            }
                            for ($childrenCCCLenght=0; ; $childrenCCCLenght++) { 
                                if ($childrenCCCLenght == count($accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'])) {
                                    break;
                                }
                                if (
                                    array_key_exists($childrenCCCCCLenght, $accountArr['children']) &&
                                    array_key_exists($childrenCCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children']) &&
                                    array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'])
                                ) {
                                    /*
                                     * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                     */
                                    foreach ($account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['entryAccount'] as $entryAccount) {
                                        if ($entryAccount['entries']) {
                                            array_push($accData, [
                                                'id'           => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['id'],
                                            ]);
                                        }
                                    }
                                }
                                for ($childrenCCLenght=0; ; $childrenCCLenght++) {
                                    if ($childrenCCLenght == count($accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'])) {
                                        break;
                                    }
                                    if (
                                        array_key_exists($childrenCCCCCLenght, $accountArr['children']) &&
                                        array_key_exists($childrenCCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children']) &&
                                        array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children']) &&
                                        array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'])
                                    ) {
                                        /*
                                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                         */
                                        foreach ($account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['entryAccount'] as $entryAccount) {
                                            if ($entryAccount['entries']) {
                                                array_push($accData, [
                                                    'id'           => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['id'],
                                                ]);
                                            }
                                        }
                                    }
                                    for ($childrenCLenght=0; ; $childrenCLenght++) {
                                        if ($childrenCLenght == count($accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'])) {
                                            break;
                                        }
                                        if (
                                            array_key_exists($childrenCCCCCLenght, $accountArr['children']) &&
                                            array_key_exists($childrenCCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children']) &&
                                            array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children']) &&
                                            array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children']) &&
                                            array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'])
                                        ) {
                                            /*
                                             * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                             */
                                            foreach ($account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['entryAccount'] as $entryAccount) {
                                                if ($entryAccount['entries']) {
                                                    array_push($accData, [
                                                        'id'           => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['id'],
                                                    ]);
                                                }
                                            }
                                        }
                                        for ($childrenLenght=0; ; $childrenLenght++) {
                                            if ($childrenLenght == count($accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])) {
                                                break;
                                            }
                                            if (
                                                array_key_exists($childrenCCCCCLenght, $accountArr['children']) &&
                                                array_key_exists($childrenCCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children']) &&
                                                array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children']) &&
                                                array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children']) &&
                                                array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children']) &&
                                                array_key_exists($childrenLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])
                                            ) {
                                                /*
                                                 * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                                 */
                                                foreach ($account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['entryAccount'] as $entryAccount) {
                                                    if ($entryAccount['entries']) {
                                                        array_push($accData, [
                                                            'id'           => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['id'],
                                                        ]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($accData == []) {
                        return response()->json(
                            [
                            'result' => false,
                            'message' => 'No se ha encontrado ningún registro de Débito o Crédito en la fecha establecida.',
                            ],
                            200
                        );
                    }
                }
            }

            /*
             * Se recorre y evalua la relacion en las conversiones necesarias a realizar
             */
            foreach ($account['entryAccount'] as $entryAccount) {
                $inRange = false;
                if ($entryAccount['entries']) {
                    if (
                        !array_key_exists(
                            $entryAccount['entries']['currency']['id'],
                            $convertions
                        )
                    ) {
                        $convertions = $this->calculateExchangeRates(
                            $convertions,
                            $entryAccount['entries'],
                            $currency['id']
                        );
                    }

                    foreach ($convertions as $convertion) {
                        foreach ($convertion as $convert) {
                            if (
                                $entryAccount['entries']['from_date'] >= $convert['start_at'] &&
                                $entryAccount['entries']['from_date'] <= $convert['end_at']
                            ) {
                                $inRange = true;
                            }
                        }
                    }

                    if (
                        !array_key_exists($entryAccount['entries']['currency']['id'], $convertions)
                        && $entryAccount['entries']['currency']['id'] != $currency['id']
                    ) {
                        return response()->json([
                            'result'  => false,
                            'message' => 'Imposible expresar saldos de ' .
                                         $entryAccount['entries']['currency']['symbol']
                                         . ' (' . $entryAccount['entries']['currency']['name'] . ')'
                                         . ' a ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                                         ', verificar tipos de cambio configurados.' .
                                         ' Para la fecha de ' .
                                         $entryAccount['entries']['from_date'],
                        ], 200);
                    } elseif (!$inRange) {
                        if ($entryAccount['entries']['currency']['id'] != $currency['id']) {
                            return response()->json([
                                'result'  => false,
                                'message' => 'Imposible expresar saldos de ' .
                                             $entryAccount['entries']['currency']['symbol']
                                             . ' (' . $entryAccount['entries']['currency']['name'] . ')'
                                             . ' a ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                                             ', verificar tipos de cambio configurados.' .
                                             ' Para la fecha de ' .
                                             $entryAccount['entries']['from_date'],
                            ], 200);
                        }
                    }
                }
            }
        }

        /**
         * Se guarda un registro cada vez que se genera un reporte, en caso de que ya exista se actualiza
        */
        $url = 'auxiliaryBook/' . $date . '/' . $account_id;

        $currentDate = new DateTime();
        $currentDate = $currentDate->format('Y-m-d');

        /**
         * [$report almacena el registro del reporte del dia si existe]
         * @var [type]
         */
        $report = AccountingReportHistory::whereBetween('updated_at', [
                                                                        $currentDate . ' 00:00:00',
                                                                        $currentDate . ' 23:59:59'
                                                                    ])
                                        ->where('report', 'Libro Auxiliar')
                                        ->where('institution_id', $institution_id)->first();

        /*
        * se crea o actualiza el registro del reporte
        */
        if (!$report) {
            $report = AccountingReportHistory::create(
                [
                    'report'      => 'Libro Auxiliar',
                    'url'         => $url,
                    'currency_id' => $currency->id,
                    'institution_id' => $institution_id,
                ]
            );
        } else {
            $report->url         = $url;
            $report->currency_id = $currency->id;
            $report->institution_id = $institution_id;
            $report->save();
        }

        return response()->json(['result' => true, 'id' => $report->id], 200);
    }

    /**
     * [pdfVue verifica las conversiones monetarias de un reporte de libro auxiliar]
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     * @param string $account_id [variable con el id de la cuenta]
     * @param string $date       [fecha para la generación de reporte, formato 'YYYY-mm']
     * @param Currency $currency moneda en que se expresara el reporte
     */
    public function pdfVueSign($date, Currency $currency, $account_id = null)
    {
        /**
         * [$initDate fecha inicial de busqueda]
         * @var string
         */
        $initDate = $date . '-01';

        /**
         * [$day ultimo dia correspondiente al mes]
         * @var date
         */
        $day = date('d', (mktime(0, 0, 0, explode('-', $date)[1] + 1, 1, explode('-', $date)[0]) - 1));

        /**
         * [$endDate fecha final de busqueda]
         * @var string
         */
        $endDate     = $date . '-' . $day;

        $institution_id = null;

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        $convertions = [];

        if (!$account_id) {
            /**
             * [$query cuenta patrimonial con su relacion en asientos contables]
             * @var [Modules\Accounting\Models\AccountingEntry]
             */
            $query = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                }])
            ->where('group', '>', 0)
            ->where('subgroup', '>', 0)
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')->get();

            foreach ($query as $account) {
                /*
                 * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                 */
                foreach ($account['entryAccount'] as $entryAccount) {
                    $inRange = false;
                    if ($entryAccount['entries']) {
                        if (
                            !array_key_exists(
                                $entryAccount['entries']['currency']['id'],
                                $convertions
                            )
                        ) {
                            $convertions = $this->calculateExchangeRates(
                                $convertions,
                                $entryAccount['entries'],
                                $currency['id']
                            );
                        }
                        foreach ($convertions as $convertion) {
                            foreach ($convertion as $convert) {
                                if (
                                    $entryAccount['entries']['from_date'] >= $convert['start_at'] &&
                                    $entryAccount['entries']['from_date'] <= $convert['end_at']
                                ) {
                                    $inRange = true;
                                }
                            }
                        }

                        if (
                            !array_key_exists($entryAccount['entries']['currency']['id'], $convertions)
                                    && $entryAccount['entries']['currency']['id'] != $currency['id']
                        ) {
                            return response()->json([
                                    'result'  => false,
                                    'message' => 'Imposible expresar saldos de ' .
                                                 $entryAccount['entries']['currency']['symbol']
                                                 . ' (' . $entryAccount['entries']['currency']['name'] . ')'
                                                 . ' a ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                                                 ', verificar tipos de cambio configurados.' .
                                                 ' Para la fecha de ' .
                                                 $entryAccount['entries']['from_date'],
                              ], 200);
                        } elseif (!$inRange) {
                            if ($entryAccount['entries']['currency']['id'] != $currency['id']) {
                                return response()->json([
                                    'result'  => false,
                                    'message' => 'Imposible expresar saldos de ' .
                                                 $entryAccount['entries']['currency']['symbol']
                                                 . ' (' . $entryAccount['entries']['currency']['name'] . ')'
                                                 . ' a ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                                                 ', verificar tipos de cambio configurados.' .
                                                 ' Para la fecha de ' .
                                                 $entryAccount['entries']['from_date'],
                                ], 200);
                            }
                        }
                    }
                }
            }
        } elseif ($account_id) {
            $account = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                }])->find($account_id);
            /*
             * Se recorre y evalua la relacion en las conversiones necesarias a realizar
             */
            foreach ($account['entryAccount'] as $entryAccount) {
                $inRange = false;
                if ($entryAccount['entries']) {
                    if (
                        !array_key_exists(
                            $entryAccount['entries']['currency']['id'],
                            $convertions
                        )
                    ) {
                        $convertions = $this->calculateExchangeRates(
                            $convertions,
                            $entryAccount['entries'],
                            $currency['id']
                        );
                    }

                    foreach ($convertions as $convertion) {
                        foreach ($convertion as $convert) {
                            if (
                                $entryAccount['entries']['from_date'] >= $convert['start_at'] &&
                                $entryAccount['entries']['from_date'] <= $convert['end_at']
                            ) {
                                $inRange = true;
                            }
                        }
                    }

                    if (
                        !array_key_exists($entryAccount['entries']['currency']['id'], $convertions)
                        && $entryAccount['entries']['currency']['id'] != $currency['id']
                    ) {
                        return response()->json([
                            'result'  => false,
                            'message' => 'Imposible expresar saldos de ' .
                                         $entryAccount['entries']['currency']['symbol']
                                         . ' (' . $entryAccount['entries']['currency']['name'] . ')'
                                         . ' a ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                                         ', verificar tipos de cambio configurados.' .
                                         ' Para la fecha de ' .
                                         $entryAccount['entries']['from_date'],
                        ], 200);
                    } elseif (!$inRange) {
                        if ($entryAccount['entries']['currency']['id'] != $currency['id']) {
                            return response()->json([
                                'result'  => false,
                                'message' => 'Imposible expresar saldos de ' .
                                             $entryAccount['entries']['currency']['symbol']
                                             . ' (' . $entryAccount['entries']['currency']['name'] . ')'
                                             . ' a ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                                             ', verificar tipos de cambio configurados.' .
                                             ' Para la fecha de ' .
                                             $entryAccount['entries']['from_date'],
                            ], 200);
                        }
                    }
                }
            }
        }

        /**
         * Se guarda un registro cada vez que se genera un reporte, en caso de que ya exista se actualiza
        */
        $url = 'auxiliaryBookSign/' . $date . '/' . $account_id;

        $currentDate = new DateTime();
        $currentDate = $currentDate->format('Y-m-d');

        /**
         * [$report almacena el registro del reporte del dia si existe]
         * @var [type]
         */
        $report = AccountingReportHistory::whereBetween('updated_at', [
                                                                        $currentDate . ' 00:00:00',
                                                                        $currentDate . ' 23:59:59'
                                                                    ])
                                        ->where('report', 'Libro Auxiliar')
                                        ->where('institution_id', $institution_id)->first();

        /*
        * se crea o actualiza el registro del reporte
        */
        if (!$report) {
            $report = AccountingReportHistory::create(
                [
                    'report'      => 'Libro Auxiliar',
                    'url'         => $url,
                    'currency_id' => $currency->id,
                    'institution_id' => $institution_id,
                ]
            );
        } else {
            $report->url         = $url;
            $report->currency_id = $currency->id;
            $report->institution_id = $institution_id;
            $report->save();
        }

        return response()->json(['result' => true, 'id' => $report->id], 200);
    }
    /**
     * [pdf vista en la que se genera el reporte en pdf]
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     * @param  integer $report [id de reporte y su informacion]
     */
    public function pdf($report)
    {
        $report     = AccountingReportHistory::with('currency')->find($report);

        // Validar acceso para el registro
        if (!auth()->user()->isAdmin()) {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            if ($report && $report->queryAccess($user_profile['institution']['id'])) {
                return view('errors.403');
            }
        }
        $date       = explode('/', $report->url)[1];
        $account_id = explode('/', $report->url)[2];
        $initMonth  = (int)explode('-', $date)[1];
        $initYear   = (int)explode('-', $date)[0];

        if ($initMonth < 10) {
            $initMonth = '0' . $initMonth;
        }
        $date     = $initYear . '-' . $initMonth;

        $currency = $report->currency;

        /**
         * [$initDate fecha inicial de busqueda]
         * @var string
         */
        $initDate = $date . '-01';

        /**
         * [$day ultimo dia correspondiente al mes]
         * @var date
         */
        $day = date('d', (mktime(0, 0, 0, explode('-', $date)[1] + 1, 1, explode('-', $date)[0]) - 1));

        /**
         * [$endDate fecha final de busqueda]
         * @var string
         */
        $endDate = $date . '-' . $day;

        $institution_id = null;

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }


        $convertions = [];
        if (!$account_id) {
            // todas las cuentas auxiliares
            $query = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                }])
            ->where('group', '>', '0')
            ->where('subgroup', '>', '0')
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')->get();

            $acc  = [];
            $cont = 0;
            foreach ($query as $account) {
                array_push($acc, [
                    'id'           => $account['id'],
                    'denomination' => $account['denomination'],
                    'code'         => $account->getCodeAttribute(),
                    'entryAccount' => [],
                ]);
                /*
                 * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                 */
                foreach ($account['entryAccount'] as $entryAccount) {
                    if ($entryAccount['entries']) {
                        $r = [
                                'debit'      => '0',
                                'assets'     => '0',
                                'entries'    => [
                                    'reference'  => $entryAccount['entries']['reference'],
                                    'concept'    => $entryAccount['entries']['concept'],
                                    'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                ],
                            ];

                        if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                            $convertions = $this->calculateExchangeRates(
                                $convertions,
                                $entryAccount['entries'],
                                $currency['id']
                            );
                        }

                        $r['debit'] = ($entryAccount['debit'] != 0) ?
                                        $this->calculateOperation(
                                            $convertions,
                                            $entryAccount['entries']['currency']['id'],
                                            $entryAccount['debit'],
                                            $entryAccount['entries']['from_date'],
                                            ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                        ) : 0;

                        $r['assets'] = ($entryAccount['assets'] != 0) ?
                                        $this->calculateOperation(
                                            $convertions,
                                            $entryAccount['entries']['currency']['id'],
                                            $entryAccount['assets'],
                                            $entryAccount['entries']['from_date'],
                                            ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                        ) : 0;
                        array_push($acc[$cont]['entryAccount'], $r);
                    }
                }
                $cont++;
            }
        } elseif ($account_id) {
            // Una sola cuenta auxiliar
            $account = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                }])->find($account_id);

            $accountData = $account->toArray();

            if ($accountData['entry_account'] == []) {
            // Cuenta auxiliar que es padre
            $account = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                },
                'children.entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                },
                'children.children.entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                },
                'children.children.children.entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                },
                'children.children.children.children.entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                },
                'children.children.children.children.children.entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                },])->find($account_id);
            }

            $accountArr = $account->toArray();

            if (!array_key_exists('children', $accountArr)) {
                /* Cuenta que solamente es HIJA (1.1.1.01.01.01.000) */
                $acc[0] = [
                    'id'             => $account['id'],
                    'denomination'   => $account['denomination'],
                    'code'           => $account->getCodeAttribute(),
                    'entryAccount'   => [],
                ];
                /*
                 * recorrido y formateo de informacion en arreglos para mostrar en pdf
                 */
                foreach ($account['entryAccount'] as $entryAccount) {
                    if ($entryAccount['entries']) {
                        $r = [
                                'debit'      => '0',
                                'assets'     => '0',
                                'entries'    => [
                                    'reference'  => $entryAccount['entries']['reference'],
                                    'concept'    => $entryAccount['entries']['concept'],
                                    'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                ],
                            ];
    
                        if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                            $convertions = $this->calculateExchangeRates(
                                $convertions,
                                $entryAccount['entries'],
                                $currency['id']
                            );
                        }
    
                        $r['debit'] = ($entryAccount['debit'] != 0) ?
                                        $this->calculateOperation(
                                            $convertions,
                                            $entryAccount['entries']['currency']['id'],
                                            $entryAccount['debit'],
                                            $entryAccount['entries']['from_date'],
                                            ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                        ) : 0;
    
                        $r['assets'] = ($entryAccount['assets'] != 0) ?
                                        $this->calculateOperation(
                                            $convertions,
                                            $entryAccount['entries']['currency']['id'],
                                            $entryAccount['assets'],
                                            $entryAccount['entries']['from_date'],
                                            ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                        ) : 0;
    
                        array_push($acc[0]['entryAccount'], $r);
                    }
                }
            } else {
                if (explode('.', $accountArr['code'])[6] == '000' && explode('.', $accountArr['code'])[5] != '00') {
                    /* Cuenta que es HIJA, PADRE de HIJAS (1.1.1.01.01.01.000) */
                    $cont = 0;
                    $accData = [];
                    for ($childrenLenght=0; ; $childrenLenght++) {
                    if ($childrenLenght == count($accountArr['children'])) {
                        break;
                    }
                    /*
                     * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                     */
                    foreach ($account['children'][$childrenLenght]['entryAccount'] as $entryAccount) {
                        if ($entryAccount['entries']) {
                            array_push($accData, [
                                'id'           => $account['children'][$childrenLenght]['id'],
                                'denomination' => $account['children'][$childrenLenght]['denomination'],
                                'code'         => $account['children'][$childrenLenght]->getCodeAttribute(),
                                'entryAccount' => [],
                            ]);
                        }
                        if ($entryAccount['entries']) {
                            $r = [
                                    'debit'      => '0',
                                    'assets'     => '0',
                                    'entries'    => [
                                        'reference'  => $entryAccount['entries']['reference'],
                                        'concept'    => $entryAccount['entries']['concept'],
                                        'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                    ],
                                ];
                            
                            if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                $convertions = $this->calculateExchangeRates(
                                    $convertions,
                                    $entryAccount['entries'],
                                    $currency['id']
                                );
                            }
                            
                            
                            $r['debit'] = ($entryAccount['debit'] != 0) ?
                            $this->calculateOperation(
                                $convertions,
                                $entryAccount['entries']['currency']['id'],
                                $entryAccount['debit'],
                                $entryAccount['entries']['from_date'],
                                ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                ) : 0;
                                
                            $r['assets'] = ($entryAccount['assets'] != 0) ?
                            $this->calculateOperation(
                                $convertions,
                                $entryAccount['entries']['currency']['id'],
                                $entryAccount['assets'],
                                $entryAccount['entries']['from_date'],
                                ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                ) : 0;
                            array_push($accData[$cont]['entryAccount'], $r);
                            $cont++;
                        }
                    }
                    }
                } else if (explode('.', $accountArr['code'])[5] == '00' && explode('.', $accountArr['code'])[4] != '00') {
                    /* Cuenta que es HIJA, PADRE de PADRES con HIJAS (1.1.1.01.01.00.000) */
                    $cont = 0;
                    $accData = [];
                    foreach ($accountArr['children'] as $accArrChildren) {
                        for ($childrenCLenght=0; ; $childrenCLenght++) {
                            if ($childrenCLenght == count($accountArr['children'])) {
                                break;
                            }
                            /*
                             * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                             */
                            foreach ($account['children'][$childrenCLenght]['entryAccount'] as $entryAccount) {
                                if ($entryAccount['entries']) {
                                    array_push($accData, [
                                        'id'           => $account['children'][$childrenCLenght]['id'],
                                        'denomination' => $account['children'][$childrenCLenght]['denomination'],
                                        'code'         => $account['children'][$childrenCLenght]->getCodeAttribute(),
                                        'entryAccount' => [],
                                    ]);
                                }
                                if ($entryAccount['entries']) {
                                    $r = [
                                            'debit'      => '0',
                                            'assets'     => '0',
                                            'entries'    => [
                                                'reference'  => $entryAccount['entries']['reference'],
                                                'concept'    => $entryAccount['entries']['concept'],
                                                'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                            ],
                                        ];
                                    
                                    if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                        $convertions = $this->calculateExchangeRates(
                                            $convertions,
                                            $entryAccount['entries'],
                                            $currency['id']
                                        );
                                    }
                                    
                                    
                                    $r['debit'] = ($entryAccount['debit'] != 0) ?
                                    $this->calculateOperation(
                                        $convertions,
                                        $entryAccount['entries']['currency']['id'],
                                        $entryAccount['debit'],
                                        $entryAccount['entries']['from_date'],
                                        ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                        ) : 0;
                                        
                                    $r['assets'] = ($entryAccount['assets'] != 0) ?
                                    $this->calculateOperation(
                                        $convertions,
                                        $entryAccount['entries']['currency']['id'],
                                        $entryAccount['assets'],
                                        $entryAccount['entries']['from_date'],
                                        ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                        ) : 0;
                                    array_push($accData[$cont]['entryAccount'], $r);
                                    $cont++;
                                }
                            }
                            for ($childrenLenght=0; ; $childrenLenght++) {
                                if ($childrenLenght == count($accArrChildren['children'])) {
                                    break;
                                }
                                if (
                                    array_key_exists($childrenCLenght, $accountArr['children']) &&
                                    array_key_exists($childrenLenght, $accountArr['children'][$childrenCLenght]['children'])
                                ) {
                                    /*
                                     * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                     */
                                    foreach ($account['children'][$childrenCLenght]['children'][$childrenLenght]['entryAccount'] as $entryAccount) {
                                        if ($entryAccount['entries']) {
                                            array_push($accData, [
                                                'id'           => $account['children'][$childrenCLenght]['children'][$childrenLenght]['id'],
                                                'denomination' => $account['children'][$childrenCLenght]['children'][$childrenLenght]['denomination'],
                                                'code'         => $account['children'][$childrenCLenght]['children'][$childrenLenght]->getCodeAttribute(),
                                                'entryAccount' => [],
                                            ]);
                                        }

                                        if ($entryAccount['entries']) {
                                            $r = [
                                                    'debit'      => '0',
                                                    'assets'     => '0',
                                                    'entries'    => [
                                                        'reference'  => $entryAccount['entries']['reference'],
                                                        'concept'    => $entryAccount['entries']['concept'],
                                                        'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                    ],
                                                ];
                                            
                                            if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                                $convertions = $this->calculateExchangeRates(
                                                    $convertions,
                                                    $entryAccount['entries'],
                                                    $currency['id']
                                                );
                                            }

                                            $r['debit'] = ($entryAccount['debit'] != 0) ?
                                            $this->calculateOperation(
                                                $convertions,
                                                $entryAccount['entries']['currency']['id'],
                                                $entryAccount['debit'],
                                                $entryAccount['entries']['from_date'],
                                                ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                ) : 0;

                                            $r['assets'] = ($entryAccount['assets'] != 0) ?
                                            $this->calculateOperation(
                                                $convertions,
                                                $entryAccount['entries']['currency']['id'],
                                                $entryAccount['assets'],
                                                $entryAccount['entries']['from_date'],
                                                ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                ) : 0;
                                            array_push($accData[$cont]['entryAccount'], $r);
                                            $cont++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else if (explode('.', $accountArr['code'])[4] == '00' && explode('.', $accountArr['code'])[3] != '00') {
                    /* Cuenta que es HIJA, PADRE de PADRES que son PADRES de HIJAS (1.1.1.01.00.00.000) */
                    $cont = 0;
                    $accData = [];
                        for ($childrenCCLenght=0; ; $childrenCCLenght++) {
                            if ($childrenCCLenght == count($accountArr['children'])) {
                                break;
                            }
                            /*
                             * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                             */
                            foreach ($account['children'][$childrenCCLenght]['entryAccount'] as $entryAccount) {
                                if ($entryAccount['entries']) {
                                    array_push($accData, [
                                        'id'           => $account['children'][$childrenCCLenght]['id'],
                                        'denomination' => $account['children'][$childrenCCLenght]['denomination'],
                                        'code'         => $account['children'][$childrenCCLenght]->getCodeAttribute(),
                                        'entryAccount' => [],
                                    ]);
                                }
                                if ($entryAccount['entries']) {
                                    $r = [
                                            'debit'      => '0',
                                            'assets'     => '0',
                                            'entries'    => [
                                                'reference'  => $entryAccount['entries']['reference'],
                                                'concept'    => $entryAccount['entries']['concept'],
                                                'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                            ],
                                        ];
                                    
                                    if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                        $convertions = $this->calculateExchangeRates(
                                            $convertions,
                                            $entryAccount['entries'],
                                            $currency['id']
                                        );
                                    }
                                    
                                    
                                    $r['debit'] = ($entryAccount['debit'] != 0) ?
                                    $this->calculateOperation(
                                        $convertions,
                                        $entryAccount['entries']['currency']['id'],
                                        $entryAccount['debit'],
                                        $entryAccount['entries']['from_date'],
                                        ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                        ) : 0;
                                        
                                    $r['assets'] = ($entryAccount['assets'] != 0) ?
                                    $this->calculateOperation(
                                        $convertions,
                                        $entryAccount['entries']['currency']['id'],
                                        $entryAccount['assets'],
                                        $entryAccount['entries']['from_date'],
                                        ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                        ) : 0;
                                    array_push($accData[$cont]['entryAccount'], $r);
                                    $cont++;
                                }
                            }
                            for ($childrenCLenght=0; ; $childrenCLenght++) {
                                if ($childrenCLenght == count($accountArr['children'][$childrenCCLenght]['children'])) {
                                    break;
                                }
                                if (
                                    array_key_exists($childrenCCLenght, $accountArr['children']) &&
                                    array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCLenght]['children'])
                                ) {
                                    /*
                                     * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                     */
                                    foreach ($account['children'][$childrenCCLenght]['children'][$childrenCLenght]['entryAccount'] as $entryAccount) {
                                        if ($entryAccount['entries']) {
                                            array_push($accData, [
                                                'id'           => $account['children'][$childrenCCLenght]['children'][$childrenCLenght]['id'],
                                                'denomination' => $account['children'][$childrenCCLenght]['children'][$childrenCLenght]['denomination'],
                                                'code'         => $account['children'][$childrenCCLenght]['children'][$childrenCLenght]->getCodeAttribute(),
                                                'entryAccount' => [],
                                            ]);
                                        }
                                        if ($entryAccount['entries']) {
                                            $r = [
                                                    'debit'      => '0',
                                                    'assets'     => '0',
                                                    'entries'    => [
                                                        'reference'  => $entryAccount['entries']['reference'],
                                                        'concept'    => $entryAccount['entries']['concept'],
                                                        'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                    ],
                                                ];
                                            
                                            if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                                $convertions = $this->calculateExchangeRates(
                                                    $convertions,
                                                    $entryAccount['entries'],
                                                    $currency['id']
                                                );
                                            }
                                            $r['debit'] = ($entryAccount['debit'] != 0) ?
                                            $this->calculateOperation(
                                                $convertions,
                                                $entryAccount['entries']['currency']['id'],
                                                $entryAccount['debit'],
                                                $entryAccount['entries']['from_date'],
                                                ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                ) : 0;
                                            $r['assets'] = ($entryAccount['assets'] != 0) ?
                                            $this->calculateOperation(
                                                $convertions,
                                                $entryAccount['entries']['currency']['id'],
                                                $entryAccount['assets'],
                                                $entryAccount['entries']['from_date'],
                                                ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                ) : 0;
                                            array_push($accData[$cont]['entryAccount'], $r);
                                            $cont++;
                                        }
                                    }
                                }
                                for ($childrenLenght=0; ; $childrenLenght++) {
                                    if ($childrenLenght == count($accountArr['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])) {
                                        break;
                                    }
                                    if (
                                        array_key_exists($childrenCCLenght, $accountArr['children']) &&
                                        array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCLenght]['children']) &&
                                        array_key_exists($childrenLenght, $accountArr['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])
                                    ) {
                                        /*
                                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                         */
                                        foreach ($account['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['entryAccount'] as $entryAccount) {
                                            if ($entryAccount['entries']) {
                                                array_push($accData, [
                                                    'id'           => $account['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['id'],
                                                    'denomination' => $account['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['denomination'],
                                                    'code'         => $account['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]->getCodeAttribute(),
                                                    'entryAccount' => [],
                                                ]);
                                            }
                                            if ($entryAccount['entries']) {
                                                $r = [
                                                        'debit'      => '0',
                                                        'assets'     => '0',
                                                        'entries'    => [
                                                            'reference'  => $entryAccount['entries']['reference'],
                                                            'concept'    => $entryAccount['entries']['concept'],
                                                            'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                        ],
                                                    ];
                                                
                                                if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                                    $convertions = $this->calculateExchangeRates(
                                                        $convertions,
                                                        $entryAccount['entries'],
                                                        $currency['id']
                                                    );
                                                }
                                                $r['debit'] = ($entryAccount['debit'] != 0) ?
                                                $this->calculateOperation(
                                                    $convertions,
                                                    $entryAccount['entries']['currency']['id'],
                                                    $entryAccount['debit'],
                                                    $entryAccount['entries']['from_date'],
                                                    ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                    ) : 0;
                                                $r['assets'] = ($entryAccount['assets'] != 0) ?
                                                $this->calculateOperation(
                                                    $convertions,
                                                    $entryAccount['entries']['currency']['id'],
                                                    $entryAccount['assets'],
                                                    $entryAccount['entries']['from_date'],
                                                    ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                    ) : 0;
                                                array_push($accData[$cont]['entryAccount'], $r);
                                                $cont++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                } else if (explode('.', $accountArr['code'])[3] == '00' && explode('.', $accountArr['code'])[2] != '0') {
                    /* Cuenta que es HIJA, PADRE de PADRES que son PADRES con HIJAS que tienen HIJAS (1.1.1.00.00.00.000) */
                    $cont = 0;
                    $accData = [];
                    for ($childrenCCCLenght=0; ; $childrenCCCLenght++) { 
                        if ($childrenCCCLenght == count($accountArr['children'])) {
                            break;
                        }
                        /*
                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                         */
                        foreach ($account['children'][$childrenCCCLenght]['entryAccount'] as $entryAccount) {
                            if ($entryAccount['entries']) {
                                array_push($accData, [
                                    'id'           => $account['children'][$childrenCCCLenght]['id'],
                                    'denomination' => $account['children'][$childrenCCCLenght]['denomination'],
                                    'code'         => $account['children'][$childrenCCCLenght]->getCodeAttribute(),
                                    'entryAccount' => [],
                                ]);
                            }
                            if ($entryAccount['entries']) {
                                $r = [
                                        'debit'      => '0',
                                        'assets'     => '0',
                                        'entries'    => [
                                            'reference'  => $entryAccount['entries']['reference'],
                                            'concept'    => $entryAccount['entries']['concept'],
                                            'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                        ],
                                    ];
                                
                                if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                    $convertions = $this->calculateExchangeRates(
                                        $convertions,
                                        $entryAccount['entries'],
                                        $currency['id']
                                    );
                                }
                                
                                
                                $r['debit'] = ($entryAccount['debit'] != 0) ?
                                $this->calculateOperation(
                                    $convertions,
                                    $entryAccount['entries']['currency']['id'],
                                    $entryAccount['debit'],
                                    $entryAccount['entries']['from_date'],
                                    ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                    ) : 0;
                                    
                                $r['assets'] = ($entryAccount['assets'] != 0) ?
                                $this->calculateOperation(
                                    $convertions,
                                    $entryAccount['entries']['currency']['id'],
                                    $entryAccount['assets'],
                                    $entryAccount['entries']['from_date'],
                                    ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                    ) : 0;
                                array_push($accData[$cont]['entryAccount'], $r);
                                $cont++;
                            }
                        }
                        for ($childrenCCLenght=0; ; $childrenCCLenght++) {
                            if ($childrenCCLenght == count($accountArr['children'][$childrenCCCLenght]['children'])) {
                                break;
                            }
                            if (
                                array_key_exists($childrenCCCLenght, $accountArr['children']) &&
                                array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCLenght]['children'])
                            ) {
                                /*
                                 * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                 */
                                foreach ($account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['entryAccount'] as $entryAccount) {
                                    if ($entryAccount['entries']) {
                                        array_push($accData, [
                                            'id'           => $account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['id'],
                                            'denomination' => $account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['denomination'],
                                            'code'         => $account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]->getCodeAttribute(),
                                            'entryAccount' => [],
                                        ]);
                                    }
                                    if ($entryAccount['entries']) {
                                        $r = [
                                                'debit'      => '0',
                                                'assets'     => '0',
                                                'entries'    => [
                                                    'reference'  => $entryAccount['entries']['reference'],
                                                    'concept'    => $entryAccount['entries']['concept'],
                                                    'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                ],
                                            ];
                                        
                                        if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                            $convertions = $this->calculateExchangeRates(
                                                $convertions,
                                                $entryAccount['entries'],
                                                $currency['id']
                                            );
                                        }


                                        $r['debit'] = ($entryAccount['debit'] != 0) ?
                                        $this->calculateOperation(
                                            $convertions,
                                            $entryAccount['entries']['currency']['id'],
                                            $entryAccount['debit'],
                                            $entryAccount['entries']['from_date'],
                                            ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                            ) : 0;

                                        $r['assets'] = ($entryAccount['assets'] != 0) ?
                                        $this->calculateOperation(
                                            $convertions,
                                            $entryAccount['entries']['currency']['id'],
                                            $entryAccount['assets'],
                                            $entryAccount['entries']['from_date'],
                                            ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                            ) : 0;
                                        array_push($accData[$cont]['entryAccount'], $r);
                                        $cont++;
                                    }
                                }
                            }
                            for ($childrenCLenght=0; ; $childrenCLenght++) {
                                if ($childrenCLenght == count($accountArr['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'])) {
                                    break;
                                }
                                if (
                                    array_key_exists($childrenCCCLenght, $accountArr['children']) &&
                                    array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCLenght]['children']) &&
                                    array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'])
                                ) {
                                    /*
                                     * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                     */
                                    foreach ($account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['entryAccount'] as $entryAccount) {
                                        if ($entryAccount['entries']) {
                                            array_push($accData, [
                                                'id'           => $account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['id'],
                                                'denomination' => $account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['denomination'],
                                                'code'         => $account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]->getCodeAttribute(),
                                                'entryAccount' => [],
                                            ]);
                                        }
                                        if ($entryAccount['entries']) {
                                            $r = [
                                                    'debit'      => '0',
                                                    'assets'     => '0',
                                                    'entries'    => [
                                                        'reference'  => $entryAccount['entries']['reference'],
                                                        'concept'    => $entryAccount['entries']['concept'],
                                                        'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                    ],
                                                ];
                                            
                                            if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                                $convertions = $this->calculateExchangeRates(
                                                    $convertions,
                                                    $entryAccount['entries'],
                                                    $currency['id']
                                                );
                                            }
                                            $r['debit'] = ($entryAccount['debit'] != 0) ?
                                            $this->calculateOperation(
                                                $convertions,
                                                $entryAccount['entries']['currency']['id'],
                                                $entryAccount['debit'],
                                                $entryAccount['entries']['from_date'],
                                                ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                ) : 0;
                                            $r['assets'] = ($entryAccount['assets'] != 0) ?
                                            $this->calculateOperation(
                                                $convertions,
                                                $entryAccount['entries']['currency']['id'],
                                                $entryAccount['assets'],
                                                $entryAccount['entries']['from_date'],
                                                ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                ) : 0;
                                            array_push($accData[$cont]['entryAccount'], $r);
                                            $cont++;
                                        }
                                    }
                                }
                                for ($childrenLenght=0; ; $childrenLenght++) {
                                    if ($childrenLenght == count($accountArr['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])) {
                                        break;
                                    }
                                    if (
                                        array_key_exists($childrenCCCLenght, $accountArr['children']) &&
                                        array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCLenght]['children']) &&
                                        array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children']) &&
                                        array_key_exists($childrenLenght, $accountArr['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])
                                    ) {
                                        /*
                                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                         */
                                        foreach ($account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['entryAccount'] as $entryAccount) {
                                            if ($entryAccount['entries']) {
                                                array_push($accData, [
                                                    'id'           => $account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['id'],
                                                    'denomination' => $account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['denomination'],
                                                    'code'         => $account['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]->getCodeAttribute(),
                                                    'entryAccount' => [],
                                                ]);
                                            }
                                            if ($entryAccount['entries']) {
                                                $r = [
                                                        'debit'      => '0',
                                                        'assets'     => '0',
                                                        'entries'    => [
                                                            'reference'  => $entryAccount['entries']['reference'],
                                                            'concept'    => $entryAccount['entries']['concept'],
                                                            'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                        ],
                                                    ];
                                                
                                                if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                                    $convertions = $this->calculateExchangeRates(
                                                        $convertions,
                                                        $entryAccount['entries'],
                                                        $currency['id']
                                                    );
                                                }
                                                $r['debit'] = ($entryAccount['debit'] != 0) ?
                                                $this->calculateOperation(
                                                    $convertions,
                                                    $entryAccount['entries']['currency']['id'],
                                                    $entryAccount['debit'],
                                                    $entryAccount['entries']['from_date'],
                                                    ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                    ) : 0;
                                                $r['assets'] = ($entryAccount['assets'] != 0) ?
                                                $this->calculateOperation(
                                                    $convertions,
                                                    $entryAccount['entries']['currency']['id'],
                                                    $entryAccount['assets'],
                                                    $entryAccount['entries']['from_date'],
                                                    ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                    ) : 0;
                                                array_push($accData[$cont]['entryAccount'], $r);
                                                $cont++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else if (explode('.', $accountArr['code'])[2] == '0' && explode('.', $accountArr['code'])[1] != '0') {
                    /* Cuenta que es HIJA, PADRE de PADRES que son PADRES de otros PADRES con HIJAS que tienen HIJAS (1.1.0.00.00.00.000) */
                    $cont = 0;
                    $accData = [];
                    for ($childrenCCCCLenght=0; ; $childrenCCCCLenght++) { 
                        if ($childrenCCCCLenght == count($accountArr['children'])) {
                            break;
                        }
                        /*
                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                         */
                        foreach ($account['children'][$childrenCCCCLenght]['entryAccount'] as $entryAccount) {
                            if ($entryAccount['entries']) {
                                array_push($accData, [
                                    'id'           => $account['children'][$childrenCCCCLenght]['id'],
                                    'denomination' => $account['children'][$childrenCCCCLenght]['denomination'],
                                    'code'         => $account['children'][$childrenCCCCLenght]->getCodeAttribute(),
                                    'entryAccount' => [],
                                ]);
                            }
                            if ($entryAccount['entries']) {
                                $r = [
                                        'debit'      => '0',
                                        'assets'     => '0',
                                        'entries'    => [
                                            'reference'  => $entryAccount['entries']['reference'],
                                            'concept'    => $entryAccount['entries']['concept'],
                                            'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                        ],
                                    ];
                                
                                if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                    $convertions = $this->calculateExchangeRates(
                                        $convertions,
                                        $entryAccount['entries'],
                                        $currency['id']
                                    );
                                }
                                
                                
                                $r['debit'] = ($entryAccount['debit'] != 0) ?
                                $this->calculateOperation(
                                    $convertions,
                                    $entryAccount['entries']['currency']['id'],
                                    $entryAccount['debit'],
                                    $entryAccount['entries']['from_date'],
                                    ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                    ) : 0;
                                    
                                $r['assets'] = ($entryAccount['assets'] != 0) ?
                                $this->calculateOperation(
                                    $convertions,
                                    $entryAccount['entries']['currency']['id'],
                                    $entryAccount['assets'],
                                    $entryAccount['entries']['from_date'],
                                    ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                    ) : 0;
                                array_push($accData[$cont]['entryAccount'], $r);
                                $cont++;
                            }
                        }
                        for ($childrenCCCLenght=0; ; $childrenCCCLenght++) { 
                            if ($childrenCCCLenght == count($accountArr['children'][$childrenCCCCLenght]['children'])) {
                                break;
                            }
                            if (
                                array_key_exists($childrenCCCCLenght, $accountArr['children']) &&
                                array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCLenght]['children'])
                            ) {
                                /*
                                 * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                 */
                                foreach ($account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['entryAccount'] as $entryAccount) {
                                    if ($entryAccount['entries']) {
                                        array_push($accData, [
                                            'id'           => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['id'],
                                            'denomination' => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['denomination'],
                                            'code'         => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]->getCodeAttribute(),
                                            'entryAccount' => [],
                                        ]);
                                    }
                                    if ($entryAccount['entries']) {
                                        $r = [
                                                'debit'      => '0',
                                                'assets'     => '0',
                                                'entries'    => [
                                                    'reference'  => $entryAccount['entries']['reference'],
                                                    'concept'    => $entryAccount['entries']['concept'],
                                                    'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                ],
                                            ];
                                        
                                        if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                            $convertions = $this->calculateExchangeRates(
                                                $convertions,
                                                $entryAccount['entries'],
                                                $currency['id']
                                            );
                                        }


                                        $r['debit'] = ($entryAccount['debit'] != 0) ?
                                        $this->calculateOperation(
                                            $convertions,
                                            $entryAccount['entries']['currency']['id'],
                                            $entryAccount['debit'],
                                            $entryAccount['entries']['from_date'],
                                            ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                            ) : 0;

                                        $r['assets'] = ($entryAccount['assets'] != 0) ?
                                        $this->calculateOperation(
                                            $convertions,
                                            $entryAccount['entries']['currency']['id'],
                                            $entryAccount['assets'],
                                            $entryAccount['entries']['from_date'],
                                            ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                            ) : 0;
                                        array_push($accData[$cont]['entryAccount'], $r);
                                        $cont++;
                                    }
                                }
                            }
                            for ($childrenCCLenght=0; ; $childrenCCLenght++) {
                                if ($childrenCCLenght == count($accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'])) {
                                    break;
                                }
                                if (
                                    array_key_exists($childrenCCCCLenght, $accountArr['children']) &&
                                    array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCLenght]['children']) &&
                                    array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'])
                                ) {
                                    /*
                                     * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                     */
                                    foreach ($account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['entryAccount'] as $entryAccount) {
                                        if ($entryAccount['entries']) {
                                            array_push($accData, [
                                                'id'           => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['id'],
                                                'denomination' => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['denomination'],
                                                'code'         => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]->getCodeAttribute(),
                                                'entryAccount' => [],
                                            ]);
                                        }
                                        if ($entryAccount['entries']) {
                                            $r = [
                                                    'debit'      => '0',
                                                    'assets'     => '0',
                                                    'entries'    => [
                                                        'reference'  => $entryAccount['entries']['reference'],
                                                        'concept'    => $entryAccount['entries']['concept'],
                                                        'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                    ],
                                                ];
                                            
                                            if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                                $convertions = $this->calculateExchangeRates(
                                                    $convertions,
                                                    $entryAccount['entries'],
                                                    $currency['id']
                                                );
                                            }
    
    
                                            $r['debit'] = ($entryAccount['debit'] != 0) ?
                                            $this->calculateOperation(
                                                $convertions,
                                                $entryAccount['entries']['currency']['id'],
                                                $entryAccount['debit'],
                                                $entryAccount['entries']['from_date'],
                                                ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                ) : 0;
    
                                            $r['assets'] = ($entryAccount['assets'] != 0) ?
                                            $this->calculateOperation(
                                                $convertions,
                                                $entryAccount['entries']['currency']['id'],
                                                $entryAccount['assets'],
                                                $entryAccount['entries']['from_date'],
                                                ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                ) : 0;
                                            array_push($accData[$cont]['entryAccount'], $r);
                                            $cont++;
                                        }
                                    }
                                }
                                for ($childrenCLenght=0; ; $childrenCLenght++) {
                                    if ($childrenCLenght == count($accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'])) {
                                        break;
                                    }
                                    if (
                                        array_key_exists($childrenCCCCLenght, $accountArr['children']) &&
                                        array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCLenght]['children']) &&
                                        array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children']) &&
                                        array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'])
                                    ) {
                                        /*
                                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                         */
                                        foreach ($account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['entryAccount'] as $entryAccount) {
                                            if ($entryAccount['entries']) {
                                                array_push($accData, [
                                                    'id'           => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['id'],
                                                    'denomination' => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['denomination'],
                                                    'code'         => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]->getCodeAttribute(),
                                                    'entryAccount' => [],
                                                ]);
                                            }
                                            if ($entryAccount['entries']) {
                                                $r = [
                                                        'debit'      => '0',
                                                        'assets'     => '0',
                                                        'entries'    => [
                                                            'reference'  => $entryAccount['entries']['reference'],
                                                            'concept'    => $entryAccount['entries']['concept'],
                                                            'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                        ],
                                                    ];
                                                
                                                if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                                    $convertions = $this->calculateExchangeRates(
                                                        $convertions,
                                                        $entryAccount['entries'],
                                                        $currency['id']
                                                    );
                                                }
                                                $r['debit'] = ($entryAccount['debit'] != 0) ?
                                                $this->calculateOperation(
                                                    $convertions,
                                                    $entryAccount['entries']['currency']['id'],
                                                    $entryAccount['debit'],
                                                    $entryAccount['entries']['from_date'],
                                                    ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                    ) : 0;
                                                $r['assets'] = ($entryAccount['assets'] != 0) ?
                                                $this->calculateOperation(
                                                    $convertions,
                                                    $entryAccount['entries']['currency']['id'],
                                                    $entryAccount['assets'],
                                                    $entryAccount['entries']['from_date'],
                                                    ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                    ) : 0;
                                                array_push($accData[$cont]['entryAccount'], $r);
                                                $cont++;
                                            }
                                        }
                                    }
                                    for ($childrenLenght=0; ; $childrenLenght++) {
                                        if ($childrenLenght == count($accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])) {
                                            break;
                                        }
                                        if (
                                            array_key_exists($childrenCCCCLenght, $accountArr['children']) &&
                                            array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCLenght]['children']) &&
                                            array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children']) &&
                                            array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children']) &&
                                            array_key_exists($childrenLenght, $accountArr['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])
                                        ) {
                                            /*
                                             * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                             */
                                            foreach ($account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['entryAccount'] as $entryAccount) {
                                                if ($entryAccount['entries']) {
                                                    array_push($accData, [
                                                        'id'           => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['id'],
                                                        'denomination' => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['denomination'],
                                                        'code'         => $account['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]->getCodeAttribute(),
                                                        'entryAccount' => [],
                                                    ]);
                                                }
                                                if ($entryAccount['entries']) {
                                                    $r = [
                                                            'debit'      => '0',
                                                            'assets'     => '0',
                                                            'entries'    => [
                                                                'reference'  => $entryAccount['entries']['reference'],
                                                                'concept'    => $entryAccount['entries']['concept'],
                                                                'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                            ],
                                                        ];
                                                    
                                                    if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                                        $convertions = $this->calculateExchangeRates(
                                                            $convertions,
                                                            $entryAccount['entries'],
                                                            $currency['id']
                                                        );
                                                    }
                                                    $r['debit'] = ($entryAccount['debit'] != 0) ?
                                                    $this->calculateOperation(
                                                        $convertions,
                                                        $entryAccount['entries']['currency']['id'],
                                                        $entryAccount['debit'],
                                                        $entryAccount['entries']['from_date'],
                                                        ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                        ) : 0;
                                                    $r['assets'] = ($entryAccount['assets'] != 0) ?
                                                    $this->calculateOperation(
                                                        $convertions,
                                                        $entryAccount['entries']['currency']['id'],
                                                        $entryAccount['assets'],
                                                        $entryAccount['entries']['from_date'],
                                                        ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                        ) : 0;
                                                    array_push($accData[$cont]['entryAccount'], $r);
                                                    $cont++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else if (explode('.', $accountArr['code'])[1] == '0') {
                    /* Cuenta que es PADRE de PADRES (1.0.0.00.00.00.000) */
                    $cont = 0;
                    $accData = [];
                    for ($childrenCCCCCLenght=0; ; $childrenCCCCCLenght++) { 
                        if ($childrenCCCCCLenght == count($accountArr['children'])) {
                            break;
                        }
                        /*
                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                         */
                        foreach ($account['children'][$childrenCCCCCLenght]['entryAccount'] as $entryAccount) {
                            if ($entryAccount['entries']) {
                                array_push($accData, [
                                    'id'           => $account['children'][$childrenCCCCCLenght]['id'],
                                    'denomination' => $account['children'][$childrenCCCCCLenght]['denomination'],
                                    'code'         => $account['children'][$childrenCCCCCLenght]->getCodeAttribute(),
                                    'entryAccount' => [],
                                ]);
                            }
                            if ($entryAccount['entries']) {
                                $r = [
                                        'debit'      => '0',
                                        'assets'     => '0',
                                        'entries'    => [
                                            'reference'  => $entryAccount['entries']['reference'],
                                            'concept'    => $entryAccount['entries']['concept'],
                                            'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                        ],
                                    ];
                                
                                if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                    $convertions = $this->calculateExchangeRates(
                                        $convertions,
                                        $entryAccount['entries'],
                                        $currency['id']
                                    );
                                }
                                
                                
                                $r['debit'] = ($entryAccount['debit'] != 0) ?
                                $this->calculateOperation(
                                    $convertions,
                                    $entryAccount['entries']['currency']['id'],
                                    $entryAccount['debit'],
                                    $entryAccount['entries']['from_date'],
                                    ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                    ) : 0;
                                    
                                $r['assets'] = ($entryAccount['assets'] != 0) ?
                                $this->calculateOperation(
                                    $convertions,
                                    $entryAccount['entries']['currency']['id'],
                                    $entryAccount['assets'],
                                    $entryAccount['entries']['from_date'],
                                    ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                    ) : 0;
                                array_push($accData[$cont]['entryAccount'], $r);
                                $cont++;
                            }
                        }
                        for ($childrenCCCCLenght=0; ; $childrenCCCCLenght++) {
                            if ($childrenCCCCLenght == count($accountArr['children'][$childrenCCCCCLenght]['children'])) {
                                break;
                            }
                            if (
                                array_key_exists($childrenCCCCCLenght, $accountArr['children']) &&
                                array_key_exists($childrenCCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'])
                            ) {
                                /*
                                 * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                 */
                                foreach ($account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['entryAccount'] as $entryAccount) {
                                    if ($entryAccount['entries']) {
                                        array_push($accData, [
                                            'id'           => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['id'],
                                            'denomination' => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['denomination'],
                                            'code'         => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]->getCodeAttribute(),
                                            'entryAccount' => [],
                                        ]);
                                    }
                                    if ($entryAccount['entries']) {
                                        $r = [
                                                'debit'      => '0',
                                                'assets'     => '0',
                                                'entries'    => [
                                                    'reference'  => $entryAccount['entries']['reference'],
                                                    'concept'    => $entryAccount['entries']['concept'],
                                                    'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                ],
                                            ];
                                        
                                        if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                            $convertions = $this->calculateExchangeRates(
                                                $convertions,
                                                $entryAccount['entries'],
                                                $currency['id']
                                            );
                                        }


                                        $r['debit'] = ($entryAccount['debit'] != 0) ?
                                        $this->calculateOperation(
                                            $convertions,
                                            $entryAccount['entries']['currency']['id'],
                                            $entryAccount['debit'],
                                            $entryAccount['entries']['from_date'],
                                            ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                            ) : 0;

                                        $r['assets'] = ($entryAccount['assets'] != 0) ?
                                        $this->calculateOperation(
                                            $convertions,
                                            $entryAccount['entries']['currency']['id'],
                                            $entryAccount['assets'],
                                            $entryAccount['entries']['from_date'],
                                            ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                            ) : 0;
                                        array_push($accData[$cont]['entryAccount'], $r);
                                        $cont++;
                                    }
                                }
                            }
                            for ($childrenCCCLenght=0; ; $childrenCCCLenght++) { 
                                if ($childrenCCCLenght == count($accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'])) {
                                    break;
                                }
                                if (
                                    array_key_exists($childrenCCCCCLenght, $accountArr['children']) &&
                                    array_key_exists($childrenCCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children']) &&
                                    array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'])
                                ) {
                                    /*
                                     * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                     */
                                    foreach ($account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['entryAccount'] as $entryAccount) {
                                        if ($entryAccount['entries']) {
                                            array_push($accData, [
                                                'id'           => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['id'],
                                                'denomination' => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['denomination'],
                                                'code'         => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]->getCodeAttribute(),
                                                'entryAccount' => [],
                                            ]);
                                        }
                                        if ($entryAccount['entries']) {
                                            $r = [
                                                    'debit'      => '0',
                                                    'assets'     => '0',
                                                    'entries'    => [
                                                        'reference'  => $entryAccount['entries']['reference'],
                                                        'concept'    => $entryAccount['entries']['concept'],
                                                        'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                    ],
                                                ];
                                            
                                            if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                                $convertions = $this->calculateExchangeRates(
                                                    $convertions,
                                                    $entryAccount['entries'],
                                                    $currency['id']
                                                );
                                            }
    
    
                                            $r['debit'] = ($entryAccount['debit'] != 0) ?
                                            $this->calculateOperation(
                                                $convertions,
                                                $entryAccount['entries']['currency']['id'],
                                                $entryAccount['debit'],
                                                $entryAccount['entries']['from_date'],
                                                ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                ) : 0;
    
                                            $r['assets'] = ($entryAccount['assets'] != 0) ?
                                            $this->calculateOperation(
                                                $convertions,
                                                $entryAccount['entries']['currency']['id'],
                                                $entryAccount['assets'],
                                                $entryAccount['entries']['from_date'],
                                                ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                ) : 0;
                                            array_push($accData[$cont]['entryAccount'], $r);
                                            $cont++;
                                        }
                                    }
                                }
                                for ($childrenCCLenght=0; ; $childrenCCLenght++) {
                                    if ($childrenCCLenght == count($accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'])) {
                                        break;
                                    }
                                    if (
                                        array_key_exists($childrenCCCCCLenght, $accountArr['children']) &&
                                        array_key_exists($childrenCCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children']) &&
                                        array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children']) &&
                                        array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'])
                                    ) {
                                        /*
                                         * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                         */
                                        foreach ($account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['entryAccount'] as $entryAccount) {
                                            if ($entryAccount['entries']) {
                                                array_push($accData, [
                                                    'id'           => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['id'],
                                                    'denomination' => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['denomination'],
                                                    'code'         => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]->getCodeAttribute(),
                                                    'entryAccount' => [],
                                                ]);
                                            }
                                            if ($entryAccount['entries']) {
                                                $r = [
                                                        'debit'      => '0',
                                                        'assets'     => '0',
                                                        'entries'    => [
                                                            'reference'  => $entryAccount['entries']['reference'],
                                                            'concept'    => $entryAccount['entries']['concept'],
                                                            'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                        ],
                                                    ];
                                                
                                                if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                                    $convertions = $this->calculateExchangeRates(
                                                        $convertions,
                                                        $entryAccount['entries'],
                                                        $currency['id']
                                                    );
                                                }
        
        
                                                $r['debit'] = ($entryAccount['debit'] != 0) ?
                                                $this->calculateOperation(
                                                    $convertions,
                                                    $entryAccount['entries']['currency']['id'],
                                                    $entryAccount['debit'],
                                                    $entryAccount['entries']['from_date'],
                                                    ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                    ) : 0;
        
                                                $r['assets'] = ($entryAccount['assets'] != 0) ?
                                                $this->calculateOperation(
                                                    $convertions,
                                                    $entryAccount['entries']['currency']['id'],
                                                    $entryAccount['assets'],
                                                    $entryAccount['entries']['from_date'],
                                                    ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                    ) : 0;
                                                array_push($accData[$cont]['entryAccount'], $r);
                                                $cont++;
                                            }
                                        }
                                    }
                                    for ($childrenCLenght=0; ; $childrenCLenght++) {
                                        if ($childrenCLenght == count($accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'])) {
                                            break;
                                        }
                                        if (
                                            array_key_exists($childrenCCCCCLenght, $accountArr['children']) &&
                                            array_key_exists($childrenCCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children']) &&
                                            array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children']) &&
                                            array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children']) &&
                                            array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'])
                                        ) {
                                            /*
                                             * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                             */
                                            foreach ($account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['entryAccount'] as $entryAccount) {
                                                if ($entryAccount['entries']) {
                                                    array_push($accData, [
                                                        'id'           => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['id'],
                                                        'denomination' => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['denomination'],
                                                        'code'         => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]->getCodeAttribute(),
                                                        'entryAccount' => [],
                                                    ]);
                                                }
                                                if ($entryAccount['entries']) {
                                                    $r = [
                                                            'debit'      => '0',
                                                            'assets'     => '0',
                                                            'entries'    => [
                                                                'reference'  => $entryAccount['entries']['reference'],
                                                                'concept'    => $entryAccount['entries']['concept'],
                                                                'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                            ],
                                                        ];
                                                    
                                                    if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                                        $convertions = $this->calculateExchangeRates(
                                                            $convertions,
                                                            $entryAccount['entries'],
                                                            $currency['id']
                                                        );
                                                    }
                                                    $r['debit'] = ($entryAccount['debit'] != 0) ?
                                                    $this->calculateOperation(
                                                        $convertions,
                                                        $entryAccount['entries']['currency']['id'],
                                                        $entryAccount['debit'],
                                                        $entryAccount['entries']['from_date'],
                                                        ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                        ) : 0;
                                                    $r['assets'] = ($entryAccount['assets'] != 0) ?
                                                    $this->calculateOperation(
                                                        $convertions,
                                                        $entryAccount['entries']['currency']['id'],
                                                        $entryAccount['assets'],
                                                        $entryAccount['entries']['from_date'],
                                                        ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                        ) : 0;
                                                    array_push($accData[$cont]['entryAccount'], $r);
                                                    $cont++;
                                                }
                                            }
                                        }
                                        for ($childrenLenght=0; ; $childrenLenght++) {
                                            if ($childrenLenght == count($accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])) {
                                                break;
                                            }
                                            if (
                                                array_key_exists($childrenCCCCCLenght, $accountArr['children']) &&
                                                array_key_exists($childrenCCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children']) &&
                                                array_key_exists($childrenCCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children']) &&
                                                array_key_exists($childrenCCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children']) &&
                                                array_key_exists($childrenCLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children']) &&
                                                array_key_exists($childrenLenght, $accountArr['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'])
                                            ) {
                                                /*
                                                 * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                                                 */
                                                foreach ($account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['entryAccount'] as $entryAccount) {
                                                    if ($entryAccount['entries']) {
                                                        array_push($accData, [
                                                            'id'           => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['id'],
                                                            'denomination' => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]['denomination'],
                                                            'code'         => $account['children'][$childrenCCCCCLenght]['children'][$childrenCCCCLenght]['children'][$childrenCCCLenght]['children'][$childrenCCLenght]['children'][$childrenCLenght]['children'][$childrenLenght]->getCodeAttribute(),
                                                            'entryAccount' => [],
                                                        ]);
                                                    }
                                                    if ($entryAccount['entries']) {
                                                        $r = [
                                                                'debit'      => '0',
                                                                'assets'     => '0',
                                                                'entries'    => [
                                                                    'reference'  => $entryAccount['entries']['reference'],
                                                                    'concept'    => $entryAccount['entries']['concept'],
                                                                    'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                                                ],
                                                            ];
                                                        
                                                        if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                                                            $convertions = $this->calculateExchangeRates(
                                                                $convertions,
                                                                $entryAccount['entries'],
                                                                $currency['id']
                                                            );
                                                        }
                                                        $r['debit'] = ($entryAccount['debit'] != 0) ?
                                                        $this->calculateOperation(
                                                            $convertions,
                                                            $entryAccount['entries']['currency']['id'],
                                                            $entryAccount['debit'],
                                                            $entryAccount['entries']['from_date'],
                                                            ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                            ) : 0;
                                                        $r['assets'] = ($entryAccount['assets'] != 0) ?
                                                        $this->calculateOperation(
                                                            $convertions,
                                                            $entryAccount['entries']['currency']['id'],
                                                            $entryAccount['assets'],
                                                            $entryAccount['entries']['from_date'],
                                                            ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                                            ) : 0;
                                                        array_push($accData[$cont]['entryAccount'], $r);
                                                        $cont++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        /**
         * [$setting configuración general de la aplicación]
         * @var [Modules\Accounting\Models\Setting]
         */
        $setting  = Setting::all()->first();
        $initDate = new DateTime($initDate);
        $endDate  = new DateTime($endDate);

        $initDate = $initDate->format('d/m/Y');
        $endDate  = $endDate->format('d/m/Y');

        /**
         * [$pdf base para generar el pdf]
         * @var [Modules\Accounting\Pdf\Pdf]
         */
        $pdf = new ReportRepository();

        /*
         *  Definicion de las caracteristicas generales de la página pdf
         */
        $institution = Institution::find(1);
        $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('report/auxiliaryBook/' . $report->id)]);
        $pdf->setHeader('Reporte de Contabilidad', 'Reporte de libro Auxiliar');
        $pdf->setFooter();
        $pdf->setBody('accounting::pdf.auxiliary_book', true, [
            'pdf'      => $pdf,
            'record'   => $acc ?? $accData,
            'initDate' => $initDate,
            'endDate'  => $endDate,
            'currency' => $currency,
        ]);
    }

    /**
     * [pdf vista en la que se genera el reporte en pdf y se realiza la firma electrónica del mismo ]
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     * @param  integer $report [id de reporte y su informacion]
     */
    public function pdfSign($report)
    {
        $report     = AccountingReportHistory::with('currency')->find($report);
        // Validar acceso para el registro
        if (!auth()->user()->isAdmin()) {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            if ($report && $report->queryAccess($user_profile['institution']['id'])) {
                return view('errors.403');
            }
        }
        $date       = explode('/', $report->url)[1];
        $account_id = explode('/', $report->url)[2];
        $initMonth  = (int)explode('-', $date)[1];
        $initYear   = (int)explode('-', $date)[0];

        if ($initMonth < 10) {
            $initMonth = '0' . $initMonth;
        }
        $date     = $initYear . '-' . $initMonth;

        $currency = $report->currency;

        /**
         * [$initDate fecha inicial de busqueda]
         * @var string
         */
        $initDate = $date . '-01';

        /**
         * [$day ultimo dia correspondiente al mes]
         * @var date
         */
        $day = date('d', (mktime(0, 0, 0, explode('-', $date)[1] + 1, 1, explode('-', $date)[0]) - 1));

        /**
         * [$endDate fecha final de busqueda]
         * @var string
         */
        $endDate = $date . '-' . $day;

        $institution_id = null;

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }


        $convertions = [];
        if (!$account_id) {
            // todas las cuentas auxiliares
            $query = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                }])
            ->where('group', '>', '0')
            ->where('subgroup', '>', '0')
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')->get();

            $acc  = [];
            $cont = 0;
            foreach ($query as $account) {
                array_push($acc, [
                    'id'           => $account['id'],
                    'denomination' => $account['denomination'],
                    'code'         => $account->getCodeAttribute(),
                    'entryAccount' => [],
                ]);
                /*
                 * Se recorre y evalua la relacion en las conversiones necesarias a realizar
                 */
                foreach ($account['entryAccount'] as $entryAccount) {
                    if ($entryAccount['entries']) {
                        $r = [
                                'debit'      => '0',
                                'assets'     => '0',
                                'entries'    => [
                                    'reference'  => $entryAccount['entries']['reference'],
                                    'concept'    => $entryAccount['entries']['concept'],
                                    'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                                ],
                            ];

                        if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                            $convertions = $this->calculateExchangeRates(
                                $convertions,
                                $entryAccount['entries'],
                                $currency['id']
                            );
                        }

                        $r['debit'] = ($entryAccount['debit'] != 0) ?
                                        $this->calculateOperation(
                                            $convertions,
                                            $entryAccount['entries']['currency']['id'],
                                            $entryAccount['debit'],
                                            $entryAccount['entries']['from_date'],
                                            ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                        ) : 0;

                        $r['assets'] = ($entryAccount['assets'] != 0) ?
                                        $this->calculateOperation(
                                            $convertions,
                                            $entryAccount['entries']['currency']['id'],
                                            $entryAccount['assets'],
                                            $entryAccount['entries']['from_date'],
                                            ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                        ) : 0;
                        array_push($acc[$cont]['entryAccount'], $r);
                    }
                }
                $cont++;
            }
        } elseif ($account_id) {
            // Una sola cuenta auxiliar
            $account = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                }])->find($account_id);

            $acc[0] = [
                'id'             => $account['id'],
                'denomination'   => $account['denomination'],
                'code'           => $account->getCodeAttribute(),
                'entryAccount'   => [],
            ];
            /*
             * recorrido y formateo de informacion en arreglos para mostrar en pdf
             */
            foreach ($account['entryAccount'] as $entryAccount) {
                if ($entryAccount['entries']) {
                    $r = [
                            'debit'      => '0',
                            'assets'     => '0',
                            'entries'    => [
                                'reference'  => $entryAccount['entries']['reference'],
                                'concept'    => $entryAccount['entries']['concept'],
                                'created_at' => $entryAccount['entries']['created_at']->format('d/m/Y'),
                            ],
                        ];

                    if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                        $convertions = $this->calculateExchangeRates(
                            $convertions,
                            $entryAccount['entries'],
                            $currency['id']
                        );
                    }

                    $r['debit'] = ($entryAccount['debit'] != 0) ?
                                    $this->calculateOperation(
                                        $convertions,
                                        $entryAccount['entries']['currency']['id'],
                                        $entryAccount['debit'],
                                        $entryAccount['entries']['from_date'],
                                        ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                    ) : 0;

                    $r['assets'] = ($entryAccount['assets'] != 0) ?
                                    $this->calculateOperation(
                                        $convertions,
                                        $entryAccount['entries']['currency']['id'],
                                        $entryAccount['assets'],
                                        $entryAccount['entries']['from_date'],
                                        ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                                    ) : 0;

                    array_push($acc[0]['entryAccount'], $r);
                }
            }
        }
        /**
         * [$setting configuración general de la aplicación]
         * @var [Modules\Accounting\Models\Setting]
         */
        $setting  = Setting::all()->first();
        $initDate = new DateTime($initDate);
        $endDate  = new DateTime($endDate);

        $initDate = $initDate->format('d/m/Y');
        $endDate  = $endDate->format('d/m/Y');

        /**
         * [$pdf base para generar el pdf]
         * @var [Modules\Accounting\Pdf\Pdf]
         */
        //$pdf = new ReportRepository();
        $pdf = new ReportRepositorySign();

        /*
         *  Definicion de las caracteristicas generales de la página pdf
         */
        $institution = Institution::find(1);
        $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('report/auxiliaryBook/' . $report->id)]);
        $pdf->setHeader('Reporte de Contabilidad', 'Reporte de libro Auxiliar');
        $pdf->setFooter();
        $report = $pdf->setBody('accounting::pdf.auxiliary_book', true, [
            'pdf'      => $pdf,
            'record'   => $acc,
            'initDate' => $initDate,
            'endDate'  => $endDate,
            'currency' => $currency,
        ]);
        if ($report['status'] == 'true') {
            return response()->download($report['file'], $report['filename'], [], 'inline');
        } else {
            return response()->json(['result' => $report['status'], 'message' => $report['message']], 200);
        }
    }
    /**
     * [calculateOperation realiza la conversion de saldo]
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     * @param  array   $convertions   [lista de tipos cambios para la moneda]
     * @param  integer $entry_id      [identificador del asiento]
     * @param  float   $value         [saldo del asiento]
     * @param  float   $date         [fecha del asiento]
     * @param  boolean $equalCurrency [bandera que indica si el tipo de moneda en el que esta el asiento es las misma
     *                                que la que se desea expresar]
     * @return float                  [resultdado de la operacion]
     */
    public function calculateOperation($convertions, $currency_id, $value, $date, $equalCurrency)
    {
        if ($equalCurrency) {
            return $value;
        }

        if ($currency_id && array_key_exists($currency_id, $convertions) && $convertions[$currency_id]) {
            foreach ($convertions[$currency_id] as $convertion) {
                if ($date >= $convertion['start_at'] && $date <= $convertion['end_at']) {
                    if ($convertion['operator'] == 'to') {
                        return ($value * $convertion['amount']);
                    } else {
                        return ($value / $convertion['amount']);
                    }
                }
            }
        }
        return -1;
    }

    /**
     * [calculateExchangeRates encuentra los tipos de cambio]
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     * @param  array           $convertions [lista de conversiones]
     * @param  AccountingEntry $entry       [asiento contable]
     * @param  integer         $currency_id [identificador de la moneda a la cual se realizara la conversion]
     * @return array                        [lista de conversiones actualizada]
     */
    public function calculateExchangeRates($convertions, $entry, $currency_id)
    {
        $exchangeRate = ExchangeRate::where('active', true)
                            ->whereIn('to_currency_id', [$entry['currency']['id'], $currency_id])
                            ->whereIn('from_currency_id', [$entry['currency']['id'], $currency_id])
                             ->orderBy('end_at', 'DESC')->get();
        if (count($exchangeRate) != 0) {
            if (!array_key_exists($entry['currency']['id'], $convertions)) {
                $convertions[$entry['currency']['id']] = [];
                foreach ($exchangeRate as $recordExchangeRate) {
                    array_push(
                        $convertions[$entry['currency']['id']],
                        [
                            'amount'   => $recordExchangeRate->amount,
                            'operator' => ($currency_id == $recordExchangeRate->from_currency_id) ? 'from' : 'to',
                            'start_at' => $recordExchangeRate->start_at,
                            'end_at'   => $recordExchangeRate->end_at
                        ]
                    );
                }
            }
        }
        return $convertions;
    }

    public function getCheckBreak()
    {
        return $this->PageBreakTrigger;
    }

    /**
     * Método para buscar las cuentas padre de una formulación
     *
     * @method    getAccountParents
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    Array con las cuentas padre de la formulación
     */
    public function getAccountParents($childData, $parents = [])
    {
        $child = AccountingAccount::find($childData['id']);
        $child['balance'] = $childData['balance'];
        $child['beginningBalance'] = $childData['beginningBalance'];
        $child['level'] = $childData['level'] - 1;

        if (!isset($child)) {
            return $parents;
        }

        if (!array_key_exists($child->id, $parents)) {
            $parents[$child->id] = $child;
        }

        if ($child->parent_id == null) {
            return $parents;
        } else {
            $child->load('parent');
            $parent = $child->parent;
            $parent['balance'] += $child['balance'];
            $parent['beginningBalance'] += $child['beginningBalance'];
            $parent['level'] = $child['level'];
            return $this->getAccountParents($parent, $parents);
        }
    }
}
