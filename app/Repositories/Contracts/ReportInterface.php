<?php

namespace App\Repositories\Contracts;

/**
 * @brief Interfaz para la gestión de reportes
 *
 * Interfaz para la gestión de reportes
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
interface ReportInterface
{
    public function setHeader($title);

    public function setBody($body);

    public function setFooter($pages);
}
