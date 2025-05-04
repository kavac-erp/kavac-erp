<?php

namespace App\Exceptions;

/**
 * @class RestrictedRegistryDeletionException *
 * @brief Excepción para eliminación de registros que tengan relación con otros.
 *
 * Gestiona la excepción relacionada con la eliminación de un registro que
 * tenga relaciones con otros.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
final class RestrictedRegistryDeletionException extends \Exception
{
}
