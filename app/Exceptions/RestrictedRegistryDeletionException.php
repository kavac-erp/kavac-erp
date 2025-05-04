<?php

namespace App\Exceptions;

/**
 * @class RestrictedRegistryDeletionException
 *
 * @brief Excepcion para eliminación de registros que tengan relación con otros.
 *
 * Gestiona la excepcion relacionada con la eliminación de un registro que
 * tenga relaciones con otros.
 * 
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 */
final class RestrictedRegistryDeletionException extends \Exception
{
}
