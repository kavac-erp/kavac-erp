<?php

declare(strict_types=1);

namespace Modules\Asset\Enums\Traits;

/**
 * @trait   Trait para la conversión de enum a array
 * @brief Trait para la conversión de enum a array
 *
 * Trait para la conversión de enum a array
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
trait EnumToArray
{
    /** @return string[] */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return array<string, string> */
    public static function toArray(): array
    {
        return array_combine(self::values(), self::names());
    }
}
