<?php

declare(strict_types=1);

namespace Komtaki\KahlanToCodeception\Converters;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

class ClassBuilder
{
    /**
     * @param Stmt[] $useStmts
     * @param Stmt[] $propertyStmts
     * @param Stmt[] $setupStmts
     * @param Stmt[] $testStmts
     * @return Stmt[]
     */
    public function __construct(
        private string $className,
        private Stmt|null $declareStmt,
        private array $useStmts,
        private array $propertyStmts,
        private array $setupStmts,
        private array $testStmts
    ) {
    }

    /**
     * @return Stmt[]
     */
    public function build(): array
    {
        $stmts = [];

        // declare()があれば追加
        if ($this->declareStmt) {
            $stmts[] = $this->declareStmt;
        }

        return [
            // use文を追加
            ...$this->useStmts,
            new Use_([
                new UseUse(new Name(['Tests', 'unit', 'TestCase']))
            ]),
            // classを追加
            $this->buildClass()
        ];
    }

    /**
     * classを追加
     */
    private function buildClass(): Class_
    {
        return new Class_(
            $this->className,
            [
                'flags' => Class_::MODIFIER_FINAL,
                'extends' => new Name('TestCase'),
                'stmts' => $this->createClassStmts()
            ]
        );
    }

    /**
     * @return Stmt[]
     */
    private function createClassStmts(): array
    {
        $classStmts = $this->propertyStmts;

        if ($this->setupStmts) {
            $classStmts[] = $this->createSetUpFun();
        }

        return $classStmts;
    }

    /**
     * PHPUnitのsetUpを組み立てる
     */
    private function createSetUpFun(): ClassMethod
    {
        $staticCall = new Expression(new StaticCall(new Name('parent'), 'setUp'));

        return new ClassMethod('setUp', [
            'flags' => Class_::MODIFIER_PUBLIC,
            'stmts' => [
                ...$this->setupStmts,
                $staticCall
            ],
            'returnType' => 'void'
        ]);
    }
}
