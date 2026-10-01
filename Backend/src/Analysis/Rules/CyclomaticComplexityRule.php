<?php

namespace Fredrik\DebtLens\Analysis\Rules;

use Fredrik\DebtLens\Analysis\AnalysisRule;
use Fredrik\DebtLens\Analysis\Finding;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\NodeFinder;

class CyclomaticComplexityRule implements AnalysisRule
{
    private const MAX_COMPLEXITY = 10;

    public function analyze(Node $node): array
    {
        if (!$node instanceof FunctionLike) {
            return [];
        }

        $statements = $node->getStmts();

        if ($statements === null) {
            return [];
        }

        $complexity = 1;

        $nodeFinder = new NodeFinder();

        $decisionNodes = $nodeFinder->find(
            $statements,
            function (Node $node): bool {
                return $node instanceof Node\Stmt\If_
                    || $node instanceof Node\Stmt\ElseIf_
                    || $node instanceof Node\Stmt\For_
                    || $node instanceof Node\Stmt\Foreach_
                    || $node instanceof Node\Stmt\While_
                    || $node instanceof Node\Stmt\Do_
                    || $node instanceof Node\Stmt\Case_
                    || $node instanceof Node\Stmt\Catch_;
            }
        );

        $complexity += count($decisionNodes);

        if ($complexity <= self::MAX_COMPLEXITY) {
            return [];
        }

        $methodName = 'anonymous';

        if ($node instanceof Node\Stmt\ClassMethod) {
            $methodName = $node->name->toString();
        }

        if ($node instanceof Node\Stmt\Function_) {
            $methodName = $node->name->toString();
        }

        return [
            new Finding(
                rule: 'cyclomatic_complexity',
                severity: 'high',
                message: sprintf(
                    '%s has cyclomatic complexity %d. Recommended maximum is %d.',
                    $methodName,
                    $complexity,
                    self::MAX_COMPLEXITY
                ),
                methodName: $methodName,
                line: $node->getStartLine(),
                actualValue: $complexity,
                threshold: self::MAX_COMPLEXITY
            )
        ];
    }
}