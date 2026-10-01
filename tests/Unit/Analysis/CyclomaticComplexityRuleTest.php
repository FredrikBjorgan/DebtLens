<?php

use Fredrik\DebtLens\Analysis\Rules\CyclomaticComplexityRule;
use PhpParser\ParserFactory;

it('flags functions with complexity above ten', function (int $complexity) {
    // Each if adds one decision to the function's base complexity of one.
    $body = str_repeat('if ($value) {}', $complexity - 1);
    $node = (new ParserFactory())->createForNewestSupportedVersion()
        ->parse('<?php function example($value) {'.$body.'}')[0];

    $findings = (new CyclomaticComplexityRule())->analyze($node);

    expect($findings)->toHaveCount(1);
    expect($findings[0]->rule)->toBe('cyclomatic_complexity');
    expect($findings[0]->actualValue)->toBe($complexity);
    expect($findings[0]->threshold)->toBe(10);
})->with([11, 12]);

it('does not flag functions with complexity at most ten', function (int $complexity) {
    $body = str_repeat('if ($value) {}', $complexity - 1);
    $node = (new ParserFactory())->createForNewestSupportedVersion()
        ->parse('<?php function example($value) {'.$body.'}')[0];

    expect((new CyclomaticComplexityRule())->analyze($node))->toBe([]);
})->with([1, 9, 10]);
