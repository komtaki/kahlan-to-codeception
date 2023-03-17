<?php

declare(strict_types=1);

namespace Komtaki\KahlanToCodeception\Converters\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;

class GetUseAliasVisitor extends NodeVisitorAbstract
{
    /** @var Use_[] */
    public array $useAlias = [];

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Use_) {
            $this->useAlias[] = $node;
        }

        return null;
    }
}
