<?php

declare(strict_types=1);

namespace Komtaki\KahlanToCodeception\Converters;

use Komtaki\KahlanToCodeception\Converters\Printers\Php7PreservingPrinter;
use Komtaki\KahlanToCodeception\Converters\Printers\PrinterInterface;
use Komtaki\KahlanToCodeception\Converters\Visitors\CollectGivenVisitor;
use Komtaki\KahlanToCodeception\Converters\Visitors\GetUseAliasVisitor;
use Komtaki\KahlanToCodeception\Converters\Visitors\GetDeclareVisitor;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;

final class KahlanToCodeceptionConverter implements ConverterInterface
{
    private PrinterInterface $printer;

    public function __construct(?PrinterInterface $printer = null)
    {
        $this->printer = $printer ?? new Php7PreservingPrinter();
    }

    /**
     * @inheritDoc
     */
    public function convert(string $filePath, string $code): string
    {
        $stmts = $this->printer->getAst($code);

        $className = $this->getClassName($filePath);

        if ($className) {
            $stmts = $this->buildClass($className, $stmts);
        }

        return $this->printer->print($stmts);
    }

    /**
     * @param Node[] $stmts
     *
     * @return Node[]
     */
    private function buildClass(string $className, array $stmts): array
    {
        $declareStmt = $this->collectDeclare($stmts);

        $useStmts = $this->collectUseStmt($stmts);

        list($setUpStmts, $propertyStmts) = $this->collectSetUp($stmts);

        $classBuilder = new ClassBuilder($className, $declareStmt, $useStmts, $propertyStmts, $setUpStmts, []);
        return $classBuilder->build();
    }

    /**
     * @param Node[] $stmts
     */
    private function collectDeclare(array $stmts): Stmt|null
    {
        $traverser = new NodeTraverser();
        $visitor = new GetDeclareVisitor();
        $traverser->addVisitor($visitor);

        $traverser->traverse($stmts);

        return $visitor->declare;
    }

    /**
     * @param Node[] $stmts
     *
     * @return Stmt[]
     */
    private function collectUseStmt(array $stmts): array
    {
        $traverser = new NodeTraverser();
        $visitor = new GetUseAliasVisitor();
        $traverser->addVisitor($visitor);

        $traverser->traverse($stmts);

        return $visitor->useAlias;
    }

    /**
     * @param Node[] $stmts
     *
     * @return array{0: Stmt[], 1: Stmt[]}
     */
    private function collectSetUp(array $stmts): array
    {
        $traverser = new NodeTraverser();
        $visitor = new CollectGivenVisitor();
        $traverser->addVisitor($visitor);

        $traverser->traverse($stmts);

        return [$visitor->setUpStmts, $visitor->propertyStmts];
    }

    /**
     * ファイル名からクラス名を抽出
     */
    private function getClassName(string $fileName): string
    {
        preg_match('/\/([a-zA-Z1-9]+).Spec.php$/', $fileName, $result);
        return $result ? $result[1] : '';
    }
}
