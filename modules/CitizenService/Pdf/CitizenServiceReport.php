<?php

namespace Modules\CitizenService\Pdf;

use Carbon\Carbon;
use App\Models\Parameter;
use App\Models\Institution;
use Illuminate\Http\Response;
use Elibyy\TCPDF\TCPDF as PDF;
use Illuminate\Support\Facades\View;
use App\Repositories\Contracts\ReportInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class CitizenServiceReport
 * @brief Gestiona los reportes de la oficina de atención al ciudadano
 *
 * @author Ing. Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceReport implements ReportInterface
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
     * Identificador de la institución que genera el reporte
     *
     * @var Institution $institution
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
    protected $pdf;

    /**
     * Método constructor de la clase
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return     void
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
     *
     * @return     void
     */
    public function setConfig($params = [])
    {
        $this->reportDate = Carbon::now();
        $this->orientation = $params['orientation'] ?? 'P';
        $this->units = $params['units'] ?? 'mm';
        $this->format = $params['format'] ?? 'LETTER';
        $this->fontFamily = $params['fontFamily'] ?? 'helvetica';
        $this->qrCodeStyle = $params['qrCodeStyle'] ?? [
            'border' => false,
            'padding' => 0,
            'fgcolor' => [0,0,0],
            'bgcolor' => false
        ];
        $this->barCodeStyle = $params['barCodeStyle'] ?? [
            'border' => 0,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => [0,0,0],
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        ];
        $this->lineStyle = $params['lineStyle'] ?? [
            'width' => 0.2,
            'cap' => 'butt',
            'join' => 'miter',
            'dash' => 0,
            'color' => [0, 0, 0]
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
     */
    public function setHeader($title = '', $subTitle = '', $hasQR = true, $hasBarCode = false)
    {
        $params = (object)[
            'institution' => $this->institution,
            'fontFamily' => $this->fontFamily,
            'barCodeStyle' => $this->barCodeStyle,
            'qrCodeStyle' => $this->qrCodeStyle,
            'lineStyle' => $this->lineStyle,
            'hasQR' => $hasQR,
            'urlVerify' => $this->urlVerify,
            'title' => $title,
            'subTitle' => $subTitle,
            'reportDate' => $this->reportDate,
            'headerY' => $this->headerY
        ];
        $this->title = $title ?? 'Reporte';

        $this->pdf->setHeaderCallback(function ($pdf) use ($params) {
            $parameter = Parameter::where(['p_key' => 'report_banner', 'p_value' => 'true'])->first();

            if (!is_null($params->institution->banner)) {
                /* Imagen del banner institucional a implementar en el reporte */
                $pdf->Image(
                    storage_path('pictures') . '/' . $params->institution->banner->file,
                    7,
                    10,
                    $this->pdf->getPageWidth(),
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
                /* Imagen del logotipo institucional a implementar en el reporte */
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
                    '',
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
                    ($this->orientation == 'P') ? 190 : 260,
                    $params->headerY,
                    12,
                    12,
                    $params->qrCodeStyle,
                    'T'
                );
            }
            /* Configuración de la fuente para el título del reporte */
            $pdf->SetFont($params->fontFamily, 'B', 15);
            /* Título del reporte */
            $pdf->MultiCell(
                ($this->orientation == 'P') ? 145 : 215,
                7,
                $params->title,
                0,
                'C',
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
            /* Configuración de la fuente para la breve descripción del reporte */
            $pdf->SetFont($params->fontFamily, 'B', 12);
            /* Descripción breve del reporte */
            $pdf->MultiCell(
                ($this->orientation == 'P') ? 72 : 140,
                4,
                $params->subTitle,
                0,
                'L',
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
                ($this->orientation == 'P') ? 72 : 140,
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
                ($this->orientation == 'P') ? 208.9 : 272.5,
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
     *
     * @return     void
     */
    public function setBody($body, $isHTML = true, $htmlParams = [])
    {
        /* Contenido del reporte */
        $htmlContent = $body;
        /* Configuración sobre el autor del reporte */
        $this->pdf->SetAuthor('Sistema de Gestión de Recursos - ' . config('app.name'));
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
        /*
         * Configuración que permite realizar un salto de página automático al alcanzar el límite inferior del cuerpo
         * del reporte
         */
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
         * Genera el reporte. Las opciones disponibles son:
         *
         * I: Genera el archivo directamente para ser visualizado en el navegador
         * D: Genera el archivo y forza la descarga del mismo
         * F: Guarda el archivo generado en la ruta del servidor establecida por defecto
         * S: Devuelve el documento generado como una cadena de texto
         * FI: Es equivalente a las opciones F + I
         * FD: Es equivalente a las opciones F + D
         * E: Devuelve el documento del tipo mime base64 para ser adjuntado en correos electrónicos
         */
        $this->pdf->Output(storage_path() . '/reports/' . $this->filename, 'F');
    }

    /**
     * Establece los datos del pie de página del reporte
     *
     * @param boolean $pages Indica si se muestra o no el número de páginas del reporte
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
                /* Número de página del reporte */
                $pageNumber = "Pág. " . $pdf->getAliasNumPage() . "/" . $pdf->getAliasNbPages();
                /* Texto a mostrar para el número de página */
                $pdf->MultiCell(
                    30,
                    4,
                    $pageNumber,
                    0,
                    'R',
                    false,
                    1,
                    ($this->orientation == 'P') ? 190 : 252.5,
                    -8,
                    true,
                    1,
                    false,
                    true,
                    1,
                    'M',
                    true
                );
            }
            /* Texto a mostrar en el pie de página del reporte */
            $pdf->MultiCell(
                ($this->orientation == 'P') ? 198 : 242.5,
                8,
                $footerText,
                0,
                'C',
                false,
                1,
                7,
                -12,
                true,
                1,
                false,
                true,
                0,
                'T',
                true
            );
            /* Línea de separación entre el cuerpo del reporte y el pie de página */
            $pdf->Line(
                7,
                ($this->orientation == 'P') ? 265 : 203,
                ($this->orientation == 'P') ? 208.9 : 272.5,
                ($this->orientation == 'P') ? 265 : 203,
                $lineStyle
            );
        });
    }

    /**
     * Muestra el reporte generado
     *
     * @param string $file Nombre del archivo
     * @param string $dest Tipo de acción a ejecutar
     *
     * @return BinaryFileResponse|void
     */
    public function show($file = null, $dest = 'I')
    {
        $filename = storage_path() . '/reports/' . $file ?? 'citizenservice-report-' . Carbon::now() . '.pdf';
        $this->pdf->Output($filename, $dest);
        if ($dest == 'F') {
            return Response::download($filename);
        };
    }

    /**
     * Verifica el reporte para el salto de página
     *
     * @return float
     */
    public function getCheckBreak()
    {
        return $this->pdf->getPageHeight() - $this->pdf->getBreakMargin();
    }

    /**
     * Obtiene la altura de la página actual
     *
     * @return float
     */
    public function getPositionY()
    {
        return $this->pdf->GetY();
    }
}
