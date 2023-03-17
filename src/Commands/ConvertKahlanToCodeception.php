<?php

declare(strict_types=1);

namespace Komtaki\KahlanToCodeception\Commands;

use Komtaki\KahlanToCodeception\Converters\KahlanToCodeceptionConverter;
use Komtaki\KahlanToCodeception\Exceptions\RuntimeException;
use Komtaki\KahlanToCodeception\FileSystem\FileGenerator;

use const PHP_EOL;

final class ConvertKahlanToCodeception implements CommandInterface
{
    public function __construct(
        private string $targetDir
    ) {
    }

    public function run(): void
    {
        $converter = new KahlanToCodeceptionConverter();

        try {
            $fileGenerator = new FileGenerator($converter);
            $fileGenerator->generate($this->targetDir);
        } catch (RuntimeException $e) {
            echo $e->getMessage() . PHP_EOL;
            exit(1);
        }
    }
}
