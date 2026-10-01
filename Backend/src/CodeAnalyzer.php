<?php

namespace Fredrik\DebtLens;

use Fredrik\DebtLens\Analysis\AnalysisRule;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

class CodeAnalyzer
{
    /**
     * @param AnalysisRule[] $rules
     */
    public function __construct(
        private array $rules
    ) {
    }

    public function analyze(string $code): array
    {
        $parser = (new ParserFactory())
            ->createForNewestSupportedVersion();

        $ast = $parser->parse($code);

        if ($ast === null) {
            return [];
        }

        $visitor = new class($this->rules) extends NodeVisitorAbstract {
            public array $findings = [];

            public function __construct(
                private array $rules
            ) {
            }

            public function enterNode(Node $node)
            {
                foreach ($this->rules as $rule) {
                    $this->findings = array_merge(
                        $this->findings,
                        $rule->analyze($node)
                    );
                }
            }
        };

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $visitor->findings;
    }
}