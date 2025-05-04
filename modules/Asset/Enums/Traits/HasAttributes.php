<?php

declare(strict_types=1);

namespace Modules\Asset\Enums\Traits;

use Illuminate\Support\Str;

/**
 * @trait   Trait para verificar atributos
 * @brief Trait para verificar atributos
 *
 * Trait para verificar atributos
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
trait HasAttributes
{
    /** get attribute value */
    public function getAttribute(string $attributeClass): string|int|bool|null
    {
        $attributes = $this->getReflectionAttributes($attributeClass);

        return 0 < count($attributes)
            ? $attributes[array_key_first($attributes)]?->getArguments()[0]
            : null;
    }

    /**
     * get attribute value or fail.
     *
     * @throws \Exception
     */
    public function getAttributeOrFail(string $Attribute): string|int|bool
    {
        return $this->getAttribute($Attribute) ?? throw new \Exception($this->getErrorMessage($Attribute));
    }

    /** get attribute instance */
    public function getAttributeInstance(string $attributeClass): object|null
    {
        $attributes = $this->getReflectionAttributes($attributeClass);

        return 0 < count($attributes)
            ? $attributes[array_key_first($attributes)]->newInstance()
            : null;
    }

    /**
     * get attribute insatance or fail.
     *
     * @throws \Exception
     */
    public function getAttributeInstanceOrFail(string $Attribute): object
    {
        return $this->getAttributeInstance($Attribute) ?? throw new \Exception($this->getErrorMessage($Attribute));
    }

    protected function getReflectionAttributes(string $attributeClass): array
    {
        $classAttributes = (new \ReflectionClassConstant(self::class, $this->name))->getAttributes();

        return array_filter($classAttributes, function ($classAttribute) use ($attributeClass) {
            return $classAttribute->getName() === $attributeClass;
        });
    }

    protected function getErrorMessage(string $Attribute): string
    {
        return Str::replaceArray('?', [
            Str::afterLast($Attribute, '\\'),
            Str::afterLast(self::class, '\\'),
            $this->name,
        ], $this->errorMessage());
    }

    protected function errorMessage(): string
    {
        return 'Attribute "?" not found on enum "?" on case "?"';
    }
}
