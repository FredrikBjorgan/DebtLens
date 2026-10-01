<?php

namespace Fredrik\DebtLens\Analysis\Rules;

use Fredrik\DebtLens\Analysis\AnalysisRule;
use Fredrik\DebtLens\Analysis\Finding;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;

class TooManyParametersRule implements AnalysisRule
{
    private const MAX_PARAMETERS = 5;

    public function analyze(Node $node): array
    {
        if (!$node instanceof FunctionLike) {
            return [];
        }

        $parameterCount = count($node->getParams());

        if ($parameterCount <= self::MAX_PARAMETERS) {
            return [];
        }

        $methodName = null;

        if ($node instanceof Node\Stmt\ClassMethod) {
            $methodName = $node->name->toString();
        }

        if ($node instanceof Node\Stmt\Function_) {
            $methodName = $node->name->toString();
        }

        return [
            new Finding(
                rule: 'too_many_parameters',
                severity: 'medium',
                message: sprintf(
                    '%s has %d parameters. Recommended maximum is %d.',
                    $methodName ?? 'Function',
                    $parameterCount,
                    self::MAX_PARAMETERS
                ),
                methodName: $methodName,
                line: $node->getStartLine(),
                actualValue: $parameterCount,
                threshold: self::MAX_PARAMETERS
            )
        ];
    }
}