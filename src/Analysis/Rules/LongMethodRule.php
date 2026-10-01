<?php

namespace Fredrik\DebtLens\Analysis\Rules;

use Fredrik\DebtLens\Analysis\AnalysisRule;
use Fredrik\DebtLens\Analysis\Finding;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;

class LongMethodRule implements AnalysisRule
{
    private const MAX_LINES = 40;

    public function analyze(Node $node): array
    {
        if (!$node instanceof FunctionLike || $node->getStmts() === null) {
            return [];
        }

        $lineCount = $node->getEndLine() - $node->getStartLine() + 1;

        if ($lineCount <= self::MAX_LINES) {
            return [];
        }

        $methodName = 'anonymous';

        if ($node instanceof Node\Stmt\ClassMethod || $node instanceof Node\Stmt\Function_) {
            $methodName = $node->name->toString();
        }

        return [
            new Finding(
                rule: 'long_method',
                severity: 'medium',
                message: sprintf(
                    '%s has %d lines. Recommended maximum is %d.',
                    $methodName,
                    $lineCount,
                    self::MAX_LINES
                ),
                methodName: $methodName,
                line: $node->getStartLine(),
                actualValue: $lineCount,
                threshold: self::MAX_LINES
            )
        ];
    }
}
