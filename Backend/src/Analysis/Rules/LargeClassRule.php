<?php

namespace Fredrik\DebtLens\Analysis\Rules;

use Fredrik\DebtLens\Analysis\AnalysisRule;
use Fredrik\DebtLens\Analysis\Finding;
use PhpParser\Node;

class LargeClassRule implements AnalysisRule
{
    private const MAX_LINES = 300;

    public function analyze(Node $node): array
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return [];
        }

        $lineCount = $node->getEndLine() - $node->getStartLine() + 1;

        if ($lineCount <= self::MAX_LINES) {
            return [];
        }

        $className = $node->name?->toString() ?? 'anonymous';

        return [
            new Finding(
                rule: 'large_class',
                severity: 'medium',
                message: sprintf(
                    '%s has %d lines. Recommended maximum is %d.',
                    $className,
                    $lineCount,
                    self::MAX_LINES
                ),
                className: $className,
                line: $node->getStartLine(),
                actualValue: $lineCount,
                threshold: self::MAX_LINES
            )
        ];
    }
}
