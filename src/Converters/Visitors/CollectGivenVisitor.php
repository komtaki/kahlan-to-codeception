<?php

declare(strict_types=1);

namespace Komtaki\KahlanToCodeception\Converters\Visitors;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitorAbstract;

class CollectGivenVisitor extends NodeVisitorAbstract
{
    /** @var Stmt[] */
    public array $setUpStmts = [];

    /** @var Property[] */
    public array $propertyStmts = [];

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof FuncCall && $node->name instanceof Name && $node->name->toString() == 'given') {
            $args = $node->getArgs();
            /**
             * @var String_ $firstArgsValue
             */
            $firstArgsValue = $args[0]->value;
            $propertyName = $firstArgsValue->value;

            /**
             * @var Closure $closure
             */
            $closure = $args[1]->value;

            $this->setUpStmts = array_merge($this->setUpStmts, $this->createSetup($propertyName, $closure));
        }

        return null;
    }

    /**
     * returnを代入に変更し、closureのSMTSを返す
     *
     * @return Stmt[]
     */
    private function createSetup(string $propertyName, Closure $closure): array
    {
        $stmts = [];
        foreach($closure->stmts as $stmt) {
            if ($stmt instanceof Return_ && $stmt->expr) {
                $type = $this->getTypeByReturn($stmt->expr);
                $this->propertyStmts[] = $this->createProperty($propertyName, $type);

                $var = new PropertyFetch(new Variable('this'), $propertyName);
                $assign = new Assign($var, $stmt->expr);
                $stmts[] = new Expression($assign);
                continue;
            }
            $stmts[] = $stmt;
        }

        return $stmts;
    }

    /**
     * returnの戻り値をみて、型を決める
     */
    private function getTypeByReturn(Expr|null $expr): mixed
    {
        if ($expr instanceof Array_) {
            return 'array';
        }

        if ($expr instanceof String_) {
            return 'string';
        }

        if ($expr instanceof LNumber) {
            return 'int';
        }

        if ($expr instanceof New_ && $expr->class instanceof Node\Stmt\Class_) {
            return $expr->class->isAnonymous() ? $expr->class->implements[0] : $expr->class;
        }

        if ($expr instanceof New_) {
            return $expr->class;
        }

        return null;
    }

    /**
     * 引数の名前で、privateのプロパティオブジェクトを作る
     */
    private function createProperty(string $propertyName, $type): Property
    {
        $visibility = Class_::MODIFIER_PRIVATE;
        $property = new PropertyProperty($propertyName);

        return new Property($visibility, [$property], [], $type);
    }
}
