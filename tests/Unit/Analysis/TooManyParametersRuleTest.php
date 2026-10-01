<?php

use Fredrik\DebtLens\Analysis\Rules\TooManyParametersRule;
use PhpParser\ParserFactory;

it('flags functions with more than five parameters', function (int $parameterCount) {
    $parameters = implode(', ', array_map(
        fn (int $index): string => '$parameter'.$index,
        range(1, $parameterCount),
    ));
    $node = (new ParserFactory())->createForNewestSupportedVersion()
        ->parse('<?php function example('.$parameters.') {}')[0];

    $findings = (new TooManyParametersRule())->analyze($node);

    expect($findings)->toHaveCount(1);
    expect($findings[0]->rule)->toBe('too_many_parameters');
    expect($findings[0]->actualValue)->toBe($parameterCount);
    expect($findings[0]->threshold)->toBe(5);
})->with([6, 7]);

it('does not flag functions with at most five parameters', function (int $parameterCount) {
    $parameters = $parameterCount === 0 ? '' : implode(', ', array_map(
        fn (int $index): string => '$parameter'.$index,
        range(1, $parameterCount),
    ));
    $node = (new ParserFactory())->createForNewestSupportedVersion()
        ->parse('<?php function example('.$parameters.') {}')[0];

    expect((new TooManyParametersRule())->analyze($node))->toBe([]);
})->with([0, 4, 5]);
