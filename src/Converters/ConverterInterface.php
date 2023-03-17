<?php

declare(strict_types=1);

namespace Komtaki\KahlanToCodeception\Converters;

interface ConverterInterface
{
    /**
     * ファイルを変換する
     */
    public function convert(string $filePath, string $code): string;
}
