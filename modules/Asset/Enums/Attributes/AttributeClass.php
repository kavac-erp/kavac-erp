<?php

/*
 | Esta clase representa los atributos enum del módulo de bienes.
 |
 | Esta clase es una clase abstracta que define la estructura básica para los atributos enum.
 | Los atributos enum son utilizados para describir diferentes valores posibles de un atributo en el módulo de bienes.
 */

declare(strict_types=1);

namespace Modules\Asset\Enums\Attributes;

use Attribute;

#[Attribute]
abstract class AttributeClass
{
    /**
     * Constructor de la clase.
     *
     * @param string|int|bool $value El valor del atributo enum.
     */
    public function __construct(
        public readonly string|int|bool $value,
    ) {
    }
}
