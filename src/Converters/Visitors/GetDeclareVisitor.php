<?php

declare(strict_types=1);

namespace Komtaki\KahlanToCodeception\Converters\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class GetDeclareVisitor extends NodeVisitorAbstract
{
    public Declare_|null $declare = null;

    /**
     * @inheritDoc
     */
    public function beforeTraverse(array $nodes)
    {
        foreach ($nodes as $node) {
            if ($node instanceof Declare_) {
                $this->declare = $node;

                return null;
            }
        }

        return null;
    }

    public function enterNode(Node $node)
    {
        return NodeTraverser::STOP_TRAVERSAL;
    }
}
