<?php

declare(strict_types=1);

namespace Komtaki\KahlanToCodeception\FileSystem;

use Komtaki\KahlanToCodeception\Converters\ConverterInterface;

use function file_get_contents;
use function file_put_contents;

class FileGenerator
{
    public function __construct(
        private ConverterInterface $converter
    ) {
    }

    public function generate(string $dirName): void
    {
        $filePaths = (new FileRecursiveSearcher())->getFileSystemPath($dirName);

        foreach ($filePaths as $filePath) {
            $code = file_get_contents($filePath);
            if (!$code) {
                continue;
            }

            $newCode = $this->converter->convert($filePath, $code);

            if ($newCode) {
                file_put_contents($filePath, $newCode);
            }
        }
    }
}
