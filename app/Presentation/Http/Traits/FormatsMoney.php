<?php

declare(strict_types=1);

namespace App\Presentation\Http\Traits;

/**
 * Trait para formatação de valores monetários em Real brasileiro.
 */
trait FormatsMoney
{
    /**
     * Formata um valor numérico para o padrão monetário brasileiro.
     *
     * @param string|float $value Valor a ser formatado
     * @return string Valor formatado (ex: "R$ 1.234,56")
     */
    protected function formatMoney(string|float $value): string
    {
        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }
}
