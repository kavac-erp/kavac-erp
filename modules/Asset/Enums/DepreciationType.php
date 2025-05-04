<?php

declare(strict_types=1);

namespace Modules\Asset\Enums;

use Modules\Asset\Enums\Attributes\NameAttribute;
use Modules\Asset\Enums\Attributes\FormulaAttribute;
use Modules\Asset\Enums\Attributes\TranslateFormulaAttribute;
use Modules\Asset\Enums\Attributes\PublicAttribute;
use Modules\Asset\Enums\Traits\EnumToArray;
use Modules\Asset\Enums\Traits\HasAttributes;

enum DepreciationType: int
{
    use EnumToArray;
    use HasAttributes;

    #[NameAttribute('Método de depreciación en linea recta')]
    #[TranslateFormulaAttribute('(( acquisition_value - residual_value) / depresciation_years)')]
    #[FormulaAttribute('(( Valor de Adquisicion de Bien - Valor residual) / vida útil del bien)')]
    #[PublicAttribute(true)]
    case STRAIGHT_LINE = 1;

    #[NameAttribute('Método de depreciación de la suma de los dígitos del año')]
    #[TranslateFormulaAttribute('')]
    #[FormulaAttribute('')]
    #[PublicAttribute(false)]
    case DIGITS_SUM = 2;

    #[NameAttribute('Método de depreciación por unidades de producción')]
    #[TranslateFormulaAttribute('')]
    #[FormulaAttribute('')]
    #[PublicAttribute(false)]
    case PRODUCTION_UNITS = 3;

    #[NameAttribute('Método de depreciación por reducción de saldos')]
    #[TranslateFormulaAttribute('')]
    #[FormulaAttribute('')]
    #[PublicAttribute(false)]
    case BALANCE_REDUCTION = 4;

    #[NameAttribute('Método de depreciación acelerada')]
    #[TranslateFormulaAttribute('')]
    #[FormulaAttribute('')]
    #[PublicAttribute(false)]
    case SPEED = 5;

    public function getName(): string
    {
        return $this->getAttribute(NameAttribute::class);
    }

    public function getFormula(): string
    {
        return $this->getAttribute(FormulaAttribute::class);
    }

    public function getTranslateFormula(): string
    {
        return $this->getAttribute(TranslateFormulaAttribute::class);
    }

    public function getPublic(): bool
    {
        return $this->getAttribute(PublicAttribute::class);
    }
}
