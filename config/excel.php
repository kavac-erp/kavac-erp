<?php

use Maatwebsite\Excel\Excel;

return [

    'exports' => [

        /*
        |--------------------------------------------------------------------------
        | Tamaño de porción
        |--------------------------------------------------------------------------
        |
        | Al usar FromQuery, la consulta se fragmenta automáticamente.
        | Aquí puede especificar qué tan grande debe ser el trozo.
        |
        */
        'chunk_size'             => 1000,

        /*
        |--------------------------------------------------------------------------
        | Precalcular fórmulas durante la exportación
        |--------------------------------------------------------------------------
        */
        'pre_calculate_formulas' => false,

        /*
        |--------------------------------------------------------------------------
        | Configuración de CSV
        |--------------------------------------------------------------------------
        |
        | Configurar, ej. delimiter, enclosure y line_ending para exportaciones CSV.
        |
        */
        'csv'                    => [
            'delimiter'              => ',',
            'enclosure'              => '"',
            'line_ending'            => PHP_EOL,
            'use_bom'                => false,
            'include_separator_line' => false,
            'excel_compatibility'    => false,
        ],
    ],

    'imports'            => [

        'read_only' => true,

        'heading_row' => [

            /*
            |--------------------------------------------------------------------------
            | Formateador de fila de encabezado
            |--------------------------------------------------------------------------
            |
            | Configure el formateador de filas de títulos.
            | Opciones Disponibles: none|slug|custom
            |
            */
            'formatter' => 'slug',
        ],

        /*
        |--------------------------------------------------------------------------
        | Configuración de CSV
        |--------------------------------------------------------------------------
        |
        | Configurar, ej. delimiter, enclosure and line ending para importaciones CSV.
        |
        */
        'csv'         => [
            'delimiter'              => ',',
            'enclosure'              => '"',
            'escape_character'       => '\\',
            'contiguous'             => false,
            'input_encoding'         => 'UTF-8',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Detector de extensión
    |--------------------------------------------------------------------------
    |
    | Configure aquí qué tipo de escritor debe usarse cuando el paquete necesita
    | adivinar el tipo correcto basándose solo en la extensión.
    |
    */
    'extension_detector' => [
        'xlsx'     => Excel::XLSX,
        'xlsm'     => Excel::XLSX,
        'xltx'     => Excel::XLSX,
        'xltm'     => Excel::XLSX,
        'xls'      => Excel::XLS,
        'xlt'      => Excel::XLS,
        'ods'      => Excel::ODS,
        'ots'      => Excel::ODS,
        'slk'      => Excel::SLK,
        'xml'      => Excel::XML,
        'gnumeric' => Excel::GNUMERIC,
        'htm'      => Excel::HTML,
        'html'     => Excel::HTML,
        'csv'      => Excel::CSV,
        'tsv'      => Excel::TSV,

        /*
        |--------------------------------------------------------------------------
        | Extensión PDF
        |--------------------------------------------------------------------------
        |
        | Configure aquí qué controlador de PDF se debe utilizar de forma predeterminada.
        | Opciones disponibles: Excel::MPDF | Excel::TCPDF | Excel::DOMPDF
        |
        */
        'pdf'      => Excel::DOMPDF,
    ],

    'value_binder' => [

        /*
        |--------------------------------------------------------------------------
        | Binder de valores predeterminados
        |--------------------------------------------------------------------------
        |
        | PhpSpreadsheet ofrece una forma de engancharse al proceso de escribir un
        | valor en una celda. Allí se hacen algunas suposiciones sobre cómo se debe
        | formatear el valor. Si desea cambiar esos valores predeterminados, puede
        | implementar su propio binder de valores predeterminados.
        |
        */
        'default' => Maatwebsite\Excel\DefaultValueBinder::class,
    ],

    'transactions' => [

        /*
        |--------------------------------------------------------------------------
        | Controlador de transacciones
        |--------------------------------------------------------------------------
        |
        | De forma predeterminada, la importación está envuelta en una transacción.
        | Esto es útil cuando una importación puede fallar y desea volver a intentarlo.
        | Con las transacciones, la importación anterior se revierte.
        |
        | Puede deshabilitar el controlador de transacciones configurando esto en nulo.
        | O puede elegir un controlador de transacciones personalizado aquí.
        |
        | Controladores admitidos: null|db
        |
        */
        'handler' => 'db',
    ],

    'temporary_files' => [

        /*
        |--------------------------------------------------------------------------
        | Ruta temporal local
        |--------------------------------------------------------------------------
        |
        | Al exportar e importar archivos, usamos un archivo temporal, antes de almacenar,
        | leer o descargar. Aquí puedes personalizar esa ruta.
        |
        */
        'local_path'  => sys_get_temp_dir(),

        /*
        |--------------------------------------------------------------------------
        | Disco temporal remoto
        |--------------------------------------------------------------------------
        |
        | Cuando se trata de una configuración de varios servidores con colas en las
        | que no puede confiar en tener una ruta temporal local compartida, es posible
        | que desee almacenar el archivo temporal en un disco compartido.
        | Durante la ejecución de la cola, recuperaremos el archivo temporal de esa ubicación.
        | Cuando se deja en nulo, siempre usará la ruta local. Esta configuración solo
        | tiene efecto cuando se usa junto con importaciones y exportaciones en cola.
        |
        */
        'remote_disk' => null,

    ],
];
