<?php

namespace Modules\Purchase\Http\Controllers\Reports\Base;

use App\Models\Parameter;
use App\Repositories\Contracts\ReportInterface;
use Carbon\Carbon;
use Elibyy\TCPDF\TCPDF as PDF;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class ReportRepository
 * @brief Gestiona los reportes de la aplicación
 *
 * Gestiona los reportes de la aplicación
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ReportRepository implements ReportInterface
{
    /**
     * Establece la orientación de la página, los posibles valores son P o L
     *
     * @var string $orientation
     */
    private $orientation;

    /**
     * Establece la unidad de medida a implementar en el reporte
     *
     * @var string $units
     */
    private $units;

    /**
     * Establece el formato de la página (A4, Letter, ...)
     *
     * @var string $format
     */
    private $format;

    /**
     * Establece el tipo de fuente a usar en el reporte
     *
     * @var string $fontFamily
     */
    private $fontFamily;

    /**
     * Estilos a implementar en códigos QR a generar
     *
     * @var array $qrCodeStyle
     */
    private $qrCodeStyle;

    /**
     * Estilos a implementar en códigos de barras a generar
     *
     * @var array $barCodeStyle
     */
    private $barCodeStyle;

    /**
     * Estilos para líneas de separación entre encabezado cuerpo y pie de página
     *
     * @var string $lineStyle
     */
    private $lineStyle;

    /**
     * URL de verificación del reporte
     *
     * @var string $urlVerify
     */
    private $urlVerify;

    /**
     * Fecha en la que se genera el reporte
     *
     * @var string $reportDate
     */
    private $reportDate;

    /**
     * Identificador de la organización que genera el reporte
     *
     * @var object $institution
     */
    private $institution;

    /**
     * Nombre del archivo a generar con el reporte
     *
     * @var string $filename
     */
    private $filename;

    /**
     * Título del reporte
     *
     * @var string $title
     */
    private $title;

    /**
     * Asunto del reporte
     *
     * @var string $subject
     */
    private $subject;

    /**
     * Establece el eje de las Y en donde comienza a mostrarse el encabezado del reporte
     *
     * @var integer $headerY
     */
    private $headerY;

    /**
     * Establece el eje de las Y para el texto de subtítulo y fecha del reporte
     *
     * @var integer $headerTextY
     */
    private $headerTextY;

    /**
     * Crea y gestiona el objeto PDF
     *
     * @var object $pdf
     */
    private $pdf;

    /**
     * Método constructor de la clase
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        $this->pdf = new PDF(config('app.name'));
    }

    /**
     * Método que permite establecer la configuración general de los reportes
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      array    $params    Parámetros de configuración
     */
    public function setConfig($params = [])
    {
        $this->reportDate = $params['reportDate'] ?? Carbon::now()->format('d-m-Y');
        $this->orientation = $params['orientation'] ?? 'P';
        $this->units = $params['units'] ?? 'mm';
        $this->format = $params['format'] ?? 'LETTER';
        $this->fontFamily = $params['fontFamily'] ?? 'helvetica';
        $this->qrCodeStyle = $params['qrCodeStyle'] ?? [
            'border' => false,
            'padding' => 0,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
        ];
        $this->barCodeStyle = $params['barCodeStyle'] ?? [
            'border' => 0,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1, // height of a single module in points
        ];
        $this->lineStyle = $params['lineStyle'] ?? [
            'width' => 0.2,
            'cap' => 'butt',
            'join' => 'miter',
            'dash' => 0,
            'color' => [0, 0, 0],
        ];
        $this->urlVerify = $params['urlVerify'] ?? null;
        $this->institution = $params['institution'] ?? null;
        $this->headerY = (is_null($this->institution->banner)) ? 10 : 22;
        $this->headerTextY = ($this->headerY === 22) ? 30 : 22;
        $this->filename = $params['filename'] ?? uniqid() . 'pdf';
        $this->subject = '';
    }

    /**
     * Método que establece los datos a mostrar en el encabezado del reporte
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      string           $title         Título del reporte
     * @param      string           $subTitle      Subtítulo o descripción del reporte
     * @param      boolean          $hasQR         Indica si el reporte dispone de un código QR de verificación
     * @param      boolean          $hasBarCode    Indica si el reporte dispone de un código de barras que identifica
     *                                             el serial del documento
     *
     * @param      string           $logoAlign     Alineación del logo en el reporte. Los valores posibles son:
     *                                             (L)eft, (R)ight, (C)enter. Valor por defecto es vacio lo cual
     *                                             realiza la alineación en el centro
     */
    public function setHeader(
        $title = '',
        $subTitle = '',
        $code = '',
        $hasQR = true,
        $hasBarCode = false,
        $logoAlign = '',
        $titleAlign = 'C',
        $subTitleAlign = 'C'
    ) {
        $params = (object) [
            'institution' => $this->institution,
            'fontFamily' => $this->fontFamily,
            'barCodeStyle' => $this->barCodeStyle,
            'qrCodeStyle' => $this->qrCodeStyle,
            'lineStyle' => $this->lineStyle,
            'hasQR' => $hasQR,
            'hasBarCode' => $hasBarCode,
            'urlVerify' => $this->urlVerify,
            'title' => $title,
            'code' => $code,
            'titleAlign' => $titleAlign,
            'subTitle' => $subTitle,
            'subTitleAlign' => $subTitleAlign,
            'reportDate' => $this->reportDate,
            'headerY' => $this->headerY,
            'logoAlign' => $logoAlign,
        ];
        $this->title = $title ?? 'Reporte';
        $this->pdf->setHeaderCallback(function ($pdf) use ($params) {
            $parameter = Parameter::where(['p_key' => 'report_banner', 'p_value' => 'true'])->first();

            if (!is_null($params->institution->banner)) {
                /* Imagen del banner de la organización a implementar en el reporte */
                $pdf->Image(
                    storage_path('pictures') . '/' . $params->institution->banner->file,
                    10,
                    10,
                    '',
                    10,
                    '',
                    '',
                    'T',
                    false,
                    300,
                    'C',
                    false,
                    false,
                    0,
                    false,
                    false,
                    true
                );
            }
            if (!is_null($params->institution->logo)) {
                /* Imagen del logotipo de la organización a implementar en el reporte */
                $pdf->Image(
                    storage_path('pictures') . '/' . $params->institution->logo->file,
                    10,
                    $params->headerY,
                    25,
                    '',
                    '',
                    '',
                    'T',
                    false,
                    300,
                    $params->logoAlign,
                    false,
                    false,
                    0,
                    false,
                    false,
                    false
                );
            }
            if ($params->hasQR && !is_null($params->urlVerify)) {
                /* Código QR con enlace de verificación del reporte */
                $pdf->write2DBarcode(
                    $params->urlVerify,
                    'QRCODE,H',
                    190,
                    $params->headerY,
                    12,
                    12,
                    $params->qrCodeStyle,
                    'T'
                );
            }
            /* Configuración de la fuente para el título del reporte */
            $pdf->SetFont($params->fontFamily, 'B', 12);
            /* Título del reporte */
            $pdf->MultiCell(
                145,
                4,
                $params->title,
                0,
                $params->titleAlign,
                false,
                1,
                40,
                $params->headerY,
                true,
                0,
                false,
                true,
                0,
                'T',
                true
            );
            $pdf->SetTextColor(246, 0, 28);

            if ($params->code) {
                $pdf->MultiCell(
                    $pdf->getPageWidth() - 130,
                    4,
                    "N° " . $params->code,
                    0,
                    'R',
                    false,
                    1,
                    113,
                    15,
                    true,
                    1,
                    false,
                    true,
                    0,
                    'T',
                    true
                );
            } else {
                $pdf->MultiCell(
                    $pdf->getPageWidth() - 130,
                    4,
                    '',
                    0,
                    'R',
                    false,
                    1,
                    113,
                    15,
                    true,
                    1,
                    false,
                    true,
                    0,
                    'T',
                    true
                );
            }

            $pdf->SetTextColor(0, 0, 0);

            /* Configuración de la fuente para la breve descripción del reporte */
            $pdf->SetFont($params->fontFamily, 'B', 12);
            /* Descripción breve del reporte */
            $pdf->MultiCell(
                145,
                7,
                $params->subTitle,
                0,
                $params->subTitleAlign,
                false,
                1,
                40,
                $params->headerY + 8,
                true,
                1,
                false,
                true,
                0,
                'T',
                true
            );
            /* Fecha de emisión del reporte */
            $pdf->MultiCell(
                $pdf->getPageWidth() - 140,
                4,
                $params->reportDate,
                0,
                'R',
                false,
                1,
                113,
                $params->headerY + 8,
                true,
                1,
                false,
                true,
                0,
                'T',
                true
            );
            /* Línea de separación entre el encabezado del reporte y el cuerpo */
            $pdf->Line(
                7,
                $params->headerY + 15,
                $pdf->getPageWidth() - $params->headerY,
                $params->headerY + 15,
                $params->lineStyle
            );
        });
    }

    /**
     * Método que permite agregar el contenido del reporte a generar
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      string         $body        Plantilla a utilizar para el reporte en caso de estar establecido
     *                                         como isHTML, en caso contrario será un texto a incluir en el cuerpo
     *                                         del reporte
     * @param      boolean        $isHTML      Establece si el cuerpo del reporte es una plantilla de blade a renderizar
     * @param      array          $htmlParams  Conjunto de parámetros requeridos por la plantilla de blade
     * @param      string         $storeAction Acción para la generación del documento
     */
    public function setBody($body, $isHTML = true, $htmlParams = [], $storeAction = 'I')
    {
        /* Contenido del reporte */
        $htmlContent = $body;
        /* Configuración sobre el autor del reporte */
        $this->pdf->SetAuthor(__('Sistema de Gestión de Recursos - :app', ['app' => config('app.name')]));
        /* Configuración del título de reporte */
        $this->pdf->SetTitle($this->title);
        /* Configuración sobre el asunto del reporte */
        $this->pdf->SetSubject($this->subject);
        /* Configuración de los márgenes del cuerpo del reporte */
        $this->pdf->SetMargins(7, 45, 7);
        /* Establece si se configura o no las fuentes para sub configuraciones */
        $this->pdf->SetFontSubsetting(false);
        /* Configuración de la fuente por defecto del cuerpo del reporte */
        $this->pdf->SetFontSize('10px');
        /* Configuración que permite realizar un salto de página automático al alcanzar el límite inferior del cuerpo del reporte */
        $this->pdf->SetAutoPageBreak(true, 15); //PDF_MARGIN_BOTTOM
        /* Agrega las respectivas páginas del reporte */
        $this->pdf->AddPage($this->orientation, $this->format);

        if ($isHTML) {
            $view = View::make($body, $htmlParams);
            $htmlContent = $view->render();
        }
        /* Escribre el contenido del reporte */
        $this->pdf->writeHTML($htmlContent, true, false, true, false, '');

        /* Establece el apuntador del reporte a la última página generada */
        $this->pdf->lastPage();
        /*
         | Genera el reporte. Las opciones disponibles son:
         |
         | I: Genera el archivo directamente para ser visualizado en el navegador
         | D: Genera el archivo y forza la descarga del mismo
         | F: Guarda el archivo generado en la ruta del servidor establecida por defecto
         | S: Devuelve el documento generado como una cadena de texto
         | FI: Es equivalente a las opciones F + I
         | FD: Es equivalente a las opciones F + D
         | E: Devuelve el documento del tipo mime base64 para ser adjuntado en correos electrónicos
         */
        $this->pdf->Output($this->filename, $storeAction);
    }

    /**
     * Establece la configuración del pie de página
     *
     * @param boolean $pages Indica si se debe mostrar o no el número de páginas
     * @param string $footerText Texto a mostrar en el pie de página
     *
     * @return void
     */
    public function setFooter($pages = true, $footerText = '')
    {
        $fontFamily = $this->fontFamily;
        $lineStyle = $this->lineStyle;
        if (empty($footerText)) {
            $footerText = $this->institution->legal_address;
        }

        $this->pdf->setFooterCallback(function ($pdf) use ($pages, $fontFamily, $footerText, $lineStyle) {
            /* Posición a 14 mm del borde inferior de la página*/
            $pdf->SetY(-14);
            /* Configuración de la fuenta a utilizar */
            $pdf->SetFont($fontFamily, 'I', 8);
            if ($pages) {
                /* @var string Número de página del reporte */
                $pageNumber = __('Pág. ') . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages();
                /* Texto a mostrar para el número de página */
                $pdf->MultiCell(20, 4, $pageNumber, 0, 'R', false, 0, 185, -8, true, 1, false, true, 1, 'T', true);
            }
            /* Texto a mostrar en el pie de página del reporte */
            $pdf->MultiCell(
                $pdf->getPageWidth() - PDF_MARGIN_RIGHT,
                8,
                $footerText,
                0,
                'C',
                false,
                0,
                7,
                -12,
                true,
                1,
                true,
                true,
                0,
                'T',
                true
            );
            /* Línea de separación entre el cuerpo del reporte y el pie de página */
            $pdf->Line(7, 265, 205, 265, $lineStyle);
        });
    }

    /**
     * Descarga un reporte
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     string|null    $file          Nombre del archivo a descargar. Este dato es opcional,
     *                                          si no se indica se genera un archivo con la fecha actual del
     *                                          servidor como nombre
     * @param     string         $outputMethod  Método a usar para mostrar o descargar el documento
     *
     * @return    BinaryFileResponse
     */
    public function show($file = null, $outputMethod = 'F')
    {
        $filename = storage_path() . '/reports/' . $file ?? 'report' . Carbon::now() . '.pdf';
        $this->pdf->Output($filename, $outputMethod);
        return response()->download($filename);
    }
}
